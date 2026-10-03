@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.restaurant-application.index') }}">Applications</a></li>
                <li class="breadcrumb-item active">{{ $application->business_name }}</li>
            </ol>
        </div>
    </div>

    <div class="col-12 col-lg-8">

        {{-- Status banner --}}
        @if ($application->status == 0)
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
                <div>
                    <p style="font-weight:700; color:#92400e; margin:0;">Pending Review</p>
                    <p style="font-size:13px; color:#b45309; margin:0;">This application is awaiting admin approval.</p>
                </div>
            </div>
        @elseif ($application->status == 1)
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                <div>
                    <p style="font-weight:700; color:#15803d; margin:0;">Approved</p>
                    <p style="font-size:13px; color:#166534; margin:0;">Reviewed by {{ $application->reviewer?->name ?? 'Admin' }} on {{ $application->reviewed_at?->format('d M Y, H:i') }}</p>
                </div>
            </div>
        @else
            <div style="background:#fef2f2; border:1px solid #fecdd3; border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-times-circle text-red-500 text-xl"></i>
                <div>
                    <p style="font-weight:700; color:#b91c1c; margin:0;">Rejected</p>
                    <p style="font-size:13px; color:#991b1b; margin:0;">Reason: {{ $application->rejection_reason }}</p>
                </div>
            </div>
        @endif

        {{-- Business details --}}
        <div class="db-card mb-4">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-building mr-2 text-primary"></i>Business Information</h3>
            </div>
            <div class="db-card-body">
                <div class="row g-3">
                    @php
                    $details = [
                        'Business Name'         => $application->business_name,
                        'Business Type'         => $application->business_type,
                        'Cuisine / Food Type'   => $application->cuisine_type ?: '—',
                        'Business Address'      => $application->business_address,
                        'City'                  => $application->city,
                        'State'                 => $application->state ?: '—',
                        'Country'               => $application->country,
                        'ZIP Code'              => $application->zip_code ?: '—',
                        'Website'               => $application->website ?: '—',
                        'Business Phone'        => $application->business_phone ?: '—',
                    ];
                    @endphp
                    @foreach ($details as $label => $value)
                        <div class="col-12 col-sm-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $label }}</p>
                            <p class="text-sm font-medium text-gray-800">
                                @if (str_starts_with((string)$value, 'http'))
                                    <a href="{{ $value }}" target="_blank" class="text-primary underline">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Legal documents --}}
        <div class="db-card mb-4">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-file-alt mr-2 text-primary"></i>Legal Documents</h3>
            </div>
            <div class="db-card-body">
                <div class="row g-3">
                    @php
                    $legal = [
                        'Business Registration #' => $application->business_registration_number ?: '—',
                        'Tax ID / VAT / GST'      => $application->tax_id ?: '—',
                        'Food License Number'     => $application->food_license_number ?: '—',
                        'Owner Name'              => $application->owner_name,
                        'Owner Email'             => $application->owner_email,
                        'Owner Phone'             => $application->owner_phone,
                    ];
                    @endphp
                    @foreach ($legal as $label => $value)
                        <div class="col-12 col-sm-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $label }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Plan & payment --}}
        <div class="db-card mb-4">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-crown mr-2 text-primary"></i>Plan & Payment</h3>
            </div>
            <div class="db-card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Selected Plan</p>
                        <p class="text-sm font-bold text-gray-800">{{ $application->plan?->name ?? 'No plan' }}</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Billing Type</p>
                        <p class="text-sm font-medium">
                            @if ($application->billing_type == 5)
                                <span class="db-table-badge bg-blue-100 text-blue-700">Subscription</span>
                            @elseif ($application->billing_type == 10)
                                <span class="db-table-badge bg-purple-100 text-purple-700">Commission</span>
                            @else —
                            @endif
                        </p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Payment Method</p>
                        <p class="text-sm font-medium text-gray-800">{{ $application->payment_method ? ucfirst($application->payment_method) : '—' }}</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Amount Paid</p>
                        <p class="text-sm font-bold text-gray-800">{{ $application->amount_paid > 0 ? currencyFormat($application->amount_paid) : '—' }}</p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Payment Status</p>
                        <p class="text-sm font-medium">
                            <span class="db-table-badge {{ $application->payment_status == 'paid' ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100' }}">
                                {{ ucfirst($application->payment_status) }}
                            </span>
                        </p>
                    </div>
                    @if ($application->payment_transaction_id)
                        <div class="col-12">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Transaction ID</p>
                            <p class="text-sm font-mono text-gray-700">{{ $application->payment_transaction_id }}</p>
                        </div>
                    @endif
                    @if ($application->notes)
                        <div class="col-12">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Applicant Notes</p>
                            <p class="text-sm text-gray-700">{{ $application->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @if ($application->status == 0)
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title"><i class="fas fa-gavel mr-2 text-primary"></i>Decision</h3>
                </div>
                <div class="db-card-body">
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <form action="{{ route('admin.restaurant-application.approve', $application) }}" method="POST"
                              onsubmit="return confirm('Approve this application?')">
                            @csrf
                            <button type="submit"
                                    style="height:44px; padding:0 24px; background:#16a34a; color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; gap:8px;">
                                <i class="fas fa-check-circle"></i> Approve Application
                            </button>
                        </form>

                        <button type="button"
                                style="height:44px; padding:0 24px; background:#ef4444; color:#fff; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; gap:8px;"
                                onclick="document.getElementById('rejectSection').style.display='block'">
                            <i class="fas fa-times-circle"></i> Reject Application
                        </button>
                    </div>

                    <div id="rejectSection" style="display:none; margin-top:20px; padding:20px; background:#fef2f2; border-radius:12px; border:1px solid #fecdd3;">
                        <form action="{{ route('admin.restaurant-application.reject', $application) }}" method="POST">
                            @csrf
                            <label style="font-size:13px; font-weight:700; color:#b91c1c; display:block; margin-bottom:8px;">
                                Rejection Reason <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="rejection_reason" required
                                      style="width:100%; border:1.5px solid #fecdd3; border-radius:10px; padding:12px; font-size:14px; height:80px; background:#fff; outline:none;"
                                      placeholder="Explain why the application is being rejected..."></textarea>
                            <button type="submit" style="margin-top:12px; height:44px; padding:0 24px; background:#ef4444; color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                                Confirm Rejection
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Right: applicant account info --}}
    <div class="col-12 col-lg-4">
        <div class="db-card sticky top-4">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-user mr-2 text-primary"></i>Account Info</h3>
            </div>
            <div class="db-card-body">
                @if ($application->user)
                    <div style="text-align:center; padding:12px 0 20px;">
                        <img src="{{ $application->user->image }}" alt="avatar"
                             style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:3px solid #f0f0f0; margin-bottom:8px;">
                        <p style="font-weight:700; font-size:15px;">{{ $application->user->first_name }} {{ $application->user->last_name }}</p>
                        <p style="font-size:13px; color:#888;">{{ $application->user->email }}</p>
                    </div>
                    <div style="border-top:1px solid #f0f0f0; padding-top:16px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; padding:6px 0;">
                            <span style="color:#888;">Registered</span>
                            <strong>{{ $application->user->created_at->format('d M Y') }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; padding:6px 0;">
                            <span style="color:#888;">Account Status</span>
                            <strong>{{ $application->user->status ? 'Active' : 'Inactive' }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; padding:6px 0;">
                            <span style="color:#888;">Application</span>
                            {!! $application->status_badge !!}
                        </div>
                    </div>
                @endif
                <div style="margin-top:20px;">
                    <a href="{{ route('admin.restaurant-application.index') }}"
                       class="db-btn w-full justify-center bg-gray-100 text-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Applications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
