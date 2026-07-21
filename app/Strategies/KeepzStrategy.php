<?php

namespace App\Strategies;

use App\Http\Controllers\Traits\PaymentStatusUpdaterTrait;
use App\Models\Booking;
use App\Models\GeneralSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use Throwable;

class KeepzStrategy implements PaymentStrategy
{
    use PaymentStatusUpdaterTrait;

    private const TEST_API_BASE_URL = 'https://gateway.dev.keepz.me/ecommerce-service';

    private const LIVE_API_BASE_URL = 'https://gateway.keepz.me/ecommerce-service';

    private const SUPPORTED_CURRENCIES = ['GEL', 'USD', 'EUR'];

    private string $mode;

    private array $credentials;

    public function __construct()
    {
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

    public function process($bookingId, $bookingData, $request)
    {
        if (! $this->isConfigured()) {
            Log::warning('Keepz payment was requested before configuration was complete.');

            return redirect('/invalid-order')->with('error', 'Keepz payment is not configured.');
        }

        $currency = strtoupper((string) ($bookingData->currency_code ?: 'GEL'));
        $amount = round((float) ($bookingData->amount_to_pay ?: $bookingData->total), 2);

        if ($amount <= 0 || ! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            return redirect('/invalid-order')->with('error', 'This payment cannot be processed by Keepz.');
        }

        $integratorOrderId = (string) Str::uuid();
        $transaction = new Transaction;
        $transaction->booking_id = $bookingId;
        $transaction->gateway_name = 'keepz';
        $transaction->transaction_id = $integratorOrderId;
        $transaction->amount = $amount;
        $transaction->currency_code = $currency;
        $transaction->payment_status = 'pending';
        $transaction->response_data = json_encode([
            'integrator_order_id' => $integratorOrderId,
            'created_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES);
        $transaction->save();

        $payload = [
            'amount' => $amount,
            'receiverId' => trim($this->credentials['receiver_id']),
            'receiverType' => strtoupper(trim($this->credentials['receiver_type'])),
            'integratorId' => trim($this->credentials['integrator_id']),
            'integratorOrderId' => $integratorOrderId,
            'currency' => $currency,
            'successRedirectUri' => route('handleReturn', [
                'booking' => $bookingId,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'failRedirectUri' => route('handleCancel', [
                'booking' => $bookingId,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'callbackUri' => route('handleCallback', [
                'booking' => $bookingId,
                'method' => 'keepz',
                'order' => $integratorOrderId,
            ]),
            'language' => 'KA',
        ];

        $response = $this->sendEncryptedRequest('POST', '/api/integrator/order', $payload);
        $redirectUrl = $this->redirectUrlFromResponse($response);

        $transaction->response_data = json_encode($this->safeGatewayPayload($response), JSON_UNESCAPED_SLASHES);
        $transaction->payment_status = $redirectUrl ? 'pending' : 'failed';
        $transaction->save();

        if (! $redirectUrl) {
            Log::warning('Keepz order creation did not return a redirect URL.', [
                'booking_id' => $bookingId,
                'integrator_order_id' => $integratorOrderId,
                'status_code' => data_get($response, 'statusCode'),
            ]);

            return redirect('/invalid-order')->with('error', 'Keepz payment could not be initialized.');
        }

        return redirect()->away($redirectUrl);
    }

    public function return($bookingId, $requestData)
    {
        $status = $this->verifyAndApplyStatus($bookingId, $this->orderIdFromRequest($requestData));

        return $status === 'success'
            ? '/payment_success?bookingId='.urlencode((string) $bookingId)
            : '/payment_fail?bookingId='.urlencode((string) $bookingId);
    }

    public function callback($bookingId, $requestData)
    {
        $this->verifyAndApplyStatus($bookingId, $this->orderIdFromRequest($requestData));
    }

    public function cancel($bookingId, $requestData)
    {
        $status = $this->verifyAndApplyStatus($bookingId, $this->orderIdFromRequest($requestData));

        return $status === 'success'
            ? '/payment_success?bookingId='.urlencode((string) $bookingId)
            : '/payment_fail?bookingId='.urlencode((string) $bookingId);
    }

    public function refund($bookingId, $bookingData) {}

    private function verifyAndApplyStatus($bookingId, ?string $integratorOrderId): ?string
    {
        if (! $integratorOrderId || ! Str::isUuid($integratorOrderId)) {
            return null;
        }

        $transaction = Transaction::where('booking_id', $bookingId)
            ->where('gateway_name', 'keepz')
            ->where('transaction_id', $integratorOrderId)
            ->first();

        if (! $transaction) {
            return null;
        }

        $response = $this->sendEncryptedRequest('GET', '/api/integrator/order/status', [
            'integratorId' => trim($this->credentials['integrator_id']),
            'integratorOrderId' => $integratorOrderId,
        ]);

        $responseOrderId = data_get($response, 'integratorOrderId');
        if (! is_string($responseOrderId) || ! hash_equals($integratorOrderId, $responseOrderId)) {
            Log::error('Keepz status response did not match the expected order.', [
                'booking_id' => $bookingId,
                'integrator_order_id' => $integratorOrderId,
            ]);

            return 'order_mismatch';
        }

        $status = $this->normalizeStatus(data_get($response, 'status') ?? data_get($response, 'orderStatus'));

        if ($status === 'success') {
            if (! $this->matchesTransaction($transaction, $response ?? [])) {
                Log::error('Keepz successful response did not match the expected amount or currency.', [
                    'booking_id' => $bookingId,
                    'integrator_order_id' => $integratorOrderId,
                ]);

                return 'amount_or_currency_mismatch';
            }

            $this->finalizeSuccessfulPayment($bookingId, $integratorOrderId, $response ?? []);
        } elseif (in_array($status, ['failed', 'failure', 'canceled', 'cancelled', 'expired'], true)) {
            $transaction->payment_status = $status;
            $transaction->response_data = json_encode($this->safeGatewayPayload($response), JSON_UNESCAPED_SLASHES);
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

            if ($booking->payment_status === 'paid') {
                $transaction->payment_status = 'ignored_already_paid';
                $transaction->response_data = json_encode($this->safeGatewayPayload($response), JSON_UNESCAPED_SLASHES);
                $transaction->save();

                return;
            }

            $transaction->payment_status = 'completed';
            $transaction->response_data = json_encode($this->safeGatewayPayload($response), JSON_UNESCAPED_SLASHES);
            $transaction->save();

            $booking->payment_status = 'paid';
            $booking->payment_method = 'keepz';
            $booking->transaction = $transaction->id;
            $booking->status = 'Completed';
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
                    $this->sendAllNotifications($values, $booking->userid, 14, ['message_key' => $booking], $booking->host_id);
                }
            } catch (Throwable $exception) {
                Log::error('Keepz payment completed but notification dispatch failed.', [
                    'booking_id' => $bookingId,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
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
                Log::warning('Keepz returned a non-JSON response.', [
                    'endpoint' => $endpoint,
                    'http_code' => $response->status(),
                ]);

                return null;
            }

            if ($response->failed()) {
                Log::warning('Keepz returned an HTTP error.', [
                    'endpoint' => $endpoint,
                    'http_code' => $response->status(),
                    'status_code' => data_get($decoded, 'statusCode'),
                    'message' => data_get($decoded, 'message'),
                ]);
            }

            return $this->decodeKeepzResponse($decoded);
        } catch (Throwable $exception) {
            Log::error('Keepz request failed.', [
                'endpoint' => $endpoint,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function buildEncryptedEnvelope(array $payload): ?array
    {
        try {
            $plainText = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
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
            Log::error('Keepz request encryption failed.', ['exception' => $exception->getMessage()]);

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

            $privateKey = PublicKeyLoader::loadPrivateKey($this->normalizePrivateKey($this->credentials['private_key']))
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
            Log::error('Keepz response decryption failed.', ['exception' => $exception->getMessage()]);

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
        return filled($status)
            ? strtolower(str_replace([' ', '-'], '_', trim((string) $status)))
            : null;
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
