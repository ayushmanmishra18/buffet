<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id')->unique();

            // UPI settings
            $table->string('upi_id', 100)->nullable()
                  ->comment('e.g. restaurantname@okicici');

            // PhonePe QR — admin uploads the QR image via media
            // Paytm QR   — admin uploads the QR image via media
            // Both stored via Spatie MediaLibrary (collections: phonepe_qr, paytm_qr)

            // Advance booking deposit
            $table->unsignedTinyInteger('advance_booking_percent')->default(0)
                  ->comment('% of estimated bill charged as advance when booking a table. 0 = no advance required.');

            // Optional: estimated per-head amount for advance calculation
            $table->decimal('per_head_estimate', 8, 2)->default(0)
                  ->comment('Estimated spend per guest. Used to compute advance amount.');

            // Which India payment modes the restaurant accepts for advance
            $table->boolean('accept_upi')->default(true);
            $table->boolean('accept_phonepe_qr')->default(false);
            $table->boolean('accept_paytm_qr')->default(false);

            // Commission plan link (from restaurant_applications)
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0)
                  ->comment('Overrides global commission if restaurant is on a commission plan');

            $table->timestamps();

            $table->foreign('restaurant_id')
                  ->references('id')->on('restaurants')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_payment_settings');
    }
};
