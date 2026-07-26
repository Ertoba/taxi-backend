<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CommissionSettlement;
use App\Models\VendorWallet;
use App\Strategies\KeepzStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommissionSettlementController extends Controller
{
    public function complete(Request $request, CommissionSettlement $settlement)
    {
        return $this->verify($request, $settlement)
            ? redirect()->route('commission-settlement.success')
            : redirect()->route('commission-settlement.fail');
    }

    public function callback(Request $request, CommissionSettlement $settlement)
    {
        $verified = $this->verify($request, $settlement);

        return response()->json(['verified' => $verified], $verified ? 200 : 409);
    }

    public function cancel(Request $request, CommissionSettlement $settlement)
    {
        if ($this->verify($request, $settlement)) {
            return redirect()->route('commission-settlement.success');
        }

        CommissionSettlement::whereKey($settlement->id)
            ->whereIn('status', ['pending', 'processing'])
            ->update(['status' => 'cancelled']);

        return redirect()->route('commission-settlement.fail');
    }

    public function success()
    {
        return response()->view('Front.CommissionSettlement.Success')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function fail()
    {
        return response()->view('Front.CommissionSettlement.Fail')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function verify(Request $request, CommissionSettlement $settlement): bool
    {
        if ($settlement->status === 'completed') {
            return true;
        }

        try {
            $keepz = new KeepzStrategy;
            $result = $keepz->verifyStandaloneOrder((string) $settlement->integrator_order_id);
            if (data_get($result, 'status') !== 'success') {
                return false;
            }

            $actualOrder = (string) data_get($result, 'integrator_order_id');
            $actualAmount = data_get($result, 'amount');
            $actualCurrency = data_get($result, 'currency');
            if (
                ! hash_equals((string) $settlement->integrator_order_id, $actualOrder)
                || ($actualAmount !== null && abs((float) $actualAmount - (float) $settlement->amount) > 0.01)
                || ($actualCurrency !== null && strtoupper((string) $actualCurrency) !== 'GEL')
            ) {
                return false;
            }

            DB::transaction(function () use ($settlement, $result): void {
                $locked = CommissionSettlement::whereKey($settlement->id)->lockForUpdate()->first();
                if (! $locked || $locked->status === 'completed') {
                    return;
                }

                VendorWallet::create([
                    'vendor_id' => $locked->driver_id,
                    'booking_id' => 0,
                    'payout_id' => 0,
                    'amount' => $locked->amount,
                    'type' => 'credit',
                    'description' => 'Verified Keepz cash commission settlement #'.$locked->id,
                ]);

                $locked->status = 'completed';
                $locked->gateway_payload = data_get($result, 'payload', []);
                $locked->paid_at = now();
                $locked->save();
            }, 3);

            return true;
        } catch (Throwable $exception) {
            Log::error('Cash commission settlement verification failed.', [
                'settlement_id' => $settlement->id,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
