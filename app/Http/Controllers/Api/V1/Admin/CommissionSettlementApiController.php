<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Http\Controllers\Traits\VendorWalletTrait;
use App\Models\AppUser;
use App\Models\CommissionSettlement;
use App\Strategies\KeepzStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CommissionSettlementApiController extends Controller
{
    use ResponseTrait, VendorWalletTrait;

    public function store(Request $request)
    {
        $driver = AppUser::where('token', $request->input('token'))
            ->where('user_type', 'driver')
            ->first();
        if (! $driver) {
            return $this->addErrorResponse(401, trans('global.token_not_match'), '');
        }

        $lock = Cache::lock('cash-commission-settlement:'.$driver->id, 30);
        if (! $lock->get()) {
            return $this->addErrorResponse(409, trans('global.commission_payment_in_progress'), '');
        }

        try {
            $summary = $this->getVendorWalletSummary($driver->id);
            $amount = (float) str_replace(',', '', $summary['commissionSettlementAmount']);
            if ($amount <= 0 || ! $summary['canSettleCommission']) {
                return $this->addErrorResponse(
                    422,
                    trans('global.commission_threshold_not_reached'),
                    ''
                );
            }

            $active = CommissionSettlement::query()
                ->where('driver_id', $driver->id)
                ->whereIn('status', ['pending', 'processing'])
                ->latest('id')
                ->first();
            if (
                $active
                && filled($active->checkout_url)
                && (float) $active->amount <= $amount
            ) {
                return $this->addSuccessResponse(200, trans('global.commission_payment_ready'), [
                    'settlement_id' => $active->id,
                    'amount' => $active->amount,
                    'currency_code' => $active->currency_code,
                    'checkout_url' => $active->checkout_url,
                ]);
            }
            if ($active) {
                return $this->addErrorResponse(
                    409,
                    trans('global.commission_payment_pending'),
                    ''
                );
            }

            $settlement = CommissionSettlement::create([
                'driver_id' => $driver->id,
                'integrator_order_id' => (string) Str::uuid(),
                'amount' => $amount,
                'currency_code' => 'GEL',
                'status' => 'pending',
            ]);

            $keepz = new KeepzStrategy;
            $order = $keepz->createStandaloneOrder(
                (string) $settlement->integrator_order_id,
                $amount,
                'GEL',
                route('commission-settlement.return', ['settlement' => $settlement->id]),
                route('commission-settlement.cancel', ['settlement' => $settlement->id]),
                route('commission-settlement.callback', ['settlement' => $settlement->id])
            );

            $checkoutUrl = data_get($order, 'checkout_url');
            $settlement->checkout_url = is_string($checkoutUrl) ? $checkoutUrl : null;
            $settlement->gateway_payload = data_get($order, 'payload', []);
            $settlement->status = $settlement->checkout_url ? 'pending' : 'failed';
            $settlement->save();

            if (! $settlement->checkout_url) {
                return $this->addErrorResponse(502, trans('global.keepz_payment_init_failed'), '');
            }

            return $this->addSuccessResponse(200, trans('global.commission_payment_ready'), [
                'settlement_id' => $settlement->id,
                'amount' => $settlement->amount,
                'currency_code' => $settlement->currency_code,
                'checkout_url' => $settlement->checkout_url,
            ]);
        } catch (Throwable $exception) {
            Log::error('Cash commission settlement initialization failed.', [
                'driver_id' => $driver->id,
                'exception' => $exception->getMessage(),
            ]);

            return $this->addErrorResponse(500, trans('global.commission_payment_init_failed'), '');
        } finally {
            $lock->release();
        }
    }
}
