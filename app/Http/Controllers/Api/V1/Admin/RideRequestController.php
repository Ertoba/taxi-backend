<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PushNotificationTrait;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Models\BookingExtension;
use App\Models\RideRequest;
use App\Services\PickupOtpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class RideRequestController extends Controller
{
    use PushNotificationTrait, ResponseTrait;

    /**
     * Notify a rider that an authenticated driver accepted the ride.
     */
    public function notifyRideAccepted(Request $request)
    {
        $driver = $request->user();

        if (! $driver || $driver->user_type !== 'driver') {
            return response()->json([
                'success' => false,
                'message' => 'Only authenticated drivers may send this notification.',
            ], 403);
        }

        $validated = $request->validate([
            'subscription_id' => ['required', 'uuid'],
        ]);

        return $this->sendFcmMessage(
            $validated['subscription_id'],
            'მგზავრობა მიღებულია',
            'მძღოლმა მიიღო შეკვეთა და თქვენსკენ მოემართება.'
        );
    }

    /**
     * Send the pickup verification PIN after a driver accepts a ride.
     *
     * Phone/PIN data is resolved only from the server-owned booking.
     */
    public function sendPickupOtp(Request $request)
    {
        $driver = $request->user();

        if (! $driver || $driver->user_type !== 'driver') {
            return response()->json([
                'success' => false,
                'message' => 'Only authenticated drivers may send a pickup code.',
            ], 403);
        }

        $validated = $request->validate([
            'ride_id' => ['required', 'string', 'max:128'],
        ]);

        $rideId = trim((string) $validated['ride_id']);
        $extension = BookingExtension::with('booking.user')
            ->where('ride_id', $rideId)
            ->latest('id')
            ->first();

        if (! $extension?->booking) {
            return response()->json([
                'success' => false,
                'message' => trans('global.pickup_verification_not_ready'),
            ], 409);
        }

        if ((string) $extension->booking->host_id !== (string) $driver->id) {
            return response()->json([
                'success' => false,
                'message' => 'This ride is not assigned to the authenticated driver.',
            ], 403);
        }

        $delivery = app(PickupOtpService::class)
            ->send($extension->booking, $extension);

        $status = (int) ($delivery['status'] ?? ($delivery['success'] ? 200 : 502));
        unset($delivery['status']);

        return response()->json($delivery, $status);
    }

    /**
     * Create a new ride request in MongoDB.
     */
    public function createRide(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'pickup_location' => 'required|string',
            'drop_location' => 'required|string',
        ]);

        // Insert into MongoDB
        $ride = RideRequest::create(array_merge($validated, [
            'status' => 'pending',
            'requested_at' => now(),
        ]));

        return response()->json([
            'success' => true,
            'data' => $ride,
        ]);
    }

    /**
     * Get all ride requests.
     */
    public function getRides()
    {
        // Get start and end of today
        $startOfDay = Carbon::today()->startOfDay();
        $endOfDay = Carbon::today()->endOfDay();

        // Fetch rides from MongoDB where requested_at is today
        $rides = RideRequest::whereBetween('requested_at', [$startOfDay, $endOfDay])->get();

        return response()->json([
            'success' => true,
            'data' => $rides,
        ]);
    }

    public function updateRideStatus(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,accepted,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(400, trans('global.Validation_Error'));
        }

        // Get validated data
        $validated = $validator->validated();

        // Find the ride by ID
        $ride = RideRequest::find($id);

        if (! $ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found',
            ], 404);
        }

        // Update status
        $ride->status = $validated['status'];
        $ride->save();

        return response()->json([
            'success' => true,
            'data' => $ride,
            'message' => 'Ride status updated successfully',
        ]);
    }
}
