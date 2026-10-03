@extends('frontend.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('frontend/lib/inttelinput/css/intlTelInput.css') }}">
    <style>
        /* ── Registration page overrides ─────────────────────────────── */
        .reg-page { min-height: 100vh; display: flex; align-items: stretch; }

        /* Left panel */
        .reg-left {
            flex: 0 0 58%;
            padding: 48px 56px;
            overflow-y: auto;
            background: #fff;
        }
        @media (max-width: 991px) { .reg-left { flex: 0 0 100%; padding: 32px 20px; } }

        /* Right panel — hero image */
        .reg-right {
            flex: 0 0 42%;
            background: url('{{ asset("frontend/images/auth.jpg") }}') center/cover no-repeat;
            position: relative;
        }
        .reg-right::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(238,29,72,.75) 0%, rgba(30,30,60,.85) 100%);
        }
        .reg-right-inner {
            position: relative; z-index: 1;
            height: 100%; display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 40px 32px; text-align: center; color: #fff;
        }
        .reg-right-inner h2 { font-size: 28px; font-weight: 800; margin-bottom: 12px; }
        .reg-right-inner p  { font-size: 15px; opacity: .85; line-height: 1.7; }
        @media (max-width: 991px) { .reg-right { display: none; } }

        /* Tab switcher */
        .reg-tabs { display: flex; gap: 4px; background: #f5f6fa; border-radius: 12px; padding: 4px; margin-bottom: 32px; }
        .reg-tab-btn {
            flex: 1; border: none; background: transparent;
            padding: 10px 16px; border-radius: 9px; font-size: 14px;
            font-weight: 600; cursor: pointer; color: #888;
            transition: all .25s ease;
        }
        .reg-tab-btn.active { background: #fff; color: var(--bs-primary, #EE1D48); box-shadow: 0 2px 8px rgba(0,0,0,.1); }

        /* Step wizard */
        .step-wizard { display: flex; align-items: center; margin-bottom: 32px; }
        .step-item { display: flex; align-items: center; flex: 1; }
        .step-item:last-child { flex: none; }
        .step-circle {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            background: #f0f0f0; color: #aaa;
            border: 2px solid #e5e5e5;
            transition: all .3s ease; flex-shrink: 0;
        }
        .step-circle.active  { background: #EE1D48; color: #fff; border-color: #EE1D48; }
        .step-circle.done    { background: #22c55e; color: #fff; border-color: #22c55e; }
        .step-line { flex: 1; height: 2px; background: #e5e5e5; margin: 0 6px; }
        .step-line.done      { background: #22c55e; }
        .step-label { font-size: 10px; font-weight: 600; color: #aaa; text-align: center; margin-top: 4px; white-space: nowrap; }
        .step-label.active   { color: #EE1D48; }
        .step-label.done     { color: #22c55e; }
        .step-wrapper { display: flex; flex-direction: column; align-items: center; }

        /* Form fields */
        .reg-field { margin-bottom: 18px; }
        .reg-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
        .reg-label .req { color: #EE1D48; margin-left: 2px; }
        .reg-input {
            width: 100%; height: 46px; padding: 0 14px;
            border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-size: 14px; color: #111; background: #fafafa;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .reg-input:focus { border-color: #EE1D48; box-shadow: 0 0 0 3px rgba(238,29,72,.08); background: #fff; }
        .reg-input.is-invalid { border-color: #ef4444; }
        textarea.reg-input { height: auto; padding: 12px 14px; resize: vertical; }
        select.reg-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        .reg-invalid { font-size: 12px; color: #ef4444; margin-top: 4px; display: block; }

        /* Plan cards */
        .plan-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .plan-card {
            border: 2px solid #e5e7eb; border-radius: 14px; padding: 20px;
            cursor: pointer; transition: all .25s ease; position: relative;
            background: #fff;
        }
        .plan-card:hover { border-color: #EE1D48; box-shadow: 0 4px 16px rgba(238,29,72,.1); }
        .plan-card.selected { border-color: #EE1D48; background: #fff9fa; box-shadow: 0 4px 20px rgba(238,29,72,.15); }
        .plan-card.featured::before {
            content: 'Popular';
            position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: #EE1D48; color: #fff; font-size: 10px; font-weight: 700;
            padding: 2px 12px; border-radius: 20px;
        }
        .plan-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; display: inline-block; margin-bottom: 8px; }
        .plan-badge.sub { background: #eff6ff; color: #3b82f6; }
        .plan-badge.comm { background: #fdf4ff; color: #a855f7; }
        .plan-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 4px; }
        .plan-price { font-size: 22px; font-weight: 800; color: #EE1D48; margin-bottom: 12px; }
        .plan-price small { font-size: 13px; font-weight: 500; color: #888; }
        .plan-features { list-style: none; padding: 0; margin: 0; }
        .plan-features li { font-size: 13px; color: #555; padding: 3px 0; }
        .plan-features li::before { content: '✓ '; color: #22c55e; font-weight: 700; }
        .plan-check { position: absolute; top: 12px; right: 12px; width: 22px; height: 22px; border-radius: 50%; background: #EE1D48; color: #fff; display: none; align-items: center; justify-content: center; font-size: 11px; }
        .plan-card.selected .plan-check { display: flex; }

        /* Payment section */
        .payment-method-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .payment-method-card {
            border: 2px solid #e5e7eb; border-radius: 10px; padding: 14px 12px;
            text-align: center; cursor: pointer; transition: all .2s;
            font-size: 13px; font-weight: 600; color: #555;
        }
        .payment-method-card:hover { border-color: #EE1D48; }
        .payment-method-card.selected { border-color: #EE1D48; background: #fff9fa; color: #EE1D48; }
        .payment-method-card i { display: block; font-size: 22px; margin-bottom: 6px; }

        /* Buttons */
        .reg-btn-primary {
            width: 100%; height: 50px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #EE1D48 0%, #c81638 100%);
            color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
            transition: all .25s ease; box-shadow: 0 4px 15px rgba(238,29,72,.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .reg-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(238,29,72,.4); }
        .reg-btn-secondary {
            height: 50px; padding: 0 24px; border: 2px solid #e5e7eb;
            border-radius: 12px; background: #fff; color: #555;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all .2s ease;
        }
        .reg-btn-secondary:hover { border-color: #EE1D48; color: #EE1D48; }

        /* Section title */
        .reg-section-title { font-size: 13px; font-weight: 700; color: #EE1D48; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f0; }

        /* Summary box */
        .reg-summary {
            background: linear-gradient(135deg, #fff9fa 0%, #fef2f2 100%);
            border: 1px solid #fecdd3; border-radius: 12px; padding: 20px;
            margin-bottom: 24px;
        }
        .reg-summary-title { font-size: 14px; font-weight: 700; color: #EE1D48; margin-bottom: 12px; }
        .reg-summary-row { display: flex; justify-content: space-between; font-size: 13px; color: #555; padding: 4px 0; }
        .reg-summary-row strong { color: #111; }

        /* Pending state notice */
        .pending-notice {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
            padding: 16px; margin-bottom: 20px; font-size: 13px; color: #92400e;
        }

        /* Phone input fix */
        .iti { width: 100%; }
        .iti .reg-input { padding-left: 52px; }

        /* Step panes */
        .step-pane { display: none; }
        .step-pane.active { display: block; }
    </style>
@endpush

@section('main-content')
<div class="reg-page">

    {{-- ── LEFT: Form panel ─────────────────────────────────────────────── --}}
    <div class="reg-left">

        {{-- Logo + heading --}}
        <div style="margin-bottom: 28px;">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/' . setting('site_logo')) }}" alt="logo" style="height:38px; margin-bottom: 20px;">
            </a>
            <h1 style="font-size:24px; font-weight:800; color:#111; margin-bottom:4px;">Create your account</h1>
            <p style="font-size:14px; color:#888;">Already have an account? <a href="{{ route('login') }}" style="color:#EE1D48; font-weight:600;">Sign in</a></p>
        </div>

        {{-- ── Role switcher tabs ────────────────────────────────────────── --}}
        <div class="reg-tabs" id="regTabs">
            <button type="button" class="reg-tab-btn active" data-role="customer" onclick="switchRole('customer')">
                <i class="fas fa-user" style="margin-right:6px;"></i> Customer
            </button>
            <button type="button" class="reg-tab-btn" data-role="owner" onclick="switchRole('owner')">
                <i class="fas fa-store" style="margin-right:6px;"></i> Hotel / Restaurant Owner
            </button>
        </div>

        {{-- ============================================================== --}}
        {{-- CUSTOMER REGISTRATION                                           --}}
        {{-- ============================================================== --}}
        <div id="customerForm">
            <form method="POST" action="{{ route('register') }}" id="customerRegForm">
                @csrf
                <input type="hidden" name="roles" value="2">

                @if ($errors->any() && old('roles') == 2)
                    <div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:10px; padding:14px; margin-bottom:20px; font-size:13px; color:#b91c1c;">
                        <strong>Please fix the following:</strong>
                        <ul style="margin:6px 0 0 16px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">First Name <span class="req">*</span></label>
                            <input name="first_name" value="{{ old('first_name') }}" type="text" class="reg-input @error('first_name') is-invalid @enderror" placeholder="John">
                            @error('first_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">Last Name <span class="req">*</span></label>
                            <input name="last_name" value="{{ old('last_name') }}" type="text" class="reg-input @error('last_name') is-invalid @enderror" placeholder="Doe">
                            @error('last_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">Username <span class="req">*</span></label>
                            <input name="username" value="{{ old('username') }}" type="text" class="reg-input @error('username') is-invalid @enderror" placeholder="johndoe">
                            @error('username')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">Email Address <span class="req">*</span></label>
                            <input name="register_email" value="{{ old('register_email') }}" type="email" class="reg-input @error('register_email') is-invalid @enderror" placeholder="john@example.com">
                            @error('register_email')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="reg-field">
                            <label class="reg-label">Phone Number <span class="req">*</span></label>
                            <input class="reg-input phone" type="tel" id="customer_phone" name="phone" onkeypress="validate(event)">
                            <input type="hidden" id="code" name="countrycode" value="1">
                            <input type="hidden" id="code_name" name="countrycodename" value="us">
                            @error('phone')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="reg-field">
                            <label class="reg-label">Home Address <span class="req">*</span></label>
                            <input name="address" value="{{ old('address') }}" type="text" class="reg-input @error('address') is-invalid @enderror" placeholder="123 Main Street, City, Country">
                            @error('address')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">Password <span class="req">*</span></label>
                            <input name="password" id="cust_password" type="password" class="reg-input @error('password') is-invalid @enderror" placeholder="Min. 6 characters">
                            @error('password')<span class="reg-invalid">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="reg-field">
                            <label class="reg-label">Confirm Password <span class="req">*</span></label>
                            <input name="password_confirmation" type="password" class="reg-input" placeholder="Repeat password">
                        </div>
                    </div>
                    <div class="col-12" style="margin-top:8px;">
                        <button type="submit" class="reg-btn-primary">
                            <i class="fas fa-user-plus"></i> Create Customer Account
                        </button>
                        <p style="text-align:center; font-size:12px; color:#aaa; margin-top:12px;">
                            By registering, you agree to our <a href="#" style="color:#EE1D48;">Terms of Service</a> and <a href="#" style="color:#EE1D48;">Privacy Policy</a>.
                        </p>
                    </div>
                </div>
            </form>
        </div>{{-- /customerForm --}}


        {{-- ============================================================== --}}
        {{-- HOTEL / RESTAURANT OWNER — MULTI-STEP WIZARD                   --}}
        {{-- ============================================================== --}}
        <div id="ownerForm" style="display:none;">

            {{-- Step wizard indicator --}}
            <div style="margin-bottom: 28px;">
                <div class="step-wizard" id="stepWizard">
                    <div class="step-wrapper">
                        <div class="step-circle active" id="sc1">1</div>
                        <div class="step-label active" id="sl1">Account</div>
                    </div>
                    <div class="step-line" id="sline1"></div>
                    <div class="step-wrapper">
                        <div class="step-circle" id="sc2">2</div>
                        <div class="step-label" id="sl2">Business</div>
                    </div>
                    <div class="step-line" id="sline2"></div>
                    <div class="step-wrapper">
                        <div class="step-circle" id="sc3">3</div>
                        <div class="step-label" id="sl3">Plan</div>
                    </div>
                    <div class="step-line" id="sline3"></div>
                    <div class="step-wrapper">
                        <div class="step-circle" id="sc4">4</div>
                        <div class="step-label" id="sl4">Submit</div>
                    </div>
                </div>
            </div>

            @if ($errors->any() && old('roles') == 3)
                <div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:10px; padding:14px; margin-bottom:20px; font-size:13px; color:#b91c1c;">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:6px 0 0 16px;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="ownerRegForm">
                @csrf
                <input type="hidden" name="roles" value="3">
                <input type="hidden" name="plan_id" id="selected_plan_id" value="{{ old('plan_id') }}">
                <input type="hidden" name="billing_type" id="selected_billing_type" value="{{ old('billing_type') }}">
                <input type="hidden" name="payment_method" id="selected_payment_method" value="{{ old('payment_method') }}">
                <input type="hidden" name="amount_paid" id="amount_paid_field" value="0">
                <input type="hidden" name="payment_status" value="pending">

                {{-- ── STEP 1: Account details ──────────────────────────── --}}
                <div class="step-pane active" id="step1">
                    <p class="reg-section-title"><i class="fas fa-user-circle" style="margin-right:6px;"></i>Personal Account Details</p>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">First Name <span class="req">*</span></label>
                                <input name="first_name" value="{{ old('first_name') }}" type="text" class="reg-input @error('first_name') is-invalid @enderror" placeholder="John">
                                @error('first_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Last Name <span class="req">*</span></label>
                                <input name="last_name" value="{{ old('last_name') }}" type="text" class="reg-input @error('last_name') is-invalid @enderror" placeholder="Doe">
                                @error('last_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Username <span class="req">*</span></label>
                                <input name="username" value="{{ old('username') }}" type="text" class="reg-input @error('username') is-invalid @enderror" placeholder="owner_johndoe">
                                @error('username')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Email Address <span class="req">*</span></label>
                                <input name="register_email" value="{{ old('register_email') }}" type="email" class="reg-input @error('register_email') is-invalid @enderror" placeholder="owner@hotel.com">
                                @error('register_email')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Phone Number <span class="req">*</span></label>
                                <input class="reg-input phone" type="tel" id="owner_phone_input" name="phone" onkeypress="validate(event)">
                                <input type="hidden" id="code" name="countrycode" value="1">
                                <input type="hidden" id="code_name" name="countrycodename" value="us">
                                @error('phone')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Residential Address <span class="req">*</span></label>
                                <input name="address" value="{{ old('address') }}" type="text" class="reg-input @error('address') is-invalid @enderror" placeholder="Your home address">
                                @error('address')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Password <span class="req">*</span></label>
                                <input name="password" id="own_password" type="password" class="reg-input @error('password') is-invalid @enderror" placeholder="Min. 6 characters">
                                @error('password')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Confirm Password <span class="req">*</span></label>
                                <input name="password_confirmation" type="password" class="reg-input" placeholder="Repeat password">
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:24px;">
                        <button type="button" class="reg-btn-primary" onclick="goToStep(2)">
                            Continue to Business Details <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>{{-- /step1 --}}

                {{-- ── STEP 2: Business details + documents ─────────────── --}}
                <div class="step-pane" id="step2">
                    <p class="reg-section-title"><i class="fas fa-building" style="margin-right:6px;"></i>Business Information</p>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Business / Hotel Name <span class="req">*</span></label>
                                <input name="business_name" value="{{ old('business_name') }}" type="text" class="reg-input @error('business_name') is-invalid @enderror" placeholder="Grand Palace Hotel">
                                @error('business_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Business Type <span class="req">*</span></label>
                                <select name="business_type" class="reg-input @error('business_type') is-invalid @enderror">
                                    <option value="">— Select Type —</option>
                                    <option value="Hotel" {{ old('business_type')=='Hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="Restaurant" {{ old('business_type')=='Restaurant' ? 'selected' : '' }}>Restaurant</option>
                                    <option value="Cafe" {{ old('business_type')=='Cafe' ? 'selected' : '' }}>Café / Coffee Shop</option>
                                    <option value="Buffet" {{ old('business_type')=='Buffet' ? 'selected' : '' }}>Buffet</option>
                                    <option value="Fast Food" {{ old('business_type')=='Fast Food' ? 'selected' : '' }}>Fast Food</option>
                                    <option value="Bakery" {{ old('business_type')=='Bakery' ? 'selected' : '' }}>Bakery / Pastry</option>
                                    <option value="Bar & Grill" {{ old('business_type')=='Bar & Grill' ? 'selected' : '' }}>Bar & Grill</option>
                                    <option value="Cloud Kitchen" {{ old('business_type')=='Cloud Kitchen' ? 'selected' : '' }}>Cloud Kitchen</option>
                                    <option value="Other" {{ old('business_type')=='Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('business_type')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Cuisine / Food Type</label>
                                <input name="cuisine_type" value="{{ old('cuisine_type') }}" type="text" class="reg-input" placeholder="e.g. Italian, Indian, Continental, Chinese">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Business Address <span class="req">*</span></label>
                                <textarea name="business_address" class="reg-input @error('business_address') is-invalid @enderror" rows="2" placeholder="Full business address including street, area">{{ old('business_address') }}</textarea>
                                @error('business_address')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">City <span class="req">*</span></label>
                                <input name="city" value="{{ old('city') }}" type="text" class="reg-input @error('city') is-invalid @enderror" placeholder="London">
                                @error('city')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">State / Province</label>
                                <input name="state" value="{{ old('state') }}" type="text" class="reg-input" placeholder="England">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Country <span class="req">*</span></label>
                                <input name="country" value="{{ old('country') }}" type="text" class="reg-input @error('country') is-invalid @enderror" placeholder="United Kingdom">
                                @error('country')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">ZIP / Postal Code</label>
                                <input name="zip_code" value="{{ old('zip_code') }}" type="text" class="reg-input" placeholder="EC1A 1BB">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Business Website</label>
                                <input name="website" value="{{ old('website') }}" type="url" class="reg-input" placeholder="https://yourhotel.com (optional)">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Business Phone Number</label>
                                <input name="business_phone" value="{{ old('business_phone') }}" type="text" class="reg-input" placeholder="+44 20 7946 0958">
                            </div>
                        </div>
                    </div>

                    <p class="reg-section-title" style="margin-top:24px;"><i class="fas fa-file-alt" style="margin-right:6px;"></i>Legal Documents</p>
                    <div class="pending-notice">
                        <i class="fas fa-info-circle" style="margin-right:6px;"></i>
                        These details help us verify your business. All information is kept confidential and secure.
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Business Registration Number</label>
                                <input name="business_registration_number" value="{{ old('business_registration_number') }}" type="text" class="reg-input" placeholder="e.g. 12345678">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Tax ID / VAT / GST Number</label>
                                <input name="tax_id" value="{{ old('tax_id') }}" type="text" class="reg-input" placeholder="e.g. GB123456789">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Food License Number</label>
                                <input name="food_license_number" value="{{ old('food_license_number') }}" type="text" class="reg-input" placeholder="e.g. FSSAI-123456">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Owner / Contact Name <span class="req">*</span></label>
                                <input name="owner_name" value="{{ old('owner_name') }}" type="text" class="reg-input @error('owner_name') is-invalid @enderror" placeholder="Full legal name">
                                @error('owner_name')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Owner Email <span class="req">*</span></label>
                                <input name="owner_email" value="{{ old('owner_email') }}" type="email" class="reg-input @error('owner_email') is-invalid @enderror" placeholder="owner@business.com">
                                @error('owner_email')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="reg-field">
                                <label class="reg-label">Owner Direct Phone <span class="req">*</span></label>
                                <input name="owner_phone" value="{{ old('owner_phone') }}" type="text" class="reg-input @error('owner_phone') is-invalid @enderror" placeholder="+44 7700 900000">
                                @error('owner_phone')<span class="reg-invalid">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="reg-field">
                                <label class="reg-label">Additional Notes</label>
                                <textarea name="notes" class="reg-input" rows="2" placeholder="Anything else you'd like us to know about your business...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; margin-top:24px;">
                        <button type="button" class="reg-btn-secondary" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="reg-btn-primary" onclick="goToStep(3)" style="flex:1;">
                            Continue to Plan Selection <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>{{-- /step2 --}}

                {{-- ── STEP 3: Plan selection ─────────────────────────────── --}}
                <div class="step-pane" id="step3">
                    <p class="reg-section-title"><i class="fas fa-crown" style="margin-right:6px;"></i>Choose Your Plan</p>
                    <p style="font-size:13px; color:#666; margin-bottom:20px;">Select the plan that best suits your business. You can upgrade at any time.</p>

                    @if ($plans->isEmpty())
                        <div style="text-align:center; padding:40px; color:#888; border:2px dashed #e5e7eb; border-radius:12px;">
                            <i class="fas fa-tags" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                            No plans available yet. Our team will contact you with options.
                            <input type="hidden" name="plan_id" value="">
                            <input type="hidden" name="billing_type" value="">
                        </div>
                    @else
                        <div class="plan-grid" id="planGrid">
                            @foreach ($plans as $plan)
                                <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }} {{ old('plan_id') == $plan->id ? 'selected' : '' }}"
                                     data-plan-id="{{ $plan->id }}"
                                     data-billing-type="{{ $plan->billing_type }}"
                                     data-price="{{ $plan->price }}"
                                     data-commission="{{ $plan->commission_rate }}"
                                     data-trial="{{ $plan->trial_days }}"
                                     onclick="selectPlan(this)">
                                    <div class="plan-check"><i class="fas fa-check"></i></div>
                                    <span class="plan-badge {{ $plan->billing_type == 5 ? 'sub' : 'comm' }}">
                                        {{ $plan->billing_type == 5 ? 'Subscription' : 'Commission' }}
                                    </span>
                                    <div class="plan-name">{{ $plan->name }}</div>
                                    <div class="plan-price">
                                        @if ($plan->billing_type == 5)
                                            {{ setting('currency_symbol') ?? '₹' }}{{ number_format($plan->price, 2) }}<small> / month</small>
                                        @else
                                            {{ $plan->commission_rate }}%<small> per order</small>
                                        @endif
                                    </div>
                                    @if ($plan->description)
                                        <p style="font-size:12px; color:#888; margin-bottom:10px;">{{ $plan->description }}</p>
                                    @endif
                                    @if ($plan->features)
                                        <ul class="plan-features">
                                            @foreach ($plan->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($plan->trial_days > 0)
                                        <div style="margin-top:10px; font-size:11px; font-weight:700; color:#22c55e; background:#f0fdf4; padding:4px 8px; border-radius:6px; display:inline-block;">
                                            {{ $plan->trial_days }}-day free trial
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @error('plan_id')<span class="reg-invalid" style="display:block; margin-top:8px;">{{ $message }}</span>@enderror
                    @endif

                    <div style="display:flex; gap:12px; margin-top:28px;">
                        <button type="button" class="reg-btn-secondary" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="reg-btn-primary" onclick="goToStep(4)" style="flex:1;" id="toPlanBtn">
                            Continue to Submit <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>{{-- /step3 --}}

                {{-- ── STEP 4: Review & Submit ────────────────────────────── --}}
                <div class="step-pane" id="step4">
                    <p class="reg-section-title"><i class="fas fa-paper-plane" style="margin-right:6px;"></i>Review & Submit Application</p>

                    {{-- Summary --}}
                    <div class="reg-summary" id="applicationSummary">
                        <div class="reg-summary-title"><i class="fas fa-clipboard-list" style="margin-right:6px;"></i>Application Summary</div>
                        <div class="reg-summary-row"><span>Business Name</span> <strong id="sum_business">—</strong></div>
                        <div class="reg-summary-row"><span>Business Type</span> <strong id="sum_type">—</strong></div>
                        <div class="reg-summary-row"><span>City / Country</span> <strong id="sum_location">—</strong></div>
                        <div class="reg-summary-row"><span>Selected Plan</span> <strong id="sum_plan">—</strong></div>
                        <div class="reg-summary-row"><span>Billing</span> <strong id="sum_billing">—</strong></div>
                    </div>

                    {{-- Payment section (only for subscription plans with price > 0) --}}
                    <div id="paymentSection" style="display:none;">
                        <p class="reg-section-title"><i class="fas fa-credit-card" style="margin-right:6px;"></i>Payment Method</p>
                        <p style="font-size:13px; color:#666; margin-bottom:16px;">
                            Your selected plan requires an initial payment. Choose a payment method:
                        </p>

                        @php $gateways = $gateways ?? collect(); @endphp
                        @if ($gateways->isNotEmpty())
                            <div class="payment-method-grid">
                                @foreach ($gateways as $gw)
                                    <div class="payment-method-card {{ old('payment_method') == $gw->name ? 'selected' : '' }}"
                                         data-method="{{ $gw->name }}"
                                         onclick="selectPayment(this)">
                                        @php
                                            $icons = [
                                                'stripe'    => 'fab fa-stripe-s',
                                                'paypal'    => 'fab fa-paypal',
                                                'razorpay'  => 'fas fa-rupee-sign',
                                                'paystack'  => 'fas fa-dollar-sign',
                                                'sslcommerz'=> 'fas fa-lock',
                                            ];
                                            $icon = $icons[strtolower($gw->name)] ?? 'fas fa-credit-card';
                                        @endphp
                                        <i class="{{ $icon }}"></i>
                                        {{ ucfirst($gw->name) }}
                                    </div>
                                @endforeach
                                <div class="payment-method-card {{ old('payment_method') == 'offline' ? 'selected' : '' }}"
                                     data-method="offline" onclick="selectPayment(this)">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Pay Later / Offline
                                </div>
                            </div>
                        @else
                            <div class="payment-method-grid">
                                <div class="payment-method-card selected" data-method="offline" onclick="selectPayment(this)">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Pay Later / Offline
                                </div>
                            </div>
                            <input type="hidden" id="selected_payment_method" value="offline">
                        @endif
                    </div>

                    {{-- Pending approval notice --}}
                    <div class="pending-notice" style="margin-top:20px;">
                        <i class="fas fa-hourglass-half" style="margin-right:6px;"></i>
                        <strong>Verification Required:</strong> After submitting, your application will be reviewed by our admin team. You'll receive an email notification once approved (typically within 1–2 business days). You can log in only after approval.
                    </div>

                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button type="button" class="reg-btn-secondary" onclick="goToStep(3)">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="reg-btn-primary" style="flex:1;" onclick="return validateFinalStep()">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                    </div>
                </div>{{-- /step4 --}}

            </form>
        </div>{{-- /ownerForm --}}

    </div>{{-- /reg-left --}}

    {{-- ── RIGHT: Hero panel ───────────────────────────────────────────────── --}}
    <div class="reg-right">
        <div class="reg-right-inner">
            <div style="font-size:52px; margin-bottom:16px;">🍽️</div>
            <h2>Join {{ setting('site_name') ?? 'our platform' }}</h2>
            <p>List your hotel or restaurant, reach thousands of hungry customers, and grow your business with our powerful tools.</p>
            <div style="margin-top:32px; display:flex; flex-direction:column; gap:14px; text-align:left; width:100%;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✓</div>
                    <span>Manage reservations & orders from one dashboard</span>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✓</div>
                    <span>Set your own menu, pricing and availability</span>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; background:rgba(255,255,255,.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">✓</div>
                    <span>Flexible subscription or commission-based plans</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput-jquery.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/intlTelInput.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/utils.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/data.js') }}"></script>
    <script defer src="{{ asset('frontend/lib/inttelinput/js/init.js') }}"></script>
    <script src="{{ asset('js/phone_validation/index.js') }}"></script>

    <script>
    // ── Role switcher ─────────────────────────────────────────────────────────
    function switchRole(role) {
        var isOwner = role === 'owner';
        document.getElementById('customerForm').style.display = isOwner ? 'none' : 'block';
        document.getElementById('ownerForm').style.display    = isOwner ? 'block' : 'none';
        document.querySelectorAll('.reg-tab-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.role === role);
        });
        if (isOwner) goToStep(1);
    }

    // ── Step navigation ───────────────────────────────────────────────────────
    var currentStep = 1;

    function goToStep(n) {
        // Validate step 3 requires a plan selected
        if (n === 4 && !document.getElementById('selected_plan_id').value) {
            alert('Please select a plan before continuing.');
            return;
        }

        document.querySelectorAll('.step-pane').forEach(function(p) { p.classList.remove('active'); });
        document.getElementById('step' + n).classList.add('active');
        currentStep = n;
        updateWizardUI(n);

        if (n === 4) populateSummary();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateWizardUI(active) {
        for (var i = 1; i <= 4; i++) {
            var circle = document.getElementById('sc' + i);
            var label  = document.getElementById('sl' + i);
            circle.classList.remove('active', 'done');
            label.classList.remove('active', 'done');
            if (i < active) {
                circle.classList.add('done');
                circle.innerHTML = '<i class="fas fa-check" style="font-size:11px;"></i>';
                label.classList.add('done');
            } else if (i === active) {
                circle.classList.add('active');
                circle.innerHTML = i;
                label.classList.add('active');
            } else {
                circle.innerHTML = i;
            }
            if (i < 4) {
                var line = document.getElementById('sline' + i);
                line.classList.toggle('done', i < active);
            }
        }
    }

    // ── Plan selection ────────────────────────────────────────────────────────
    function selectPlan(card) {
        document.querySelectorAll('.plan-card').forEach(function(c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        document.getElementById('selected_plan_id').value    = card.dataset.planId;
        document.getElementById('selected_billing_type').value = card.dataset.billingType;

        // Show payment section only for paid subscription plans
        var price = parseFloat(card.dataset.price || 0);
        var isSubscription = card.dataset.billingType == 5;
        var paymentSection = document.getElementById('paymentSection');
        if (paymentSection) {
            paymentSection.style.display = (isSubscription && price > 0) ? 'block' : 'none';
        }
        document.getElementById('amount_paid_field').value = isSubscription ? price : 0;
    }

    // ── Payment selection ─────────────────────────────────────────────────────
    function selectPayment(card) {
        document.querySelectorAll('.payment-method-card').forEach(function(c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        document.getElementById('selected_payment_method').value = card.dataset.method;
    }

    // ── Summary population ────────────────────────────────────────────────────
    function populateSummary() {
        var bn  = document.querySelector('[name="business_name"]');
        var bt  = document.querySelector('[name="business_type"]');
        var city = document.querySelector('[name="city"]');
        var cntry = document.querySelector('[name="country"]');
        var selectedPlan = document.querySelector('.plan-card.selected');

        document.getElementById('sum_business').textContent  = bn    ? (bn.value || '—') : '—';
        document.getElementById('sum_type').textContent      = bt    ? (bt.value || '—') : '—';
        document.getElementById('sum_location').textContent  = [city ? city.value : '', cntry ? cntry.value : ''].filter(Boolean).join(', ') || '—';

        if (selectedPlan) {
            var planName = selectedPlan.querySelector('.plan-name');
            var planPrice = selectedPlan.querySelector('.plan-price');
            document.getElementById('sum_plan').textContent    = planName  ? planName.textContent.trim() : '—';
            document.getElementById('sum_billing').textContent = planPrice ? planPrice.textContent.trim() : '—';
        }
    }

    // ── Final step validation ─────────────────────────────────────────────────
    function validateFinalStep() {
        if (!document.getElementById('selected_plan_id').value) {
            alert('Please go back and select a plan.');
            return false;
        }
        return true;
    }

    // ── Restore state on page load (after validation error) ──────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var oldRole = {{ old('roles', 2) }};
        if (oldRole == 3) {
            switchRole('owner');
            // Jump to the last step that had data if there are errors
            @if ($errors->any())
                goToStep(1);
            @endif
        }

        // Pre-select plan if old value exists
        var oldPlanId = '{{ old("plan_id") }}';
        if (oldPlanId) {
            var planCard = document.querySelector('.plan-card[data-plan-id="' + oldPlanId + '"]');
            if (planCard) selectPlan(planCard);
        }
    });
    </script>
@endpush
