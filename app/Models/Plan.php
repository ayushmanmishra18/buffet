<?php

namespace App\Models;

use App\Enums\BillingType;

class Plan extends BaseModel
{
    protected $table    = 'plans';
    protected $fillable = [
        'name', 'description', 'billing_type', 'price',
        'commission_rate', 'trial_days', 'features',
        'is_featured', 'status', 'sort',
    ];
    protected $casts = [
        'billing_type'    => 'int',
        'price'           => 'float',
        'commission_rate' => 'float',
        'trial_days'      => 'int',
        'features'        => 'array',
        'is_featured'     => 'boolean',
        'status'          => 'boolean',
        'sort'            => 'int',
    ];

    // Human-readable billing type label
    public function getBillingLabelAttribute(): string
    {
        return $this->billing_type === BillingType::SUBCRIPTION
            ? 'Subscription'
            : 'Commission';
    }

    // Formatted price string
    public function getPriceDisplayAttribute(): string
    {
        if ($this->billing_type === BillingType::SUBCRIPTION) {
            return currencyFormat($this->price) . ' / month';
        }
        return $this->commission_rate . '% per order';
    }

    // Scope: only active plans
    public function scopeActive($query)
    {
        return $query->where('status', true)->orderBy('sort');
    }

    public function applications()
    {
        return $this->hasMany(RestaurantApplication::class);
    }
}
