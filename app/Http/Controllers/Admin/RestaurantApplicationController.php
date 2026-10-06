<?php

namespace App\Http\Controllers\Admin;

use App\Models\RestaurantApplication;
use App\Models\User;
use App\Models\Restaurant;
use App\Enums\Status;
use App\Enums\DeliveryStatus;
use App\Enums\PickupStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;
use Spatie\Permission\Models\Role;

class RestaurantApplicationController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Restaurant Applications';
    }

    /**
     * List all pending applications (and optionally filter by status).
     */
    public function index(Request $request)
    {
        $status = $request->get('status', ''); // '', 0, 1, 2

        $query = RestaurantApplication::with('user', 'plan')->orderByDesc('id');

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $this->data['applications'] = $query->paginate(20)->withQueryString();
        $this->data['filterStatus'] = $status;

        $this->data['counts'] = [
            'pending'  => RestaurantApplication::where('status', RestaurantApplication::STATUS_PENDING)->count(),
            'approved' => RestaurantApplication::where('status', RestaurantApplication::STATUS_APPROVED)->count(),
            'rejected' => RestaurantApplication::where('status', RestaurantApplication::STATUS_REJECTED)->count(),
        ];

        return view('admin.restaurant-application.index', $this->data);
    }

    /**
     * View full details of a single application.
     */
    public function show(RestaurantApplication $restaurantApplication)
    {
        $this->data['application'] = $restaurantApplication->load('user', 'plan', 'reviewer');
        return view('admin.restaurant-application.show', $this->data);
    }

    /**
     * Approve an application — activate user + create restaurant record.
     */
    public function approve(Request $request, RestaurantApplication $restaurantApplication)
    {
        $application = $restaurantApplication;

        // Activate the user account — status 5 = Status::ACTIVE in this app
        $user = $application->user;
        $user->status = \App\Enums\Status::ACTIVE; // 5
        $user->save();

        // Create the Restaurant record from the application data
        $restaurant = new Restaurant();
        $restaurant->user_id        = $user->id;
        $restaurant->name           = $application->business_name;
        $restaurant->description    = $application->business_type . ($application->cuisine_type ? ' — ' . $application->cuisine_type : '');
        $restaurant->address        = $application->business_address . ', ' . $application->city . ', ' . $application->country;
        $restaurant->status         = Status::ACTIVE;
        $restaurant->current_status = Status::ACTIVE;
        $restaurant->delivery_status = DeliveryStatus::ENABLE;
        $restaurant->pickup_status  = PickupStatus::ENABLE;
        $restaurant->table_status   = 10; // disabled by default
        $restaurant->applied        = 1;
        $restaurant->save();

        // Carry the chosen plan into the restaurant's payment settings
        // (commission plans drive per-order deduction; subscription plans are display-only)
        $paymentSetting = \App\Models\RestaurantPaymentSetting::forRestaurant($restaurant->id);
        $plan = $application->plan;
        if ($plan && $plan->billing_type == \App\Enums\BillingType::COMMISION) {
            $paymentSetting->plan_id         = $plan->id;
            $paymentSetting->commission_rate = (float) $plan->commission_rate;
            $paymentSetting->save();
        }

        // Mark application as approved
        $application->status      = RestaurantApplication::STATUS_APPROVED;
        $application->reviewed_by = auth()->id();
        $application->reviewed_at = now();
        $application->save();

        return redirect(route('admin.restaurant-application.index'))
            ->withSuccess("Application approved. {$user->first_name}'s restaurant has been created and their account activated.");
    }

    /**
     * Reject an application with a reason.
     */
    public function reject(Request $request, RestaurantApplication $restaurantApplication)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $application = $restaurantApplication;

        // Keep user inactive
        $application->status           = RestaurantApplication::STATUS_REJECTED;
        $application->rejection_reason = $request->rejection_reason;
        $application->reviewed_by      = auth()->id();
        $application->reviewed_at      = now();
        $application->save();

        return redirect(route('admin.restaurant-application.index'))
            ->withSuccess("Application rejected. The applicant has been notified.");
    }
}
