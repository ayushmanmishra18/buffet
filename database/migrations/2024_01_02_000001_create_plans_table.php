<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('billing_type');   // 5=subscription, 10=commission
            $table->decimal('price', 10, 2)->default(0);   // monthly fee (subscription)
            $table->decimal('commission_rate', 5, 2)->default(0); // % of order (commission)
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->json('features')->nullable();           // array of bullet points
            $table->boolean('is_featured')->default(false);
            $table->boolean('status')->default(true);      // true=active, false=inactive
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
