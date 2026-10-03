<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BackendController;
use App\Models\Restaurant;
use App\Models\RestaurantPaymentSetting;
use App\Models\RestaurantApplication;
use App\Models\Plan;
use App\Enums\BillingType;
use App\Enums\Status;
use Illuminate\Http\Request;

class RestaurantPaymentSettingController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Payment Settings';
    }

    /**
     * List all restaurants with their payment settings summary.
     * Accessible to super-admin only.
     */
    public function index()
    {
        $restaurants = Restaurant::with('user')
            ->where('status', Status::ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                $r->paymentSetting = RestaurantPaymentSetting::forRestaurant($r->id);
                return $r;
            });

        $this->data['restaurants'] = $restaurants;
        return view('admin.restaurant-payment.index', $this->data);
    }

    /**
     * Show edit form for a specific restaurant's payment settings.
     */
    public function edit(Restaurant $restaurant)
    {
        $setting = RestaurantPaymentSetting::forRestaurant($restaurant->id);
        $plans   = Plan::active()->where('billing_type', BillingType::COMMISION)->get();

        $this->data['restaurant'] = $restaurant;
        $this->data['setting']    = $setting;
        $this->data['plans']      = $plans;

        return view('admin.restaurant-payment.edit', $this->data);
    }

    /**
     * Save payment settings for a restaurant.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'upi_id'                  => 'nullable|string|max:100',
            'advance_booking_percent' => 'required|integer|min:0|max:100',
            'per_head_estimate'       => 'nullable|numeric|min:0',
            'accept_upi'              => 'nullable|boolean',
            'accept_phonepe_qr'       => 'nullable|boolean',
            'accept_paytm_qr'         => 'nullable|boolean',
            'plan_id'                 => 'nullable|exists:plans,id',
            'commission_rate'         => 'nullable|numeric|min:0|max:100',
            'phonepe_qr_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'paytm_qr_image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $setting = RestaurantPaymentSetting::forRestaurant($restaurant->id);

        $setting->upi_id                  = $request->upi_id;
        $setting->advance_booking_percent = (int) $request->advance_booking_percent;
        $setting->per_head_estimate       = (float) ($request->per_head_estimate ?? 0);
        $setting->accept_upi              = $request->boolean('accept_upi');
        $setting->accept_phonepe_qr       = $request->boolean('accept_phonepe_qr');
        $setting->accept_paytm_qr         = $request->boolean('accept_paytm_qr');
        $setting->plan_id                 = $request->plan_id ?: null;
        $setting->commission_rate         = (float) ($request->commission_rate ?? 0);
        $setting->save();

        // Handle PhonePe QR upload
        if ($request->hasFile('phonepe_qr_image') && $request->file('phonepe_qr_image')->isValid()) {
            $setting->clearMediaCollection('phonepe_qr');
            $setting->addMediaFromRequest('phonepe_qr_image')
                     ->toMediaCollection('phonepe_qr');
        }

        // Handle Paytm QR upload
        if ($request->hasFile('paytm_qr_image') && $request->file('paytm_qr_image')->isValid()) {
            $setting->clearMediaCollection('paytm_qr');
            $setting->addMediaFromRequest('paytm_qr_image')
                     ->toMediaCollection('paytm_qr');
        }

        return redirect(route('admin.restaurant-payment.index'))
            ->withSuccess("Payment settings updated for {$restaurant->name}.");
    }

    /**
     * Restaurant owners can manage their own payment settings.
     */
    public function ownerEdit()
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) {
            return redirect(route('admin.dashboard.index'))
                ->withError('No restaurant found for your account.');
        }
        return $this->edit($restaurant);
    }

    public function ownerUpdate(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) {
            return redirect(route('admin.dashboard.index'))
                ->withError('No restaurant found for your account.');
        }
        return $this->update($request, $restaurant);
    }
}
