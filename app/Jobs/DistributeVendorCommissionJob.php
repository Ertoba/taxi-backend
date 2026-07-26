<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\VendorWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributeVendorCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        try {
            $processed = 0;

            Booking::query()
                ->where('status', 'Completed')
                ->where('vendor_commission_given', 0)
                ->orderBy('id')
                ->chunkById(100, function ($bookings) use (&$processed): void {
                    foreach ($bookings as $booking) {
                        DB::transaction(function () use ($booking, &$processed): void {
                            $lockedBooking = Booking::query()
                                ->with('host')
                                ->whereKey($booking->id)
                                ->lockForUpdate()
                                ->first();

                            if (! $lockedBooking || (int) $lockedBooking->vendor_commission_given !== 0) {
                                return;
                            }

                            if (! $lockedBooking->host) {
                                Log::warning('Skipped driver ledger update because the driver is missing.', [
                                    'booking_id' => $lockedBooking->id,
                                    'driver_id' => $lockedBooking->host_id,
                                ]);

                                return;
                            }

                            $paymentMethod = strtolower(trim((string) $lockedBooking->payment_method));
                            $adminCommission = round(max(0, (float) $lockedBooking->admin_commission), 2);

                            // Keepz Split transfers the driver's share directly to the configured
                            // receiver. It is intentionally audit-only and never withdrawable here.
                            // For cash rides, the driver already collected the fare, so only the
                            // platform commission becomes a settlement liability.
                            if ($paymentMethod === 'cash' && $adminCommission > 0) {
                                $description = "Cash ride platform commission for booking #{$lockedBooking->token}";
                                $alreadyRecorded = VendorWallet::query()
                                    ->where('vendor_id', $lockedBooking->host_id)
                                    ->where('booking_id', $lockedBooking->id)
                                    ->where('type', 'debit')
                                    ->where('description', $description)
                                    ->exists();

                                if (! $alreadyRecorded) {
                                    VendorWallet::create([
                                        'vendor_id' => $lockedBooking->host_id,
                                        'amount' => $adminCommission,
                                        'booking_id' => $lockedBooking->id,
                                        'type' => 'debit',
                                        'description' => $description,
                                    ]);
                                }
                            }

                            $lockedBooking->vendor_commission_given = 1;
                            $lockedBooking->save();
                            $processed++;
                        }, 3);
                    }
                });

            Log::info('Driver commission ledger updated.', ['processed_bookings' => $processed]);
        } catch (\Throwable $e) {
            Log::error('Driver commission ledger update failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
