<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->where('status', 'Completed')
            ->whereRaw('LOWER(payment_method) = ?', ['cash'])
            ->where('vendor_commission_given', 1)
            ->where('admin_commission', '>', 0)
            ->orderBy('id')
            ->chunkById(100, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $description = "Cash ride platform commission for booking #{$booking->token}";
                    $alreadyRecorded = DB::table('vendor_wallets')
                        ->where('vendor_id', $booking->host_id)
                        ->where('booking_id', $booking->id)
                        ->where('type', 'debit')
                        ->where('description', $description)
                        ->exists();

                    if ($alreadyRecorded || ! DB::table('app_users')->where('id', $booking->host_id)->exists()) {
                        continue;
                    }

                    DB::table('vendor_wallets')->insert([
                        'vendor_id' => $booking->host_id,
                        'amount' => round((float) $booking->admin_commission, 2),
                        'booking_id' => $booking->id,
                        'payout_id' => 0,
                        'type' => 'debit',
                        'token' => $this->uniqueWalletToken(),
                        'description' => $description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Historical reconciliation is an accounting audit record and must not
        // be deleted automatically during a rollback.
    }

    private function uniqueWalletToken(): string
    {
        do {
            $token = Str::upper(Str::random(10));
        } while (DB::table('vendor_wallets')->where('token', $token)->exists());

        return $token;
    }
};
