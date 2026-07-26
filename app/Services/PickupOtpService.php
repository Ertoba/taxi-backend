<?php

namespace App\Services;

use App\Http\Controllers\Traits\SMSTrait;
use App\Models\Booking;
use App\Models\BookingExtension;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PickupOtpService
{
    use SMSTrait;

    /**
     * Send the booking-owned pickup PIN once per delivery window.
     *
     * The phone and PIN are always resolved from the authenticated booking
     * relationship. Client-supplied values are intentionally never trusted.
     */
    public function send(Booking $booking, BookingExtension $extension): array
    {
        $booking->loadMissing('user');

        $pickupOtp = preg_replace('/\D+/', '', (string) $extension->pick_otp) ?? '';
        $countryCode = preg_replace('/\D+/', '', (string) $booking->user?->phone_country) ?? '';
        $phone = preg_replace('/\D+/', '', (string) $booking->user?->phone) ?? '';

        if ($countryCode !== '' && ! str_starts_with($phone, $countryCode)) {
            $phone = $countryCode.$phone;
        }

        if (strlen($pickupOtp) < 4 || strlen($pickupOtp) > 8) {
            return [
                'success' => false,
                'status' => 409,
                'message' => trans('global.pickup_verification_not_ready'),
            ];
        }

        if (strlen($phone) < 9 || strlen($phone) > 15) {
            return [
                'success' => false,
                'status' => 422,
                'message' => trans('global.rider_phone_invalid'),
            ];
        }

        $deduplicationKey = 'pickup-otp-sms:'.hash(
            'sha256',
            $booking->id.'|'.$phone.'|'.$pickupOtp
        );

        if (! Cache::add($deduplicationKey, true, now()->addSeconds(90))) {
            return [
                'success' => true,
                'status' => 200,
                'duplicate' => true,
                'message' => trans('global.pickup_code_already_requested'),
            ];
        }

        try {
            $this->sendSMS(
                'Mili Taxi',
                'Mili Taxi მგზავრობა #'.$booking->id.' — აყვანის კოდი: '.$pickupOtp,
                $phone
            );

            Log::info('Ride pickup OTP SMS sent.', [
                'booking_id' => $booking->id,
                'ride_id' => $extension->ride_id,
                'driver_id' => $booking->host_id,
                'rider_id' => $booking->userid,
            ]);

            return [
                'success' => true,
                'status' => 200,
                'duplicate' => false,
                'message' => trans('global.pickup_code_sent'),
            ];
        } catch (\Throwable $exception) {
            Cache::forget($deduplicationKey);

            Log::warning('Ride pickup OTP SMS could not be sent.', [
                'booking_id' => $booking->id,
                'ride_id' => $extension->ride_id,
                'driver_id' => $booking->host_id,
                'exception' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 502,
                'message' => trans('global.pickup_code_send_failed'),
            ];
        }
    }
}
