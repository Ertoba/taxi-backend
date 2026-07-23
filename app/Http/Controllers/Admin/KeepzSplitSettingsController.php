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

        $mode = $this->activeMode();
        $prefix = $mode.'_keepz_';

        return view('admin.generalSettings.payment-methods.keepz-split', [
            'mode' => $mode,
            'splitStatus' => (string) GeneralSetting::getMetaValue('keepz_split_status'),
            'mainReceiverType' => (string) GeneralSetting::getMetaValue($prefix.'receiver_type'),
            'mainReceiverId' => (string) GeneralSetting::getMetaValue($prefix.'receiver_id'),
            'platformIban' => (string) GeneralSetting::getMetaValue($prefix.'split_platform_iban'),
            'mappingConfirmed' => (string) GeneralSetting::getMetaValue(
                $prefix.'split_platform_mapping_confirmed'
            ) === '1',
        ]);
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('general_setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'split_status' => 'required|boolean',
            'platform_iban' => 'nullable|string|max:40',
            'mapping_confirmed' => 'nullable|boolean',
        ]);

        $splitEnabled = (bool) $validated['split_status'];
        $mappingConfirmed = $request->boolean('mapping_confirmed');
        $mode = $this->activeMode();
        $prefix = $mode.'_keepz_';
        $mainReceiverType = KeepzReceiver::normalizeType(
            GeneralSetting::getMetaValue($prefix.'receiver_type')
        );
        $mainReceiverId = KeepzReceiver::normalizeIdentifier(
            GeneralSetting::getMetaValue($prefix.'receiver_id'),
            $mainReceiverType
        );
        $platformIban = KeepzReceiver::normalizeIdentifier(
            $validated['platform_iban'] ?? '',
            KeepzReceiver::TYPE_IBAN
        );

        if ($splitEnabled) {
            if (
                $mainReceiverType !== KeepzReceiver::TYPE_BRANCH
                || ! KeepzReceiver::isValid($mainReceiverType, $mainReceiverId)
            ) {
                return redirect()->back()->withErrors([
                    'platform_iban' => 'The active Keepz main receiver must be configured as a valid BRANCH UUID before Split can be enabled.',
                ])->withInput();
            }

            if (! KeepzReceiver::isValid(KeepzReceiver::TYPE_IBAN, $platformIban)) {
                return redirect()->back()->withErrors([
                    'platform_iban' => 'Enter the Georgian IBAN assigned by Keepz to the active main BRANCH receiver.',
                ])->withInput();
            }

            if (! $mappingConfirmed) {
                return redirect()->back()->withErrors([
                    'mapping_confirmed' => 'Confirm that Keepz has mapped the displayed main BRANCH receiver to this platform IBAN.',
                ])->withInput();
            }
        }

        GeneralSetting::updateOrCreate(
            ['meta_key' => $prefix.'split_platform_iban'],
            ['meta_value' => $platformIban, 'module' => 2]
        );
        GeneralSetting::updateOrCreate(
            ['meta_key' => $prefix.'split_platform_mapping_confirmed'],
            ['meta_value' => $mappingConfirmed ? '1' : '0', 'module' => 2]
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

    private function activeMode(): string
    {
        return strtolower((string) GeneralSetting::getMetaValue('keepz_options')) === 'live'
            ? 'live'
            : 'test';
    }
}
