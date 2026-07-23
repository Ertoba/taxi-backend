<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Models\AppUser;
use App\Models\KeepzSplitSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KeepzSettlementApiController extends Controller
{
    use ResponseTrait;

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $driver = AppUser::where('token', $request->input('token'))
            ->where('user_type', 'driver')
            ->first();
        if (! $driver) {
            return $this->addErrorResponse(404, trans('front.user_not_found'), '');
        }

        $offset = (int) $request->input('offset', 0);
        $limit = (int) $request->input('limit', 20);
        $settlements = KeepzSplitSettlement::query()
            ->where('driver_id', $driver->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(fn (KeepzSplitSettlement $settlement): array => [
                'id' => $settlement->id,
                'booking_id' => $settlement->booking_id,
                'integrator_order_id' => $settlement->integrator_order_id,
                'currency_code' => $settlement->currency_code,
                'driver_amount' => $settlement->driver_amount,
                'gateway_status' => $settlement->gateway_status,
                'receiver_type' => $settlement->driver_receiver_type,
                'receiver_identifier_masked' => $settlement->driver_receiver_masked,
                'paid_at' => optional($settlement->paid_at)->toIso8601String(),
            ]);

        return $this->addSuccessResponse(200, 'Keepz split settlements retrieved successfully.', [
            'settlements' => $settlements,
            'offset' => $settlements->isEmpty() ? -1 : $offset + $settlements->count(),
            'limit' => $limit,
        ]);
    }
}
