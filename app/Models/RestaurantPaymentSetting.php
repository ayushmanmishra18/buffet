<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class RestaurantPaymentSetting extends BaseModel implements HasMedia
{
    use InteractsWithMedia;

    protected $table    = 'restaurant_payment_settings';
    protected $fillable = [
        'restaurant_id',
        'upi_id',
        'advance_booking_percent',
        'per_head_estimate',
        'accept_upi',
        'accept_phonepe_qr',
        'accept_paytm_qr',
        'plan_id',
        'commission_rate',
    ];
    protected $casts = [
        'advance_booking_percent' => 'int',
        'per_head_estimate'       => 'float',
        'commission_rate'         => 'float',
        'accept_upi'              => 'boolean',
        'accept_phonepe_qr'       => 'boolean',
        'accept_paytm_qr'         => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // ── Media accessors ──────────────────────────────────────────────────

    /** URL of the PhonePe QR code image */
    public function getPhonepeQrImageAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('phonepe_qr');
        return $url ?: null;
    }

    /** URL of the Paytm QR code image */
    public function getPaytmQrImageAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('paytm_qr');
        return $url ?: null;
    }

    /**
     * Calculate the advance deposit amount for a given number of guests.
     * Returns 0 if advance is not configured.
     */
    public function calculateAdvance(int $guests): float
    {
        if ($this->advance_booking_percent <= 0) {
            return 0.0;
        }

        $estimatedBill = $this->per_head_estimate > 0
            ? $this->per_head_estimate * $guests
            : 0;

        if ($estimatedBill <= 0) {
            return 0.0;
        }

        return round(($estimatedBill * $this->advance_booking_percent) / 100, 2);
    }

    /**
     * Get or create the payment settings record for a restaurant.
     */
    public static function forRestaurant(int $restaurantId): self
    {
        return static::firstOrCreate(
            ['restaurant_id' => $restaurantId],
            [
                'advance_booking_percent' => 0,
                'per_head_estimate'       => 0,
                'accept_upi'              => true,
                'accept_phonepe_qr'       => false,
                'accept_paytm_qr'         => false,
                'commission_rate'         => 0,
            ]
        );
    }
}
