<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeliveryBoyAccount;
use App\Models\Plan;
use App\Models\RestaurantApplication;
use App\Models\Gateway;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show registration form — passes active plans and gateways.
     */
    public function showRegistrationForm()
    {
        $plans    = Plan::active()->get();
        $gateways = Gateway::where('status', 1)->get();
        return view('auth.register', compact('plans', 'gateways'));
    }

    /**
     * Validation rules — split by role.
     */
    protected function validator(array $data)
    {
        $isRestaurant = isset($data['roles']) && (int)$data['roles'] === 3;

        $rules = [
            'roles'           => ['required', 'numeric', 'in:2,3'],
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['required', 'numeric'],
            'address'         => ['required', 'string', 'max:255'],
            'register_email'  => ['required', 'string', 'email', Rule::unique('users', 'email'), 'max:100'],
            'username'        => request('username')
                                    ? ['required', 'string', Rule::unique('users', 'username'), 'max:60']
                                    : ['nullable'],
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
            'countrycode'     => ['required', 'numeric'],
            'countrycodename' => ['required', 'string', 'max:255'],
        ];

        if ($isRestaurant) {
            $rules = array_merge($rules, [
                'business_name'    => ['required', 'string', 'max:200'],
                'business_type'    => ['required', 'string', 'max:100'],
                'business_address' => ['required', 'string', 'max:500'],
                'city'             => ['required', 'string', 'max:100'],
                'country'          => ['required', 'string', 'max:100'],
                'owner_name'       => ['required', 'string', 'max:200'],
                'owner_email'      => ['required', 'email', 'max:150'],
                'owner_phone'      => ['required', 'string', 'max:30'],
                'plan_id'          => ['required', 'exists:plans,id'],
                'billing_type'     => ['required', 'numeric', 'in:5,10'],
            ]);
        }

        return Validator::make($data, $rules, [
            'register_email.required' => 'The email field is required.',
            'register_email.unique'   => 'This email is already registered.',
            'plan_id.required'        => 'Please select a plan to continue.',
        ]);
    }

    /**
     * Create the user. Restaurant owners are created in INACTIVE state
     * and a RestaurantApplication record is saved for admin review.
     */
    protected function create(array $data)
    {
        $isRestaurant = (int)$data['roles'] === 3;

        $user = User::create([
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'username'          => $data['username'] ?? $this->makeUsername($data['register_email']),
            'phone'             => $data['phone'],
            'address'           => $data['address'],
            'email'             => $data['register_email'],
            'password'          => Hash::make($data['password']),
            'country_code'      => $data['countrycode'],
            'country_code_name' => $data['countrycodename'],
            // Restaurant owners start inactive (10) until admin approves
            'status'            => $isRestaurant ? \App\Enums\Status::INACTIVE : \App\Enums\Status::ACTIVE,
        ]);

        $role = Role::find($data['roles']);
        if (!blank($user) && !blank($role)) {
            $user->assignRole($role->name);
        }

        if ($isRestaurant) {
            // Save the full application for admin review
            RestaurantApplication::create([
                'user_id'                       => $user->id,
                'business_name'                 => $data['business_name'],
                'business_type'                 => $data['business_type'],
                'cuisine_type'                  => $data['cuisine_type'] ?? null,
                'business_address'              => $data['business_address'],
                'city'                          => $data['city'],
                'state'                         => $data['state'] ?? null,
                'country'                       => $data['country'],
                'zip_code'                      => $data['zip_code'] ?? null,
                'website'                       => $data['website'] ?? null,
                'business_registration_number'  => $data['business_registration_number'] ?? null,
                'tax_id'                        => $data['tax_id'] ?? null,
                'food_license_number'           => $data['food_license_number'] ?? null,
                'owner_name'                    => $data['owner_name'],
                'owner_phone'                   => $data['owner_phone'],
                'owner_email'                   => $data['owner_email'],
                'business_phone'                => $data['business_phone'] ?? null,
                'plan_id'                       => $data['plan_id'],
                'billing_type'                  => $data['billing_type'],
                'payment_method'                => $data['payment_method'] ?? null,
                'payment_transaction_id'        => $data['payment_transaction_id'] ?? null,
                'amount_paid'                   => $data['amount_paid'] ?? 0,
                'payment_status'                => $data['payment_status'] ?? 'pending',
                'notes'                         => $data['notes'] ?? null,
                'status'                        => RestaurantApplication::STATUS_PENDING,
            ]);
        }

        return $user;
    }

    /**
     * Override the registered() callback so restaurant owners are NOT
     * automatically logged in — they must wait for admin approval.
     */
    protected function registered(Request $request, $user)
    {
        $isRestaurant = $user->hasRole('Restaurant Owner');

        if ($isRestaurant) {
            // Log them out and show a pending-approval message
            auth()->logout();
            return redirect(route('login'))
                ->with('success', 'Your application has been submitted successfully! Our team will review it and notify you within 1–2 business days. You will be able to log in once approved.');
        }

        // Customers: proceed normally to home
        return redirect($this->redirectPath());
    }

    // ── API endpoint: return plan details for JS ──────────────────────────────
    public function planDetails(Request $request)
    {
        $plan = Plan::active()->find($request->plan_id);
        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }
        return response()->json([
            'id'              => $plan->id,
            'name'            => $plan->name,
            'billing_type'    => $plan->billing_type,
            'price'           => $plan->price,
            'commission_rate' => $plan->commission_rate,
            'trial_days'      => $plan->trial_days,
            'features'        => $plan->features ?? [],
            'price_display'   => $plan->price_display,
        ]);
    }

    private function makeUsername(string $email): string
    {
        $parts = explode('@', $email);
        return $parts[0] . mt_rand(100, 999);
    }
}
