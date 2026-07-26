<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('app_users')->cascadeOnDelete();
            $table->uuid('integrator_order_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->char('currency_code', 3)->default('GEL');
            $table->string('status', 32)->default('pending')->index();
            $table->text('checkout_url')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_settlements');
    }
};
