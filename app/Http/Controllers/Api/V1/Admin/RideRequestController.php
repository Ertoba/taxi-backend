<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PushNotificationTrait;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Http\Controllers\Traits\SMSTrait;
use App\Models\RideRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Validator;

class RideRequestController extends Controller
{
    use PushNotificationTrait, ResponseTrait, SMSTrait;

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
     * The current realtime ride flow lives in Firebase, so the authenticated
     * driver app forwards the rider phone and PIN from that ride document.
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
            'rider_id' => ['nullable', 'string', 'max:128'],
            'rider_phone' => ['required', 'string', 'max:32'],
            'rider_phone_country' => ['nullable', 'string', 'max:8'],
            'pickup_otp' => ['required', 'digits_between:4,8'],
        ]);

        $rideId = trim((string) $validated['ride_id']);
        $countryCode = preg_replace('/\D+/', '', (string) ($validated['rider_phone_country'] ?? '')) ?? '';
        $phone = preg_replace('/\D+/', '', (string) $validated['rider_phone']) ?? '';

        if ($countryCode !== '' && ! str_starts_with($phone, $countryCode)) {
            $phone = $countryCode.$phone;
        }

        if (strlen($phone) < 9 || strlen($phone) > 15) {
            return response()->json([
                'success' => false,
                'message' => 'The rider phone number is invalid.',
            ], 422);
        }

        $deduplicationKey = 'pickup-otp-sms:'.hash(
            'sha256',
            $driver->id.'|'.$rideId.'|'.$phone.'|'.$validated['pickup_otp']
        );

        if (! Cache::add($deduplicationKey, true, now()->addSeconds(90))) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'The pickup verification code was already requested.',
            ]);
        }

        try {
            $this->sendSMS(
                'Mili Taxi',
                'Mili Taxi pickup PIN: '.$validated['pickup_otp'],
                $phone
            );

            Log::info('Ride pickup OTP SMS sent.', [
                'ride_id' => $rideId,
                'driver_id' => $driver->id,
                'rider_id' => $validated['rider_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pickup verification code sent.',
            ]);
        } catch (\Throwable $exception) {
            Cache::forget($deduplicationKey);

            Log::warning('Ride pickup OTP SMS could not be sent.', [
                'ride_id' => $rideId,
                'driver_id' => $driver->id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send the pickup verification code.',
            ], 502);
        }
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
