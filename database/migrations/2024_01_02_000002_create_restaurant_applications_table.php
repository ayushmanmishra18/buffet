<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // ── Business identity ──────────────────────────────────────────
            $table->string('business_name', 200);
            $table->string('business_type', 100)->nullable(); // Hotel, Restaurant, Cafe, etc.
            $table->string('cuisine_type', 200)->nullable();  // comma-separated or free text
            $table->longText('business_address');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('website', 255)->nullable();

            // ── Legal / documents ──────────────────────────────────────────
            $table->string('business_registration_number', 100)->nullable();
            $table->string('tax_id', 100)->nullable();           // GST / VAT / EIN
            $table->string('food_license_number', 100)->nullable();

            // ── Contact ────────────────────────────────────────────────────
            $table->string('owner_name', 200);
            $table->string('owner_phone', 30);
            $table->string('owner_email', 150);
            $table->string('business_phone', 30)->nullable();

            // ── Plan ────────────────────────────────────────────────────────
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedTinyInteger('billing_type')->nullable(); // 5=subscription, 10=commission

            // ── Payment ─────────────────────────────────────────────────────
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_transaction_id', 200)->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_status', 30)->default('pending'); // pending, paid, waived

            // ── Verification ────────────────────────────────────────────────
            // status: pending=0, approved=1, rejected=2
            $table->unsignedTinyInteger('status')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // ── Additional notes ────────────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_applications');
    }
};
