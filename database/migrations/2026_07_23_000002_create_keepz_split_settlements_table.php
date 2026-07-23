<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keepz_split_settlements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('transaction_id')->unique();
            $table->unsignedBigInteger('driver_id')->index();
            $table->string('integrator_order_id', 64)->unique();
            $table->string('currency_code', 3);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('platform_amount', 14, 2);
            $table->decimal('driver_amount', 14, 2);
            $table->string('platform_receiver_type', 16);
            $table->string('platform_receiver_masked', 64);
            $table->string('driver_receiver_type', 16);
            $table->string('driver_receiver_masked', 64);
            $table->string('gateway_status', 32)->default('SUCCESS');
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            $table->index(['driver_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keepz_split_settlements');
    }
};
