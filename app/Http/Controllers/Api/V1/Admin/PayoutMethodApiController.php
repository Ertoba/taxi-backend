<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\EmailTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Controllers\Traits\MiscellaneousTrait;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\PushNotificationTrait;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Http\Controllers\Traits\SMSTrait;
use App\Http\Controllers\Traits\UserWalletTrait;
use App\Http\Controllers\Traits\VendorWalletTrait;
use App\Models\AppUser;
use App\Models\AppUserMeta;
use App\Models\PayoutMethod;
use App\Services\KeepzSplitService;
use App\Support\KeepzReceiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayoutMethodApiController extends Controller
{
    use EmailTrait, MediaUploadingTrait, MiscellaneousTrait, NotificationTrait, OTPTrait, PushNotificationTrait, ResponseTrait, SMSTrait, UserWalletTrait, VendorWalletTrait;

    public function getPayoutTypes(Request $request)
    {
        $payoutMethods = PayoutMethod::select('id', 'name')
            ->where('status', 1)
            ->where(function ($query): void {
                $query->whereNull('module')->orWhere('module', 2);
            })
            ->get()
            ->map(fn (PayoutMethod $method): array => [
                'id' => $method->id,
                'name' => strtolower($method->name),
            ]);

        return $this->addSuccessResponse(
            200,
            trans('front.payment_methods_retrieved_successfully'),
            ['payout_methods' => $payoutMethods]
        );
    }

    public function getPayoutMethods(Request $request)
    {
        $validator = Validator::make($request->all(), ['token' => 'required']);
        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $user = AppUser::where('token', $request->input('token'))->first();
        if (! $user || $user->user_type !== 'driver') {
            return $this->addErrorResponse(404, trans('front.user_not_found'), '');
        }

        $payoutMethods = collect();
        $user->metadata()->get()->each(function (AppUserMeta $meta) use ($payoutMethods): void {
            $payoutMethod = PayoutMethod::whereRaw('LOWER(name) = ?', [strtolower($meta->meta_key)])->first();
            if (! $payoutMethod) {
                return;
            }

            $decoded = json_decode((string) $meta->meta_value, true);
            if (! is_array($decoded)) {
                return;
            }

            if (strtolower($meta->meta_key) === KeepzSplitService::DRIVER_META_KEY) {
                $decoded['keepz_receiver_type'] = KeepzReceiver::normalizeType(
                    $decoded['keepz_receiver_type'] ?? KeepzReceiver::TYPE_IBAN
                );
                $decoded['keepz_receiver_identifier'] = KeepzReceiver::normalizeIdentifier(
                    $decoded['keepz_receiver_identifier'] ?? '',
                    $decoded['keepz_receiver_type']
                );
                $decoded['keepz_receiver_identifier_masked'] = KeepzReceiver::mask(
                    $decoded['keepz_receiver_type'],
                    $decoded['keepz_receiver_identifier']
                );
            }

            $payoutMethods->push([
                'id' => $payoutMethod->id,
                'payout_method' => $payoutMethod->name,
                'details' => $decoded,
            ]);
        });

        return $this->addSuccessResponse(
            200,
            trans('front.payment_methods_retrieved_successfully'),
            ['payout_methods' => $payoutMethods]
        );
    }

    public function updatePayoutMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'payout_methods' => 'required|array|min:1',
            'payout_methods.*.payout_method_id' => 'required|exists:payout_method,id',
            'payout_methods.*.is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $user = AppUser::where('token', $request->input('token'))->first();
        if (! $user || $user->user_type !== 'driver') {
            return $this->addErrorResponse(404, trans('front.user_not_found'), '');
        }

        $responseData = [];

        foreach ($request->input('payout_methods') as $methodData) {
            $payoutMethod = PayoutMethod::whereKey($methodData['payout_method_id'])
                ->where('status', 1)
                ->first();
            if (! $payoutMethod) {
                return $this->addErrorResponse(422, 'Invalid or inactive payout method.', '');
            }

            $type = strtolower($payoutMethod->name);
            $validatedData = $this->validateMethodData($type, $methodData);
            if ($validatedData instanceof \Illuminate\Contracts\Validation\Validator) {
                return $this->errorComputing($validatedData);
            }

            $validatedData['id'] = $payoutMethod->id;
            $validatedData['is_active'] = isset($methodData['is_active'])
                ? (int) ((bool) $methodData['is_active'])
                : 0;

            AppUserMeta::updateOrCreate(
                ['user_id' => $user->id, 'meta_key' => $type],
                ['meta_value' => json_encode($validatedData, JSON_UNESCAPED_SLASHES)]
            );

            $responseData[] = [
                'id' => $payoutMethod->id,
                'payout_method' => $type,
                'details' => $validatedData,
            ];
        }

        return $this->addSuccessResponse(
            200,
            trans('front.payment_method_saved_successfully'),
            ['payout_methods' => $responseData]
        );
    }

    private function validateMethodData(string $type, array $methodData): array|\Illuminate\Contracts\Validation\Validator
    {
        if ($type === KeepzSplitService::DRIVER_META_KEY) {
            $validator = Validator::make($methodData, [
                'account_name' => 'required|string|max:120',
                'keepz_receiver_type' => ['required', Rule::in([KeepzReceiver::TYPE_IBAN])],
                'keepz_receiver_identifier' => ['required', 'string', 'max:40'],
            ]);
            if ($validator->fails()) {
                return $validator;
            }

            $validated = $validator->validated();
            $validated['keepz_receiver_type'] = KeepzReceiver::TYPE_IBAN;
            $validated['keepz_receiver_identifier'] = KeepzReceiver::normalizeIdentifier(
                $validated['keepz_receiver_identifier'],
                KeepzReceiver::TYPE_IBAN
            );

            if (! KeepzReceiver::isValid(
                $validated['keepz_receiver_type'],
                $validated['keepz_receiver_identifier']
            )) {
                $validator->errors()->add(
                    'keepz_receiver_identifier',
                    'Enter a valid Georgian IBAN in the format GE00AA0000000000000000.'
                );

                return $validator;
            }

            $validated['keepz_receiver_identifier_masked'] = KeepzReceiver::mask(
                $validated['keepz_receiver_type'],
                $validated['keepz_receiver_identifier']
            );

            return $validated;
        }

        $rules = $type === 'bank account'
            ? [
                'account_name' => 'required|string|max:120',
                'bank_name' => 'required|string|max:120',
                'branch_name' => 'nullable|string|max:120',
                'account_number' => 'required|string|max:80',
                'iban' => 'nullable|string|max:40',
                'swift_code' => 'nullable|string|max:20',
            ]
            : [
                'email' => 'required|string|max:190',
                'note' => 'nullable|string|max:1000',
            ];

        $validator = Validator::make($methodData, $rules);

        return $validator->fails() ? $validator : $validator->validated();
    }
}
