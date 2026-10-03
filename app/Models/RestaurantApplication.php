<?php

namespace App\Models;

class RestaurantApplication extends BaseModel
{
    protected $table    = 'restaurant_applications';
    protected $fillable = [
        'user_id', 'business_name', 'business_type', 'cuisine_type',
        'business_address', 'city', 'state', 'country', 'zip_code', 'website',
        'business_registration_number', 'tax_id', 'food_license_number',
        'owner_name', 'owner_phone', 'owner_email', 'business_phone',
        'plan_id', 'billing_type',
        'payment_method', 'payment_transaction_id', 'amount_paid', 'payment_status',
        'status', 'rejection_reason', 'reviewed_by', 'reviewed_at', 'notes',
    ];
    protected $casts = [
        'status'       => 'int',
        'billing_type' => 'int',
        'amount_paid'  => 'float',
        'reviewed_at'  => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING  = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default               => 'Pending',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => '<span class="db-table-badge text-green-600 bg-green-100">Approved</span>',
            self::STATUS_REJECTED => '<span class="db-table-badge text-red-600 bg-red-100">Rejected</span>',
            default               => '<span class="db-table-badge text-yellow-600 bg-yellow-100">Pending</span>',
        };
    }
}
