<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BookingAvailableTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Controllers\Traits\MiscellaneousTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\PaymentStatusUpdaterTrait;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Http\Controllers\Traits\SMSTrait;
use App\Http\Controllers\Traits\UserWalletTrait;
use App\Http\Controllers\Traits\VendorWalletTrait;
use App\Models\AddCoupon;
use App\Models\AppUser;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\CancellationPolicy;
use App\Models\GeneralSetting;
use App\Models\Modern\Currency;
use App\Models\Modern\Item;
use App\Models\Modern\ItemType;
use App\Models\Review;
use App\Models\VendorWallet;
use App\Services\CommissionCalculator;
use App\Services\PickupOtpService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingApiController extends Controller
{
    use BookingAvailableTrait, MediaUploadingTrait, MiscellaneousTrait, OTPTrait, PaymentStatusUpdaterTrait, ResponseTrait, SMSTrait, UserWalletTrait, VendorWalletTrait;

    public function bookItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:rental_items,id',
            'driver_id' => 'required|exists:app_users,id',
            'token' => 'required|exists:app_users,token',
            'item_type_id' => 'required|exists:rental_item_types,id',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
            'estimated_distance_km' => 'required|numeric|min:0.1',
            'pickup_address' => 'required|string',
            'dropoff_address' => 'required|string',
            'service_charge' => 'nullable|numeric',
            'wallet_amount' => 'nullable|numeric',
            'payment_method' => 'nullable|string',
            'currency_code' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'coupon_discount' => 'nullable|numeric',
            'discount_price' => 'nullable|numeric',
            'amount_to_pay' => 'nullable|numeric',
            'estimated_duration_min' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $userId = $this->checkUserByToken($request->token);
        if (! $userId) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }

        $walletAmount = $request->wallet_amount ?? 0;
        $distance = $request->estimated_distance_km;
        $itemTypeId = $request->item_type_id;
        $couponCode = $request->coupon_code;
        $currencyCode = $request->currency_code ?? 'USD';
        $itemId = $request->input('item_id');
        $conversionRate = Currency::getValueByCurrencyCode($currencyCode);
        $pricingResult = $this->getItemPricesDetails($itemTypeId, $distance, $couponCode, $walletAmount, $currencyCode, $conversionRate);
        $pricingPayload = $pricingResult->getData(true);
        if (($pricingPayload['status'] ?? 500) !== 200 || ! isset($pricingPayload['data'])) {
            return $pricingResult;
        }

        $pricing = $pricingPayload['data'];
        $serverBaseFare = round($this->convertFormattedNumber($pricing['price_before_discount'] ?? 0), 2);
        $serverTotalPrice = round($this->convertFormattedNumber($pricing['total_price'] ?? 0), 2);
        $serverGrossPrice = round($this->convertFormattedNumber($pricing['gross_price'] ?? 0), 2);
        $serverWalletAmount = round($this->convertFormattedNumber($pricing['wallet_amount'] ?? 0), 2);

        // A missing fare is a pricing/configuration error, never a paid wallet ride.
        if ($serverBaseFare <= 0 || ($serverGrossPrice <= 0 && $serverWalletAmount <= 0)) {
            Log::warning('Ride booking rejected because the authoritative fare was zero.', [
                'user_id' => $userId,
                'driver_id' => $request->driver_id,
                'item_type_id' => $itemTypeId,
                'distance_km' => (float) $distance,
                'currency_code' => $currencyCode,
            ]);

            return $this->addErrorResponse(422, 'Ride fare is not configured for this vehicle type.', '');
        }

        if ($request->filled('amount_to_pay')) {
            $clientAmount = round($this->convertFormattedNumber($request->input('amount_to_pay')), 2);
            if (abs($clientAmount - $serverGrossPrice) > 0.01) {
                Log::warning('Ride booking rejected because the client fare was stale.', [
                    'user_id' => $userId,
                    'driver_id' => $request->driver_id,
                    'item_type_id' => $itemTypeId,
                    'client_amount' => $clientAmount,
                    'server_amount' => $serverGrossPrice,
                    'currency_code' => $currencyCode,
                ]);

                return $this->addErrorResponse(409, 'The fare changed. Please request the ride again.', '');
            }
        }

        $booking = new Booking;
        $booking->itemid = $itemId;
        $booking->userid = $userId;
        $booking->host_id = $request->driver_id;
        $booking->ride_date = $request->ride_date;
        $booking->payment_status = 'notpaid';
        $booking->price_per_km = $pricing['price_per_km'];
        $booking->base_price = $serverBaseFare;
        $booking->total = $serverTotalPrice;
        $booking->wall_amt = $serverWalletAmount;
        $booking->payment_status = 'notpaid';
        $requestedPaymentMethod = strtolower(trim((string) $request->input('payment_method')));
        $onlinePaymentsEnabled = strtolower((string) GeneralSetting::getMetaValue('onlinepayment')) === 'active';
        $keepzEnabled = strtolower((string) GeneralSetting::getMetaValue('keepz_status')) === 'active';
        $onlinePaymentSelected = $onlinePaymentsEnabled
            && $keepzEnabled
            && in_array($requestedPaymentMethod, ['online', 'card', 'keepz'], true);
        $booking->payment_method = $onlinePaymentSelected ? 'keepz' : 'cash';
        $booking->currency_code = $request->currency_code ?? 'USD';
        $booking->coupon_code = $pricing['coupon_code'];
        $booking->coupon_discount = $pricing['coupon_discount'];
        $booking->discount_price = $pricing['coupon_discount'];
        $booking->amount_to_pay = $serverGrossPrice;
        $booking->rating = 0;
        $booking->module = $request->module_id;
        $booking->status = 'Accepted';
        $bookingItem = Item::where('id', $itemId)->get()->map(function ($bookingItem) {
            $formattedItem = $this->formatItemData($bookingItem);
            $itemType = ItemType::find($bookingItem->item_type_id);
            $formattedItem['item_type'] = $itemType ? $itemType->name : null;
            $formattedItem['item_info'] = $this->getModuleInfoValues('', $bookingItem->id);

            return $formattedItem;
        });

        $vehicleSpeed = $bookingItem[0]['average_speed_kmph'] ?? 40;

        $estimatedDurationMin = null;

        if ($vehicleSpeed && $vehicleSpeed > 0 && $distance > 0) {
            $estimatedDurationMin = ($distance / $vehicleSpeed) * 60;
        }

        $commissions = $this->calculateCommissions(
            $this->convertFormattedNumber($pricing['total_price']),
            $bookingItem[0]['item_type_id']
        );
        $booking->admin_commission = $commissions['admin_commission'];
        $booking->vendor_commission = $commissions['vendor_commission'];

        $booking->save();

        $bookingExtension = new BookingExtension;
        $bookingExtension->booking_id = $booking->id;
        $bookingExtension->pickup_location = [
            'latitude' => $request->pickup_latitude,
            'longitude' => $request->pickup_longitude,
            'address' => $request->pickup_address,
        ];
        $bookingExtension->dropoff_location = [
            'latitude' => $request->dropoff_latitude,
            'longitude' => $request->dropoff_longitude,
            'address' => $request->dropoff_address,
        ];
        $bookingExtension->estimated_distance_km = $request->estimated_distance_km ?? null;
        $bookingExtension->estimated_duration_min = $estimatedDurationMin ?? null;
        $bookingExtension->pick_otp = $this->createPickDropOTP();
        $bookingExtension->drop_otp = $this->createPickDropOTP();
        $bookingExtension->ride_id = $request->ride_id;
        $bookingExtension->service_type_id = $request->service_type_id ?? '1';
        $bookingExtension->save();

        app(PickupOtpService::class)->send($booking, $bookingExtension);

        if ($booking->wall_amt > 0) {
            $this->addWalletTransaction($userId, $booking->wall_amt, 'debit', 'Wallet used for ride #'.$booking->id);
        }

        $fullyCoveredByWallet = $serverTotalPrice > 0
            && $serverGrossPrice <= 0.009
            && $serverWalletAmount >= ($serverTotalPrice - 0.009);

        if ($fullyCoveredByWallet) {
            $booking->payment_status = 'paid';
            $booking->payment_method = 'wallet';
            $booking->save();
            $responseData = [
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'payment_url' => route('payment_success', ['booking' => $booking->id]),
            ];

            return $this->addSuccessResponse(200, trans('global.booked_succesfully'), $responseData);
        }

        $bookingCount = Booking::where('userid', $userId)
            ->whereIn('status', ['Ongoing', 'Arrived', 'Accepted', 'Completed'])
            ->count();

        $booking->payment_status = 'notpaid';
        $booking->save();
        $firstBookingCoupon = GeneralSetting::where('meta_key', 'first_booking_coupon')->value('meta_value');

        $responseData = [
            'booking_id' => $booking->id,
            'ride_id' => $booking->extension->ride_id ?? 0,
            'booking_token' => $booking->token ?? 0,
            'pickup_otp' => $booking->extension->pick_otp ?? 0,
            'drop_otp' => $booking->extension->drop_otp ?? 0,
            'status' => $booking->status,
            'bookingCount' => $bookingCount ?? 0,
            'coupon' => $firstBookingCoupon ?: null,
            'payment_url' => route('payment_methods', ['booking' => $booking->id]),
        ];

        return $this->addSuccessResponse(200, trans('global.booked_succesfully'), $responseData);
    }

    private function calculateCommissions($basePrice, $itemTypeId)
    {
        $itemType = ItemType::find($itemTypeId);
        $adminCommissionPercent = optional($itemType->cityFare)->admin_commission ?? 0;
        return app(CommissionCalculator::class)->calculate(
            (float) $basePrice,
            (float) $adminCommissionPercent
        );
    }

    public function bookingRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|exists:app_users,token',
            'booking_status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        $user_id = $this->checkUserByToken($request->token);
        if (! $user_id) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }

        $module = 2;
        $bookingsQuery = Booking::with(['extension', 'review', 'host'])
            ->where('userid', $user_id)
            ->where('module', $module)
            ->whereIn('status', ['Completed', 'Cancelled']);

        if ($request->filled('booking_status')) {
            $bookingsQuery->where('status', $request->input('booking_status'));
        }

        $bookings = $bookingsQuery
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($booking) {
                $review = $booking->review;
                $booking['review_status'] = $review ? '1' : '0';
                $booking['review_rating'] = $review->guest_rating ?? '';
                $booking['review'] = $review->guest_message ?? '';
                $host = $booking->host;
                $booking['host_name'] = $host ? ($host->first_name.' '.$host->last_name) : '';
                $booking['host_number'] = $host->phone ?? '';
                $booking['host_email'] = $host->email ?? '';
                $booking['host_phone_country'] = $host->phone_country ?? '';
                $extension = $booking->extension;
                $booking['doorStep_price'] = $extension->doorStep_price ?? 0;
                $booking['pickup_otp'] = $extension->pick_otp ?? 0;
                $booking['pickup_location'] = $extension->pickup_location ?? null;
                $booking['dropoff_location'] = $extension->dropoff_location ?? null;
                $booking['estimated_distance_km'] = $extension->estimated_distance_km ?? 0;
                $booking['firebase_json'] = json_decode($booking->firebase_json);
                $parcelImages = [];

                foreach (['delivery_image_1', 'delivery_image_2'] as $collection) {
                    foreach ($booking->getMedia($collection) as $media) {
                        $parcelImages[] = $media->getUrl();
                    }
                }

                if (! empty($parcelImages)) {
                    $booking['delivery_images'] = $parcelImages;
                }
                unset($booking->extension, $booking->review, $booking->host);

                return $booking;
            });

        $nextOffset = $bookings->isEmpty() ? -1 : ($offset + count($bookings));

        return $this->addSuccessResponse(200, trans('global.booking_list'), [
            'Bookings' => $bookings,
            'offset' => $nextOffset,
            'limit' => $limit,
        ]);
    }

    public function vendorBookingRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $user_id = $this->checkUserByToken($request->token);

        if (! $user_id) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }

        $type = $request->type;
        $module = $this->getModuleIdOrDefault($request);

        $query = Booking::with(['extension', 'user:id,first_name,last_name,phone,email,phone_country'])
            ->where('host_id', $user_id)
            ->where('module', $module);

        switch ($type) {
            case 'rejected':
                $query->where('status', 'Rejected');
                break;
            case 'cancelled':
                $query->where('status', 'Cancelled');
                break;
            case 'previous':
                $query->where('check_out', '<', now()->format('Y-m-d'));
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            default:
                return $this->addErrorResponse(400, 'Invalid type');
        }

        $bookings = $query->orderByDesc('id')->skip($offset)->take($limit)->get()
            ->map(function ($booking) use ($type) {
                if ($booking->user) {
                    $booking['user_name'] = $booking->user->first_name.' '.$booking->user->last_name;
                    $booking['user_number'] = $booking->user->phone;
                    $booking['user_email'] = $booking->user->email;
                    $booking['user_phone_country'] = $booking->user->phone_country;
                } else {
                    $booking['user_name'] = $booking['user_number'] = $booking['user_email'] = $booking['user_phone_country'] = null;
                }
                $booking['doorStep_price'] = $booking->extension->doorStep_price ?? 0;
                $booking['pickup_otp'] = $booking->extension->pick_otp ?? 0;
                $booking['pickup_location'] = $booking->extension->pickup_location ?? null;
                $booking['dropoff_location'] = $booking->extension->dropoff_location ?? null;
                $booking['estimated_distance_km'] = $booking->extension->estimated_distance_km ?? 0;
                $booking['estimated_duration_min'] = $booking->extension->estimated_duration_min ?? 0;
                unset($booking->extension);
                $today = now()->format('Y-m-d');
                $booking['is_item_delivered_button'] = ($booking->check_in === $today && $booking->is_item_received == 0 && $booking->is_item_delivered == 0) ? 'yes' : 'no';
                $booking['is_item_returned_button'] = ($booking->is_item_delivered == 1 && $booking->is_item_returned == 0) ? 'yes' : 'no';
                if (in_array($type, ['previous', 'completed'])) {
                    $review = Review::where('bookingid', $booking->id)->where('host_rating', '>', 0)->first();
                    $booking['review_status'] = $review ? '1' : '0';
                    $booking['review_rating'] = $review->host_rating ?? '';
                    $booking['review'] = $review->host_message ?? '';
                }

                $parcelImages = [];

                foreach (['delivery_image_1', 'delivery_image_2'] as $collection) {
                    foreach ($booking->getMedia($collection) as $media) {
                        $parcelImages[] = $media->getUrl();
                    }
                }

                if (! empty($parcelImages)) {
                    $booking['delivery_images'] = $parcelImages;
                }
                $booking['firebase_json'] = json_decode($booking->firebase_json);

                return $booking;
            });

        $nextOffset = $bookings->isEmpty() ? -1 : ($offset + $bookings->count());

        return $this->addSuccessResponse(200, trans("global.vendor_{$type}_bookings_is"), [
            'Bookings' => $bookings,
            'offset' => $nextOffset,
            'limit' => $limit,
        ]);
    }

    public function confirmBookingByHost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'token' => 'required|exists:app_users,token',
            'pickup_otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }
        $user = AppUser::where('token', $request->input('token'))->first();
        if (! $user) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }

        $bookingId = $request->input('booking_id');
        $userId = $user->id;
        $booking = Booking::where('id', $bookingId)
            ->where('host_id', $userId)
            ->whereIn('status', ['Accepted', 'Arrived'])
            ->first();

        if (! $booking) {
            return $this->addErrorResponse(409, trans('global.booking_not_found_or_not_editable'), '');
        }
        $bookingExtension = BookingExtension::where('booking_id', $bookingId)->first();
        if (! $bookingExtension) {
            return $this->addErrorResponse(404, trans('global.booking_extension_not_found'), '');
        }
        if (! hash_equals(
            (string) $bookingExtension->pick_otp,
            trim((string) $request->pickup_otp)
        )) {
            return $this->addErrorResponse(400, trans('global.invalid_otp'), '');
        }
        $imageFields = [
            'delivery_image_1',
            'delivery_image_2',
        ];
        foreach ($imageFields as $field) {
            if ($request->has($field) && ! empty($request->input($field))) {
                $imageData = $request->input($field);
                $imagePath = $this->serveBase64Image($imageData);

                \Log::info("Saving Image for {$field}: ", ['imageUrl' => $imagePath]);

                if ($imagePath && file_exists($imagePath)) {
                    if ($booking->getMedia($field)->isNotEmpty()) {
                        $booking->getMedia($field)->last()->delete();
                    }

                    $booking->addMedia($imagePath)->toMediaCollection($field);
                }
            }
        }

        $booking->status = 'Confirmed';
        $booking->save();

        // $template_id = 18;
        // $valuesArray = $this->createNotificationArray($booking->userid, $booking->host_id, $booking->itemid, $booking->id);
        // $dataVal['message_key'] = $booking;
        // $this->sendAllNotifications($valuesArray, $booking->userid, $template_id, $dataVal, $booking->host_id);

        try {
            return $this->addSuccessResponse(200, trans('global.booking_confirmed_successfully'), ['booking' => $booking]);
        } catch (\Exception $e) {
            return $this->addErrorResponse(500, trans('global.ServerError_internal_server_error'), $e->getMessage());
        }
    }

    public function getCancellationPolicies(Request $request)
    {
        try {
            $module = $this->getModuleIdOrDefault($request);
            $cancellationPolicies = CancellationPolicy::where('status', 1)
                ->where('module', $module)
                ->get();

            return $this->addSuccessResponse(200, trans('global.Result_found'), ['cancellation_policies' => $cancellationPolicies]);
        } catch (\Exception $e) {
            return $this->addErrorResponse(500, trans('global.ServerError_internal_server_error'), $e->getMessage());
        }
    }

    public function distributeVendorCommission()
    {
        try {
            $affectedRows = DB::transaction(function () {
                $currentDate = Carbon::now();
                $affectedRows = 0;
                Booking::where('status', 'Completed')
                    ->where('vendor_commission_given', 0)
                    ->chunkById(100, function ($bookings) use (&$affectedRows, $currentDate) {
                        $updates = [];
                        $walletInserts = [];

                        foreach ($bookings as $booking) {
                            $description = "Driver commission for ride #{$booking->token}";
                            $walletInserts[] = [
                                'vendor_id' => $booking->host_id,
                                'amount' => $booking->vendor_commission,
                                'booking_id' => $booking->id,
                                'type' => 'credit',
                                'description' => $description,
                                'created_at' => $currentDate,
                                'updated_at' => $currentDate,
                            ];
                            $updates[] = $booking->id;
                        }

                        if (! empty($walletInserts)) {
                            VendorWallet::insert($walletInserts);
                        }

                        if (! empty($updates)) {
                            Booking::whereIn('id', $updates)->update([
                                'vendor_commission_given' => 1,
                                'updated_at' => $currentDate,
                            ]);
                        }
                        $affectedRows += count($updates);
                    });

                return $affectedRows;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Driver commissions distributed successfully.',
                'affected_rows' => $affectedRows,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Driver commission distribution failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'failure',
                'message' => 'Failed to distribute vendor commissions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function removeUnpaidBookings(Request $request)
    {
        try {
            $unpaidBookings = Booking::where('payment_status', 'notpaid')
                ->where('created_at', '<=', Carbon::now()->subHour())
                ->get();
            $count = $unpaidBookings->count();
            if ($count == 0) {
                return;
            }

            $unpaidBookingIds = $unpaidBookings->pluck('id')->toArray();
            DB::beginTransaction();
            DB::table('rental_item_dates')
                ->whereIn('booking_id', $unpaidBookingIds)
                ->update(['status' => 'Available', 'booking_id' => 0]);
            Booking::whereIn('id', $unpaidBookingIds)->delete();
            DB::commit();

            return response()->json(['message' => 'Done', 'removed_count' => $count], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Done', 'details' => $e->getMessage()], 500);
        }
    }

    public function getItemPrices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_type_id' => 'required|exists:rental_item_types,id',
            'distance' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string',
            'wallet_amount' => 'nullable|numeric|min:0',
            'selected_currency_code' => 'nullable|string',
            'token' => 'required|exists:app_users,token',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $userid = $this->checkUserByToken($request->token);
        if (! $userid) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }
        $booking = Booking::where('id', $request->booking_id)
            ->where('userid', $userid)
            ->where('status', 'Completed')
            ->first();

        if (! $booking) {
            return $this->addErrorResponse(419, trans('global.booking_not_found'), '');
        }

        $itemTypeId = $request->input('item_type_id');
        $distance = $request->input('distance');
        $couponCode = $request->input('coupon_code');
        $walletAmount = $request->input('wallet_amount', 0);
        $selectedCurrencyCode = $request->input('selected_currency_code', 'USD');
        $conversionRate = Currency::getValueByCurrencyCode($selectedCurrencyCode);

        $coupon = null;

        if ($couponCode) {
            $coupon = AddCoupon::where('coupon_code', $couponCode)
                ->where('status', 1)
                ->whereDate('coupon_expiry_date', '>=', now()->toDateString())
                ->first();

            if (! $coupon) {
                return $this->addErrorResponse(401, trans('global.invalid_coupon_code'), '');
            }

            $usedCount = Booking::where('userid', $userid)
                ->where('coupon_code', $couponCode)
                ->whereIn('status', ['Pending', 'Ongoing', 'Arrived', 'Accepted', 'Completed'])
                ->count();

            if ($coupon->max_uses_per_user > 0 && $usedCount >= $coupon->max_uses_per_user) {
                return $this->addErrorResponse(401, trans('global.coupon_already_used'), '');
            }

            $itemType = ItemType::with('cityFare')->findOrFail($itemTypeId);
            $cityFare = $itemType->cityFare;
            $recommendedFare = max(0, (float) ($cityFare?->recommended_fare ?? 0));
            $minimumFare = max(0, (float) ($cityFare?->min_fare ?? 0));

            $priceBeforeDiscount = round(max(
                $minimumFare,
                max(0, (float) $distance) * $recommendedFare
            ), 2);
            $totalPrice = $priceBeforeDiscount;

            if ($coupon->min_order_amount > 0 && $coupon->min_order_amount > $totalPrice) {
                return $this->addErrorResponse(401, trans('global.minimum_amount_required'), '');
            }

            if (! is_null($coupon->max_uses) && $coupon->used_count >= $coupon->max_uses) {
                return $this->addErrorResponse(401, trans('global.coupon_usage_limit_reached'), '');
            }
        }

        $pricingResult = $this->getItemPricesDetails($itemTypeId, $distance, $couponCode, $walletAmount, $selectedCurrencyCode, $conversionRate);
        $pricing = $pricingResult->getData(true)['data'];
        $booking->price_per_km = $pricing['price_per_km'];
        $booking->base_price = $pricing['price_before_discount'];
        $booking->total = $pricing['gross_price'];
        $booking->wall_amt = $pricing['wallet_amount'];
        $booking->coupon_code = $pricing['coupon_code'];
        $booking->coupon_discount = $pricing['coupon_discount'];
        $booking->discount_price = $pricing['coupon_discount'];
        $booking->amount_to_pay = $pricing['gross_price'];
        $booking->save();

        return $pricingResult;
    }

    public function updateBookingStatusByDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'token' => 'required|exists:app_users,token',
            'status' => 'required|string|in:Pending,Ongoing,Accepted,Arrived,pick_up,Rejected,Completed,Cancelled',
            'estimated_duration_min' => 'nullable|integer|min:1',
            'drop_otp' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $user = AppUser::where('token', $request->input('token'))->first();
        if (! $user) {
            return $this->addErrorResponse(401, trans('global.token_not_match'), '');
        }

        $booking = Booking::where('id', $request->input('booking_id'))
            ->where('host_id', $user->id)
            ->first();

        if (! $booking) {
            return $this->addErrorResponse(404, trans('global.booking_not_found'), '');
        }

        if ($request->filled('drop_otp')) {
            $bookingExtension = BookingExtension::where('booking_id', $booking->id)->first();
            if (! $bookingExtension) {
                return $this->addErrorResponse(404, trans('global.booking_extension_not_found'), '');
            }
            if ($bookingExtension->drop_otp !== $request->drop_otp) {
                return $this->addErrorResponse(400, trans('global.invalid_otp'), '');
            }
        }
        $requestedStatus = $request->input('status') === 'pick_up'
            ? 'Arrived'
            : $request->input('status');
        if (
            in_array($booking->status, ['Completed', 'Cancelled', 'Rejected'], true)
            && $booking->status !== $requestedStatus
        ) {
            return $this->addErrorResponse(409, trans('global.booking_not_found_or_not_editable'), '');
        }
        $booking->status = $requestedStatus;
        if ($request->has('firebase_json')) {
            $booking->firebase_json = json_encode($request->input('firebase_json'));
        }

        if ($request->has('total_time_taken')) {
            $extension = $booking->extension;
            if ($extension) {
                $extension->estimated_duration_min = $request->input('total_time_taken');
                $extension->save();
            }
        }
        $booking->save();

        return $this->addSuccessResponse(200, trans('global.booking_status_updated_successfully'), [
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);
    }

    public function updateBookingStatusByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'token' => 'required|exists:app_users,token',
            'status' => 'required|string|in:Cancelled',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $user = AppUser::where('token', $request->input('token'))->first();
        if (! $user) {
            return $this->addErrorResponse(401, trans('global.token_not_match'), '');
        }
        $booking = Booking::where('id', $request->input('booking_id'))
            ->where('userid', $user->id)
            ->first();
        if (! $booking) {
            return $this->addErrorResponse(404, trans('global.booking_not_found'), '');
        }
        if ($booking->status === 'Cancelled') {
            return $this->addSuccessResponse(200, trans('global.booking_status_updated_successfully'), [
                'booking_id' => $booking->id,
                'status' => $booking->status,
            ]);
        }
        if (! in_array($booking->status, ['Pending', 'Accepted', 'Arrived'], true)) {
            return $this->addErrorResponse(409, trans('global.booking_not_found_or_not_editable'), '');
        }

        $booking->status = 'Cancelled';
        $booking->save();

        return $this->addSuccessResponse(200, trans('global.booking_status_updated_successfully'), [
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]);
    }

    public function updatePaymentStatusByDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'token' => 'required|exists:app_users,token',
            'payment_method' => 'required|string|in:cash',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }
        $userid = $this->checkUserByToken($request->token);
        if (! $userid) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }
        $result = DB::transaction(function () use ($request, $userid) {
            $booking = Booking::where('id', $request->input('booking_id'))
                ->where('host_id', $userid)
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                return null;
            }

            $requestedMethod = strtolower($request->input('payment_method'));
            $currentMethod = strtolower((string) $booking->payment_method);

            // Mobile networks can retry the same Collect request after the
            // server committed it. Treat that exact retry as success, while
            // rejecting a conflicting attempt to replace a verified online
            // payment with cash.
            if (strtolower(trim((string) $booking->payment_status)) === 'paid') {
                return [
                    'booking' => $booking,
                    'idempotent' => $currentMethod === $requestedMethod,
                ];
            }

            $booking->payment_status = 'paid';
            $booking->payment_method = $requestedMethod;
            $booking->save();

            return [
                'booking' => $booking,
                'idempotent' => false,
            ];
        });

        if (! $result) {
            return $this->addErrorResponse(404, trans('global.booking_not_found'), '');
        }

        if ($result['booking']->payment_status === 'paid' && ! $result['idempotent']
            && strtolower((string) $result['booking']->payment_method) !== strtolower($request->input('payment_method'))) {
            return $this->addErrorResponse(409, trans('global.payment_status_already_paid'), '');
        }

        $booking = $result['booking'];

        return $this->addSuccessResponse(200, trans('global.payment_status_updated_successfully'), [
            'booking_id' => $booking->id,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'idempotent' => $result['idempotent'],
        ]);
    }

    public function updatePaymentStatusByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|string|in:keepz,online,card',
            'token' => 'required|exists:app_users,token',
        ]);

        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $userid = $this->checkUserByToken($request->token);
        if (! $userid) {
            return $this->addErrorResponse(419, trans('global.token_not_match'), '');
        }
        $onlinePaymentsEnabled = strtolower((string) GeneralSetting::getMetaValue('onlinepayment')) === 'active';
        $keepzEnabled = strtolower((string) GeneralSetting::getMetaValue('keepz_status')) === 'active';
        if (! $onlinePaymentsEnabled || ! $keepzEnabled) {
            return $this->addErrorResponse(422, 'Keepz card payments are not active.', '');
        }

        $booking = DB::transaction(function () use ($request, $userid) {
            $lockedBooking = Booking::where('id', $request->input('booking_id'))
                ->where('userid', $userid)
                ->lockForUpdate()
                ->first();

            if (! $lockedBooking) {
                return null;
            }

            $paymentStatus = strtolower(trim((string) $lockedBooking->payment_status));
            $paymentMethod = strtolower(trim((string) $lockedBooking->payment_method));

            if ($paymentStatus === 'paid') {
                return 'paid';
            }

            if ($paymentStatus === 'pending' && $paymentMethod === 'keepz') {
                return $lockedBooking;
            }

            if ($paymentStatus !== 'notpaid') {
                return 'invalid';
            }

            $lockedBooking->payment_status = 'pending';
            $lockedBooking->payment_method = 'keepz';
            $lockedBooking->save();

            return $lockedBooking;
        });

        if ($booking === null) {
            return $this->addErrorResponse(404, trans('global.booking_not_found'), '');
        }
        if ($booking === 'paid') {
            return $this->addErrorResponse(409, trans('global.payment_already_processed'), '');
        }
        if ($booking === 'invalid') {
            return $this->addErrorResponse(409, trans('global.booking_payment_status_invalid'), '');
        }

        return $this->addSuccessResponse(200, trans('global.payment_status_updated_successfully'), [
            'booking_id' => $booking->id,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
        ]);
    }
}
