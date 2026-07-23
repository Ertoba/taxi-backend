<?php

namespace App\Strategies;

use App\Http\Controllers\Traits\PaymentStatusUpdaterTrait;
use App\Models\Booking;
use App\Models\GeneralSetting;
use App\Models\KeepzSplitSettlement;
use App\Models\Transaction;
use App\Services\KeepzSplitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use Throwable;

class KeepzSplitStrategy implements PaymentStrategy
{
    use PaymentStatusUpdaterTrait;

    private const TEST_API_BASE_URL = 'https://gateway.dev.keepz.me/ecommerce-service';

    private const LIVE_API_BASE_URL = 'https://gateway.keepz.me/ecommerce-service';

    private const SUPPORTED_CURRENCIES = ['GEL', 'USD', 'EUR'];

    private string $mode;

    private array $credentials;

    private KeepzStrategy $legacyStrategy;

    public function __construct(private ?KeepzSplitService $splitService = null)
    {
        $this->splitService ??= new KeepzSplitService;
        $this->legacyStrategy = new KeepzStrategy;
        $this->mode = strtolower((string) GeneralSetting::getMetaValue('keepz_options')) === 'live'
            ? 'live'
            : 'test';

        $prefix = $this->mode.'_keepz_';
        $this->credentials = [
            'identifier' => (string) GeneralSetting::getMetaValue($prefix.'identifier'),
            'integrator_id' => (string) GeneralSetting::getMetaValue($prefix.'integrator_id'),
            'receiver_id' => (string) GeneralSetting::getMetaValue($prefix.'receiver_id'),
            'receiver_type' => (string) GeneralSetting::getMetaValue($prefix.'receiver_type'),
            'public_key' => (string) GeneralSetting::getMetaValue($prefix.'public_key'),
            'private_key' => (string) GeneralSetting::getMetaValue($prefix.'private_key'),
        ];
    }

    public function process($bookingId, $bookingData, Request $request)
    {
        if (! $this->splitService->isEnabled()) {
            return $this->legacyStrategy->process($bookingId, $bookingData, $request);
        }

        $lock = Cache::lock('keepz-split-booking:'.(string) $bookingId, 30);
        if (! $lock->get()) {
            return redirect('/invalid-order')->with(
                'error',
                'A Keepz payment is already being prepared for this ride. Please wait and try again.'
            );
        }

        try {
            return $this->processSplitPayment($bookingId);
        } finally {
            optional($lock)->release();
        }
    }

    private function processSplitPayment($bookingId)
    {
        if (! $this->isConfigured()) {
            Log::warning('Keepz split payment was requested before gateway configuration was complete.');

            return redirect('/invalid-order')->with('error', 'Keepz payment is not configured.');
        }

        $booking = Booking::with('host')->find($bookingId);
        if (! $booking || ! $booking->host || $booking->host->user_type !== 'driver') {
            return redirect('/invalid-order')->with('error', 'The ride or driver is invalid.');
        }

        if ($booking->payment_status === 'paid') {
            return redirect('/invalid-order')->with('error', 'This ride is already paid.');
        }

        $existingAttempt = $this->latestActiveSplitTransaction($booking->id);
        if ($existingAttempt) {
            $status = $this->verifyAndApplyStatus(
                $booking->id,
                (string) $existingAttempt->transaction_id
            );

            if ($status === 'success') {
                return redirect('/payment_success?bookingId='.urlencode((string) $booking->id));
            }

            if (in_array($status, ['initial', 'processing'], true) || $status === null) {
                $metadata = $this->transactionMetadata($existingAttempt->fresh());
                $checkoutUrl = data_get($metadata, 'checkout_url');
                if (is_string($checkoutUrl) && filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
                    return redirect()->away($checkoutUrl);
                }

                return redirect('/invalid-order')->with(
                    'error',
                    'The existing Keepz payment is still being verified. Please try again shortly.'
                );
            }
        }

        $currency = strtoupper((string) ($booking->currency_code ?: 'GEL'));
        $amount = round((float) ($booking->amount_to_pay ?: $booking->total), 2);

        if ($amount <= 0 || ! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            return redirect('/invalid-order')->with('error', 'This payment cannot be processed by Keepz.');
        }

        try {
            $platformReceiver = $this->splitService->platformReceiver($this->mode);
            $driverReceiver = $this->splitService->driverReceiver($booking->host);
            $allocation = $this->splitService->allocation($booking, $amount);
            $splitDetails = $this->splitService->splitDetails(
                $platformReceiver,
                $driverReceiver,
                $allocation
            );
        } catch (Throwable $exception) {
            Log::warning('Keepz split payment initialization was blocked.', [
                'booking_id' => $booking->id,
                'driver_id' => $booking->host_id,
                'reason' => $exception->getMessage(),
            ]);

            return redirect('/invalid-order')->with(
                'error',
                'Keepz split payment cannot start until the platform and driver IBAN accounts are configured.'
            );
        }

        $integratorOrderId = (string) Str::uuid();
        $splitMetadata = [
            'keepz_split' => true,
            'integrator_order_id' => $integratorOrderId,
            'driver_id' => (int) $booking->host_id,
            'total_amount' => $allocation['total'],
            'platform_amount' => $allocation['platform'],
            'driver_amount' => $allocation['driver'],
            'driver_ratio' => $allocation['driver_ratio'] ?? null,
            'platform_receiver_type' => $platformReceiver['type'],
            'platform_receiver_masked' => $platformReceiver['masked_identifier'],
            'driver_receiver_type' => $driverReceiver['type'],
            'driver_receiver_masked' => $driverReceiver['masked_identifier'],
            'created_at' => now()->toIso8601String(),
        ];

        $transaction = new Transaction;
        $transaction->booking_id = $booking->id;
        $transaction->gateway_name = 'keepz';
        $transaction->transaction_id = $integratorOrderId;
        $transaction->amount = $allocation['total'];
        $transaction->currency_code = $currency;
        $transaction->payment_status = 'pending';
        $transaction->response_data = json_encode($splitMetadata, JSON_UNESCAPED_SLASHES);
        $transaction->save();

        $payload = [
            'amount' => $allocation['total'],
            'receiverId' => trim($this->credentials['receiver_id']),
            'receiverType' => strtoupper(trim($this->credentials['receiver_type'])),
            'integratorId' => trim($this->credentials['integrator_id']),
            'integratorOrderId' => $integratorOrderId,
            'currency' => $currency,
            'successRedirectUri' => route('handleReturn', [
                'booking' => $booking->id,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'failRedirectUri' => route('handleCancel', [
                'booking' => $booking->id,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'callbackUri' => route('handleCallback', [
                'booking' => $booking->id,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'language' => 'KA',
            'splitDetails' => $splitDetails,
        ];

        $response = $this->sendEncryptedRequest('POST', '/api/integrator/order', $payload);
        $redirectUrl = $this->redirectUrlFromResponse($response);

        $transaction->response_data = json_encode(array_merge(
            $splitMetadata,
            [
                'checkout_url' => $redirectUrl,
                'gateway_create_response' => $this->safeGatewayPayload($response),
            ]
        ), JSON_UNESCAPED_SLASHES);
        $transaction->payment_status = $redirectUrl ? 'pending' : 'failed';
        $transaction->save();

        if (! $redirectUrl) {
            Log::warning('Keepz split order creation did not return a redirect URL.', [
                'booking_id' => $booking->id,
                'integrator_order_id' => $integratorOrderId,
                'status_code' => data_get($response, 'statusCode'),
            ]);

            return redirect('/invalid-order')->with('error', 'Keepz payment could not be initialized.');
        }

        return redirect()->away($redirectUrl);
    }

    public function return($bookingId, $requestData)
    {
        $integratorOrderId = $this->orderIdFromRequest($requestData);
        if (! $this->isSplitTransaction($bookingId, $integratorOrderId)) {
            return $this->legacyStrategy->return($bookingId, $requestData);
        }

        $status = $this->verifyAndApplyStatus($bookingId, $integratorOrderId);

        return $status === 'success'
            ? '/payment_success?bookingId='.urlencode((string) $bookingId)
            : '/payment_fail?bookingId='.urlencode((string) $bookingId);
    }

    public function callback($bookingId, $requestData)
    {
        $integratorOrderId = $this->orderIdFromRequest($requestData);
        if (! $this->isSplitTransaction($bookingId, $integratorOrderId)) {
            return $this->legacyStrategy->callback($bookingId, $requestData);
        }

        return $this->verifyAndApplyStatus($bookingId, $integratorOrderId);
    }

    public function cancel($bookingId, $requestData)
    {
        $integratorOrderId = $this->orderIdFromRequest($requestData);
        if (! $this->isSplitTransaction($bookingId, $integratorOrderId)) {
            return $this->legacyStrategy->cancel($bookingId, $requestData);
        }

        $status = $this->verifyAndApplyStatus($bookingId, $integratorOrderId);

        return $status === 'success'
            ? '/payment_success?bookingId='.urlencode((string) $bookingId)
            : '/payment_fail?bookingId='.urlencode((string) $bookingId);
    }

    public function refund($bookingId, $bookingData)
    {
        Log::warning('Automatic Keepz split refund is not enabled.', [
            'booking_id' => $bookingId,
        ]);

        return false;
    }

    public function reconcilePendingTransaction(Transaction $transaction): ?string
    {
        if ($transaction->gateway_name !== 'keepz' || ! $this->transactionContainsSplitMetadata($transaction)) {
            return null;
        }

        return $this->verifyAndApplyStatus(
            $transaction->booking_id,
            (string) $transaction->transaction_id
        );
    }

    private function verifyAndApplyStatus($bookingId, ?string $integratorOrderId): ?string
    {
        if (! $integratorOrderId || ! Str::isUuid($integratorOrderId)) {
            return null;
        }

        $transaction = Transaction::where('booking_id', $bookingId)
            ->where('gateway_name', 'keepz')
            ->where('transaction_id', $integratorOrderId)
            ->first();

        if (! $transaction || ! $this->transactionContainsSplitMetadata($transaction)) {
            return null;
        }

        $response = $this->sendEncryptedRequest('GET', '/api/integrator/order/status', [
            'integratorId' => trim($this->credentials['integrator_id']),
            'integratorOrderId' => $integratorOrderId,
        ]);

        $responseOrderId = data_get($response, 'integratorOrderId');
        if (! is_string($responseOrderId) || ! hash_equals($integratorOrderId, $responseOrderId)) {
            Log::error('Keepz split status response did not match the expected order.', [
                'booking_id' => $bookingId,
                'integrator_order_id' => $integratorOrderId,
            ]);

            return 'order_mismatch';
        }

        $status = $this->normalizeStatus(
            data_get($response, 'status') ?? data_get($response, 'orderStatus')
        );

        if ($status === 'success') {
            if (! $this->matchesTransaction($transaction, $response ?? [])) {
                Log::error('Keepz split success response amount or currency mismatch.', [
                    'booking_id' => $bookingId,
                    'integrator_order_id' => $integratorOrderId,
                ]);

                return 'amount_or_currency_mismatch';
            }

            $this->finalizeSuccessfulPayment($bookingId, $integratorOrderId, $response ?? []);
        } elseif (in_array($status, ['failed', 'failure', 'canceled', 'cancelled', 'expired'], true)) {
            $metadata = $this->transactionMetadata($transaction);
            $transaction->payment_status = $status;
            $transaction->response_data = json_encode(array_merge(
                $metadata,
                ['gateway_status_response' => $this->safeGatewayPayload($response)]
            ), JSON_UNESCAPED_SLASHES);
            $transaction->save();
        } elseif ($status !== null) {
            $metadata = $this->transactionMetadata($transaction);
            $transaction->payment_status = $status;
            $transaction->response_data = json_encode(array_merge(
                $metadata,
                ['gateway_status_response' => $this->safeGatewayPayload($response)]
            ), JSON_UNESCAPED_SLASHES);
            $transaction->save();
        }

        return $status;
    }

    private function finalizeSuccessfulPayment($bookingId, string $integratorOrderId, array $response): void
    {
        $notify = false;

        DB::transaction(function () use ($bookingId, $integratorOrderId, $response, &$notify): void {
            $booking = Booking::whereKey($bookingId)->lockForUpdate()->first();
            $transaction = Transaction::where('booking_id', $bookingId)
                ->where('gateway_name', 'keepz')
                ->where('transaction_id', $integratorOrderId)
                ->lockForUpdate()
                ->first();

            if (! $booking || ! $transaction || $transaction->payment_status === 'completed') {
                return;
            }

            $metadata = $this->transactionMetadata($transaction);
            if (($metadata['keepz_split'] ?? false) !== true) {
                return;
            }

            if ($booking->payment_status === 'paid') {
                $transaction->payment_status = 'duplicate_paid_requires_refund';
                $transaction->response_data = json_encode(array_merge(
                    $metadata,
                    ['gateway_status_response' => $this->safeGatewayPayload($response)]
                ), JSON_UNESCAPED_SLASHES);
                $transaction->save();

                Log::critical('A duplicate successful Keepz split payment requires manual refund review.', [
                    'booking_id' => $bookingId,
                    'integrator_order_id' => $integratorOrderId,
                    'existing_transaction_id' => $booking->transaction,
                ]);

                return;
            }

            $transaction->payment_status = 'completed';
            $transaction->response_data = json_encode(array_merge(
                $metadata,
                [
                    'paid_at' => now()->toIso8601String(),
                    'gateway_status_response' => $this->safeGatewayPayload($response),
                ]
            ), JSON_UNESCAPED_SLASHES);
            $transaction->save();

            KeepzSplitSettlement::create([
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'driver_id' => (int) $booking->host_id,
                'integrator_order_id' => $integratorOrderId,
                'currency_code' => strtoupper((string) $transaction->currency_code),
                'total_amount' => (float) ($metadata['total_amount'] ?? $transaction->amount),
                'platform_amount' => (float) ($metadata['platform_amount'] ?? 0),
                'driver_amount' => (float) ($metadata['driver_amount'] ?? 0),
                'platform_receiver_type' => (string) ($metadata['platform_receiver_type'] ?? ''),
                'platform_receiver_masked' => (string) ($metadata['platform_receiver_masked'] ?? ''),
                'driver_receiver_type' => (string) ($metadata['driver_receiver_type'] ?? ''),
                'driver_receiver_masked' => (string) ($metadata['driver_receiver_masked'] ?? ''),
                'gateway_status' => 'SUCCESS',
                'gateway_payload' => $this->safeGatewayPayload($response),
                'paid_at' => now(),
            ]);

            $booking->payment_status = 'paid';
            $booking->payment_method = 'keepz';
            $booking->transaction = $transaction->id;
            $booking->status = 'Completed';
            $booking->vendor_commission_given = 1;
            $booking->save();
            $notify = true;
        });

        if ($notify) {
            try {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    $values = $this->createNotificationArray(
                        $booking->userid,
                        $booking->host_id,
                        $booking->itemid,
                        $bookingId
                    );
                    $this->sendAllNotifications(
                        $values,
                        $booking->userid,
                        14,
                        ['message_key' => $booking],
                        $booking->host_id
                    );
                }
            } catch (Throwable $exception) {
                Log::error('Keepz split payment completed but notification dispatch failed.', [
                    'booking_id' => $bookingId,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function latestActiveSplitTransaction(int $bookingId): ?Transaction
    {
        return Transaction::query()
            ->where('booking_id', $bookingId)
            ->where('gateway_name', 'keepz')
            ->whereIn('payment_status', ['pending', 'initial', 'processing'])
            ->orderByDesc('id')
            ->get()
            ->first(fn (Transaction $transaction): bool => $this->transactionContainsSplitMetadata($transaction));
    }

    private function isSplitTransaction($bookingId, ?string $integratorOrderId): bool
    {
        if (! $integratorOrderId || ! Str::isUuid($integratorOrderId)) {
            return false;
        }

        $transaction = Transaction::where('booking_id', $bookingId)
            ->where('gateway_name', 'keepz')
            ->where('transaction_id', $integratorOrderId)
            ->first();

        return $transaction ? $this->transactionContainsSplitMetadata($transaction) : false;
    }

    private function transactionContainsSplitMetadata(Transaction $transaction): bool
    {
        return ($this->transactionMetadata($transaction)['keepz_split'] ?? false) === true;
    }

    private function transactionMetadata(Transaction $transaction): array
    {
        $decoded = json_decode((string) $transaction->response_data, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function matchesTransaction(Transaction $transaction, array $response): bool
    {
        $actualAmount = data_get($response, 'amount') ?? data_get($response, 'acquiringAmount');
        $actualCurrency = data_get($response, 'initialCurrency') ?? data_get($response, 'currency');

        if ($actualAmount !== null && abs((float) $actualAmount - (float) $transaction->amount) > 0.01) {
            return false;
        }

        return $actualCurrency === null
            || strtoupper((string) $actualCurrency) === strtoupper((string) $transaction->currency_code);
    }

    private function sendEncryptedRequest(string $method, string $endpoint, array $payload): ?array
    {
        $envelope = $this->buildEncryptedEnvelope($payload);
        if (! $envelope) {
            return null;
        }

        try {
            $request = Http::acceptJson()->timeout(30);
            $response = strtoupper($method) === 'GET'
                ? $request->get($this->baseUrl().$endpoint, array_merge($envelope, ['aes' => 'true']))
                : $request->post($this->baseUrl().$endpoint, $envelope);
            $decoded = $response->json();

            if (! is_array($decoded)) {
                Log::warning('Keepz split request returned a non-JSON response.', [
                    'endpoint' => $endpoint,
                    'http_code' => $response->status(),
                ]);

                return null;
            }

            if ($response->failed()) {
                Log::warning('Keepz split request returned an HTTP error.', [
                    'endpoint' => $endpoint,
                    'http_code' => $response->status(),
                    'status_code' => data_get($decoded, 'statusCode'),
                    'message' => data_get($decoded, 'message'),
                ]);
            }

            return $this->decodeKeepzResponse($decoded);
        } catch (Throwable $exception) {
            Log::error('Keepz split request failed.', [
                'endpoint' => $endpoint,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function buildEncryptedEnvelope(array $payload): ?array
    {
        try {
            $plainText = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
            $aesKey = random_bytes(32);
            $iv = random_bytes(16);
            $cipherText = openssl_encrypt($plainText, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);

            if ($cipherText === false) {
                throw new \RuntimeException('AES encryption failed.');
            }

            $aesProperties = base64_encode($aesKey).'.'.base64_encode($iv);
            $publicKey = PublicKeyLoader::load($this->normalizeKey($this->credentials['public_key'], 'PUBLIC KEY'))
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');

            return [
                'identifier' => trim($this->credentials['identifier']),
                'encryptedData' => base64_encode($cipherText),
                'encryptedKeys' => base64_encode($publicKey->encrypt($aesProperties)),
                'aes' => true,
            ];
        } catch (Throwable $exception) {
            Log::error('Keepz split request encryption failed.', [
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function decodeKeepzResponse(array $response): ?array
    {
        if (! isset($response['encryptedData'], $response['encryptedKeys'])) {
            return $response;
        }

        try {
            $encryptedKeys = base64_decode((string) $response['encryptedKeys'], true);
            $encryptedData = base64_decode((string) $response['encryptedData'], true);

            if ($encryptedKeys === false || $encryptedData === false) {
                throw new \RuntimeException('Invalid Keepz response encoding.');
            }

            $privateKey = PublicKeyLoader::loadPrivateKey(
                $this->normalizePrivateKey($this->credentials['private_key'])
            )
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');
            $aesProperties = $privateKey->decrypt($encryptedKeys);
            $delimiter = strpos($aesProperties, '.');

            if ($delimiter === false) {
                throw new \RuntimeException('Invalid Keepz AES properties.');
            }

            $aesKey = base64_decode(substr($aesProperties, 0, $delimiter), true);
            $iv = base64_decode(substr($aesProperties, $delimiter + 1), true);
            if ($aesKey === false || $iv === false) {
                throw new \RuntimeException('Invalid Keepz AES key material.');
            }

            $plainText = openssl_decrypt($encryptedData, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA, $iv);
            $decoded = $plainText === false ? null : json_decode($plainText, true);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $exception) {
            Log::error('Keepz split response decryption failed.', [
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function normalizePrivateKey(string $key): string
    {
        $normalized = str_replace('\\n', "\n", trim($key));

        return str_contains($normalized, '-----BEGIN')
            ? $normalized
            : $this->normalizeKey($normalized, 'PRIVATE KEY');
    }

    private function normalizeKey(string $key, string $label): string
    {
        $normalized = str_replace('\\n', "\n", trim($key));
        if (str_contains($normalized, '-----BEGIN')) {
            return $normalized;
        }

        $body = preg_replace('/\s+/', '', $normalized) ?? $normalized;

        return "-----BEGIN {$label}-----\n".chunk_split($body, 64, "\n")."-----END {$label}-----";
    }

    private function orderIdFromRequest($requestData): ?string
    {
        $orderId = $requestData instanceof Request
            ? $requestData->input('order')
            : data_get($requestData, 'order');

        return is_string($orderId) ? $orderId : null;
    }

    private function redirectUrlFromResponse(?array $response): ?string
    {
        $url = data_get($response, 'urlForQR')
            ?? data_get($response, 'redirectUrl')
            ?? data_get($response, 'checkoutUrl')
            ?? data_get($response, 'url');

        return is_string($url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function normalizeStatus(?string $status): ?string
    {
        $normalized = filled($status)
            ? strtolower(str_replace([' ', '-'], '_', trim((string) $status)))
            : null;

        return match ($normalized) {
            'success', 'successful', 'completed', 'paid' => 'success',
            default => $normalized,
        };
    }

    private function safeGatewayPayload(?array $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        unset($payload['encryptedData'], $payload['encryptedKeys']);

        return $payload;
    }

    private function isConfigured(): bool
    {
        foreach ($this->credentials as $value) {
            if (trim($value) === '') {
                return false;
            }
        }

        return true;
    }

    private function baseUrl(): string
    {
        return $this->mode === 'live' ? self::LIVE_API_BASE_URL : self::TEST_API_BASE_URL;
    }
}
