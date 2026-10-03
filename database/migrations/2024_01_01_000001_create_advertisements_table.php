<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('position', 30)->default('middle'); // top, after_hero, middle, bottom
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(5); // 5=active, 10=inactive
            $table->auditColumn();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
