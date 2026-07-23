<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Support\KeepzReceiver;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KeepzSplitSettingsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('general_setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $mode = strtolower((string) GeneralSetting::getMetaValue('keepz_options')) === 'live'
            ? 'live'
            : 'test';
        $prefix = $mode.'_keepz_';

        return view('admin.generalSettings.payment-methods.keepz-split', [
            'mode' => $mode,
            'splitStatus' => (string) GeneralSetting::getMetaValue('keepz_split_status'),
            'receiverType' => (string) GeneralSetting::getMetaValue($prefix.'split_platform_receiver_type'),
            'receiverIdentifier' => (string) GeneralSetting::getMetaValue($prefix.'split_platform_receiver_identifier'),
        ]);
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('general_setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'mode' => 'required|in:test,live',
            'split_status' => 'required|boolean',
            'receiver_type' => 'required|in:IBAN,BRANCH,USER',
            'receiver_identifier' => 'nullable|string|max:80',
        ]);

        $type = KeepzReceiver::normalizeType($validated['receiver_type']);
        $identifier = KeepzReceiver::normalizeIdentifier($validated['receiver_identifier'] ?? '', $type);
        $splitEnabled = (bool) $validated['split_status'];

        if ($splitEnabled && ! KeepzReceiver::isValid($type, $identifier)) {
            return redirect()->back()->withErrors([
                'receiver_identifier' => $type === KeepzReceiver::TYPE_IBAN
                    ? 'Enter a valid Georgian IBAN in the format GE00AA0000000000000000.'
                    : 'Enter a valid Keepz receiver UUID.',
            ])->withInput();
        }

        $prefix = $validated['mode'].'_keepz_';
        GeneralSetting::updateOrCreate(
            ['meta_key' => $prefix.'split_platform_receiver_type'],
            ['meta_value' => $type, 'module' => 2]
        );
        GeneralSetting::updateOrCreate(
            ['meta_key' => $prefix.'split_platform_receiver_identifier'],
            ['meta_value' => $identifier, 'module' => 2]
        );
        GeneralSetting::updateOrCreate(
            ['meta_key' => 'keepz_split_status'],
            ['meta_value' => $splitEnabled ? 'Active' : 'Inactive', 'module' => 2]
        );
        GeneralSetting::updateOrCreate(
            ['meta_key' => 'keepz_split_fallback_to_main_receiver'],
            ['meta_value' => '0', 'module' => 2]
        );

        return redirect()->back()->with('success', 'Keepz split settings updated successfully.');
    }
}
