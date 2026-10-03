<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BillingType;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;

class PlanController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Plans';
    }

    public function index()
    {
        $this->data['plans'] = Plan::orderBy('sort')->orderBy('id')->get();
        return view('admin.plan.index', $this->data);
    }

    public function create()
    {
        $this->data['billingTypes'] = [
            BillingType::SUBCRIPTION => 'Subscription (Monthly Fee)',
            BillingType::COMMISION   => 'Commission (% per Order)',
        ];
        return view('admin.plan.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'billing_type'    => 'required|in:5,10',
            'price'           => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'trial_days'      => 'nullable|integer|min:0',
            'description'     => 'nullable|string|max:500',
            'features'        => 'nullable|string',
            'status'          => 'required|boolean',
        ]);

        $plan                  = new Plan();
        $plan->name            = $request->name;
        $plan->description     = $request->description;
        $plan->billing_type    = $request->billing_type;
        $plan->price           = $request->billing_type == BillingType::SUBCRIPTION ? ($request->price ?? 0) : 0;
        $plan->commission_rate = $request->billing_type == BillingType::COMMISION ? ($request->commission_rate ?? 0) : 0;
        $plan->trial_days      = $request->trial_days ?? 0;
        $plan->is_featured     = $request->boolean('is_featured');
        $plan->status          = $request->boolean('status');
        $plan->features        = $this->parseFeatures($request->features);
        $plan->save();
        $plan->sort = $plan->id;
        $plan->save();

        return redirect(route('admin.plan.index'))->withSuccess('Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $this->data['plan'] = $plan;
        $this->data['billingTypes'] = [
            BillingType::SUBCRIPTION => 'Subscription (Monthly Fee)',
            BillingType::COMMISION   => 'Commission (% per Order)',
        ];
        return view('admin.plan.edit', $this->data);
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'billing_type'    => 'required|in:5,10',
            'price'           => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'trial_days'      => 'nullable|integer|min:0',
            'description'     => 'nullable|string|max:500',
            'features'        => 'nullable|string',
            'status'          => 'required|boolean',
        ]);

        $plan->name            = $request->name;
        $plan->description     = $request->description;
        $plan->billing_type    = $request->billing_type;
        $plan->price           = $request->billing_type == BillingType::SUBCRIPTION ? ($request->price ?? 0) : 0;
        $plan->commission_rate = $request->billing_type == BillingType::COMMISION ? ($request->commission_rate ?? 0) : 0;
        $plan->trial_days      = $request->trial_days ?? 0;
        $plan->is_featured     = $request->boolean('is_featured');
        $plan->status          = $request->boolean('status');
        $plan->features        = $this->parseFeatures($request->features);
        $plan->save();

        return redirect(route('admin.plan.index'))->withSuccess('Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect(route('admin.plan.index'))->withSuccess('Plan deleted successfully.');
    }

    /** Parse a textarea of one feature per line into a JSON array. */
    private function parseFeatures(?string $raw): ?array
    {
        if (blank($raw)) return null;
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }
}
