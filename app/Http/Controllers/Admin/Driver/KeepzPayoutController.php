<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\AppUserMeta;
use App\Models\PayoutMethod;
use App\Services\KeepzSplitService;
use App\Support\KeepzReceiver;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KeepzPayoutController extends Controller
{
    public function edit(int $driverId)
    {
        abort_if(Gate::denies('app_user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $appUser = AppUser::whereKey($driverId)
            ->where('user_type', 'driver')
            ->firstOrFail();
        $metadata = AppUserMeta::where('user_id', $appUser->id)
            ->where('meta_key', KeepzSplitService::DRIVER_META_KEY)
            ->first();
        $details = $metadata ? json_decode((string) $metadata->meta_value, true) : [];

        return view('admin.appUsers.driver.keepz_payout_method', [
            'appUser' => $appUser,
            'details' => is_array($details) ? $details : [],
        ]);
    }

    public function update(Request $request, int $driverId)
    {
        abort_if(Gate::denies('app_user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $driver = AppUser::whereKey($driverId)
            ->where('user_type', 'driver')
            ->firstOrFail();
        $validated = $request->validate([
            'account_name' => 'required|string|max:120',
            'iban' => 'required|string|max:40',
            'is_active' => 'nullable|boolean',
        ]);

        $iban = KeepzReceiver::normalizeIdentifier($validated['iban'], KeepzReceiver::TYPE_IBAN);
        if (! KeepzReceiver::isValid(KeepzReceiver::TYPE_IBAN, $iban)) {
            return redirect()->back()->withErrors([
                'iban' => 'Enter a valid Georgian IBAN in the format GE00AA0000000000000000.',
            ])->withInput();
        }

        $payoutMethod = PayoutMethod::whereRaw('LOWER(name) = ?', [KeepzSplitService::DRIVER_META_KEY])
            ->where('status', 1)
            ->firstOrFail();
        $details = [
            'id' => $payoutMethod->id,
            'account_name' => trim($validated['account_name']),
            'keepz_receiver_type' => KeepzReceiver::TYPE_IBAN,
            'keepz_receiver_identifier' => $iban,
            'keepz_receiver_identifier_masked' => KeepzReceiver::mask(KeepzReceiver::TYPE_IBAN, $iban),
            'is_active' => $request->boolean('is_active') ? 1 : 0,
        ];

        AppUserMeta::updateOrCreate(
            ['user_id' => $driver->id, 'meta_key' => KeepzSplitService::DRIVER_META_KEY],
            ['meta_value' => json_encode($details, JSON_UNESCAPED_SLASHES)]
        );

        return redirect()->back()->with('success', 'Driver Keepz IBAN updated successfully.');
    }
}
