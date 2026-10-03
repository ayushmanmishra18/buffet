<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Advance payment details
            $table->decimal('advance_amount', 10, 2)->default(0)
                  ->after('notes')
                  ->comment('Advance booking deposit amount collected from customer');
            $table->unsignedTinyInteger('advance_payment_status')->default(0)
                  ->after('advance_amount')
                  ->comment('0=not_required, 1=pending, 2=paid, 3=waived');
            $table->string('advance_payment_method', 30)->nullable()
                  ->after('advance_payment_status')
                  ->comment('upi, phonepe_qr, paytm_qr');
            $table->string('advance_upi_transaction_id', 200)->nullable()
                  ->after('advance_payment_method')
                  ->comment('UTR / transaction reference entered by customer');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'advance_amount',
                'advance_payment_status',
                'advance_payment_method',
                'advance_upi_transaction_id',
            ]);
        });
    }
};
