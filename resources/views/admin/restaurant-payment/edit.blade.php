@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.restaurant-payment.index') }}">Payment Settings</a></li>
                <li class="breadcrumb-item active">{{ $restaurant->name }}</li>
            </ol>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <form action="{{ auth()->user()->myrole == 1 ? route('admin.restaurant-payment.update', $restaurant) : route('admin.restaurant-payment.owner.update') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- UPI Settings --}}
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo-vector.svg/120px-UPI-Logo-vector.svg.png"
                             style="height:20px;margin-right:8px;" alt="UPI"> UPI Settings
                    </h3>
                </div>
                <div class="db-card-body">
                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">UPI ID</label>
                            <input type="text" name="upi_id"
                                   class="db-field-control @error('upi_id') invalid @enderror"
                                   value="{{ old('upi_id', $setting->upi_id) }}"
                                   placeholder="e.g. hotelname@okicici">
                            <small class="text-gray-400 text-xs">Customers will see this UPI ID to pay directly.</small>
                            @error('upi_id')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Accept UPI Payments</label>
                            <div class="flex items-center gap-3 mt-2">
                                <input type="hidden" name="accept_upi" value="0">
                                <input type="checkbox" name="accept_upi" value="1" id="accept_upi"
                                       {{ old('accept_upi', $setting->accept_upi) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded accent-primary cursor-pointer">
                                <label for="accept_upi" class="text-sm text-gray-600 cursor-pointer">
                                    Show UPI payment option to customers
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PhonePe QR --}}
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <span style="color:#5f259f;font-weight:800;margin-right:8px;">Pe</span> PhonePe QR Code
                    </h3>
                </div>
                <div class="db-card-body">
                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Accept PhonePe QR</label>
                            <div class="flex items-center gap-3 mt-2">
                                <input type="hidden" name="accept_phonepe_qr" value="0">
                                <input type="checkbox" name="accept_phonepe_qr" value="1" id="accept_phonepe_qr"
                                       {{ old('accept_phonepe_qr', $setting->accept_phonepe_qr) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded accent-primary cursor-pointer">
                                <label for="accept_phonepe_qr" class="text-sm text-gray-600 cursor-pointer">
                                    Enable PhonePe QR code payment option
                                </label>
                            </div>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Upload PhonePe QR Image</label>
                            @if ($setting->phonepe_qr_image)
                                <div class="mb-2">
                                    <p class="text-xs text-gray-500 mb-1">Current QR:</p>
                                    <img src="{{ $setting->phonepe_qr_image }}" alt="PhonePe QR"
                                         style="width:120px;height:120px;object-fit:contain;border:1px solid #eee;border-radius:8px;">
                                </div>
                            @endif
                            <input type="file" name="phonepe_qr_image" accept="image/*"
                                   class="db-field-control @error('phonepe_qr_image') invalid @enderror">
                            <small class="text-gray-400 text-xs">Upload your PhonePe merchant QR code. PNG/JPG, max 3MB.</small>
                            @error('phonepe_qr_image')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paytm QR --}}
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <span style="color:#00baf2;font-weight:800;margin-right:8px;">Pt</span> Paytm QR Code
                    </h3>
                </div>
                <div class="db-card-body">
                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Accept Paytm QR</label>
                            <div class="flex items-center gap-3 mt-2">
                                <input type="hidden" name="accept_paytm_qr" value="0">
                                <input type="checkbox" name="accept_paytm_qr" value="1" id="accept_paytm_qr"
                                       {{ old('accept_paytm_qr', $setting->accept_paytm_qr) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded accent-primary cursor-pointer">
                                <label for="accept_paytm_qr" class="text-sm text-gray-600 cursor-pointer">
                                    Enable Paytm QR code payment option
                                </label>
                            </div>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Upload Paytm QR Image</label>
                            @if ($setting->paytm_qr_image)
                                <div class="mb-2">
                                    <p class="text-xs text-gray-500 mb-1">Current QR:</p>
                                    <img src="{{ $setting->paytm_qr_image }}" alt="Paytm QR"
                                         style="width:120px;height:120px;object-fit:contain;border:1px solid #eee;border-radius:8px;">
                                </div>
                            @endif
                            <input type="file" name="paytm_qr_image" accept="image/*"
                                   class="db-field-control @error('paytm_qr_image') invalid @enderror">
                            <small class="text-gray-400 text-xs">Upload your Paytm merchant QR code. PNG/JPG, max 3MB.</small>
                            @error('paytm_qr_image')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advance Booking --}}
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <i class="fas fa-calendar-check mr-2 text-primary"></i> Advance Booking Deposit
                    </h3>
                </div>
                <div class="db-card-body">
                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required">Advance Deposit Percentage</label>
                            <input type="number" name="advance_booking_percent" min="0" max="100"
                                   class="db-field-control @error('advance_booking_percent') invalid @enderror"
                                   value="{{ old('advance_booking_percent', $setting->advance_booking_percent) }}"
                                   placeholder="0">
                            <small class="text-gray-400 text-xs">
                                % of estimated bill charged as advance when a customer books a table.
                                Set to 0 to disable advance requirement.
                            </small>
                            @error('advance_booking_percent')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Estimated Amount Per Guest (₹)</label>
                            <input type="number" name="per_head_estimate" min="0" step="1"
                                   class="db-field-control @error('per_head_estimate') invalid @enderror"
                                   value="{{ old('per_head_estimate', $setting->per_head_estimate) }}"
                                   placeholder="e.g. 500">
                            <small class="text-gray-400 text-xs">
                                Used to calculate advance. E.g. ₹500/head × 4 guests × 20% = ₹400 advance.
                            </small>
                            @error('per_head_estimate')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    {{-- Live advance calculator --}}
                    <div id="advanceCalc" class="mt-3 p-3 bg-orange-50 border border-orange-100 rounded-xl text-sm text-orange-700 hidden">
                        <i class="fas fa-calculator mr-2"></i>
                        Example: For <strong id="calcGuests">4</strong> guests →
                        ₹<strong id="calcEstimate">0</strong> estimated ×
                        <strong id="calcPct">0</strong>% =
                        <strong>₹<span id="calcResult">0</span> advance</strong>
                    </div>
                </div>
            </div>

            {{-- Commission (admin only) --}}
            @if (auth()->user()->myrole == 1)
            <div class="db-card mb-4">
                <div class="db-card-header">
                    <h3 class="db-card-title">
                        <i class="fas fa-percent mr-2 text-primary"></i> Commission Plan (Admin Only)
                    </h3>
                </div>
                <div class="db-card-body">
                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Commission Plan</label>
                            <div class="db-field-down-arrow">
                                <select name="plan_id" class="db-field-control appearance-none">
                                    <option value="">— Use global commission rate —</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}"
                                            {{ old('plan_id', $setting->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} ({{ $plan->commission_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Custom Commission Rate (%)</label>
                            <input type="number" name="commission_rate" min="0" max="100" step="0.01"
                                   class="db-field-control @error('commission_rate') invalid @enderror"
                                   value="{{ old('commission_rate', $setting->commission_rate) }}"
                                   placeholder="0">
                            <small class="text-gray-400 text-xs">
                                Override the plan's rate if needed. Leave 0 to use plan rate or global default.
                            </small>
                            @error('commission_rate')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-col-12 mt-2 flex gap-3">
                <button type="submit" class="db-btn text-white bg-primary">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Save Payment Settings</span>
                </button>
                @if (auth()->user()->myrole == 1)
                    <a href="{{ route('admin.restaurant-payment.index') }}" class="db-btn bg-gray-100 text-gray-600">
                        Cancel
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Right: Preview panel --}}
    <div class="col-12 col-lg-4">
        <div class="db-card sticky top-4">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-eye mr-2 text-primary"></i>Customer Preview</h3>
            </div>
            <div class="db-card-body p-4">
                <p class="text-xs text-gray-500 mb-3">This is what customers see at checkout / booking:</p>

                <div style="background:#fff5f7;border-radius:12px;padding:16px;border:1px solid #FFE8ED;">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Payment Options</p>
                    <div class="flex flex-col gap-2">
                        @if ($setting->upi_id && $setting->accept_upi)
                            <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                <span style="font-size:18px;">💳</span>
                                <div>
                                    <p style="font-size:12px;font-weight:700;">Pay via UPI</p>
                                    <p style="font-size:11px;color:#888;">{{ $setting->upi_id }}</p>
                                </div>
                            </div>
                        @endif
                        @if ($setting->accept_phonepe_qr)
                            <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                <span style="font-size:18px;color:#5f259f;font-weight:800;">Pe</span>
                                <p style="font-size:12px;font-weight:700;">Scan PhonePe QR</p>
                            </div>
                        @endif
                        @if ($setting->accept_paytm_qr)
                            <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                <span style="font-size:18px;color:#00baf2;font-weight:800;">Pt</span>
                                <p style="font-size:12px;font-weight:700;">Scan Paytm QR</p>
                            </div>
                        @endif
                        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                            <span style="font-size:18px;">💵</span>
                            <p style="font-size:12px;font-weight:700;">Cash on Delivery / At Counter</p>
                        </div>
                    </div>

                    @if ($setting->advance_booking_percent > 0 && $setting->per_head_estimate > 0)
                        <div style="margin-top:12px;padding:10px;background:#fff3cd;border-radius:8px;font-size:11px;color:#856404;">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Advance Required:</strong> {{ $setting->advance_booking_percent }}% of estimated bill
                            (₹{{ number_format($setting->per_head_estimate) }}/guest) is collected at booking.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    function calcAdvance() {
        var pct  = parseFloat(document.querySelector('[name="advance_booking_percent"]').value) || 0;
        var head = parseFloat(document.querySelector('[name="per_head_estimate"]').value) || 0;
        var calc = document.getElementById('advanceCalc');
        if (pct > 0 && head > 0) {
            var guests   = 4; // example
            var estimate = head * guests;
            var advance  = Math.round(estimate * pct / 100);
            document.getElementById('calcGuests').textContent   = guests;
            document.getElementById('calcEstimate').textContent = estimate;
            document.getElementById('calcPct').textContent      = pct;
            document.getElementById('calcResult').textContent   = advance;
            calc.classList.remove('hidden');
        } else {
            calc.classList.add('hidden');
        }
    }

    document.querySelector('[name="advance_booking_percent"]').addEventListener('input', calcAdvance);
    document.querySelector('[name="per_head_estimate"]').addEventListener('input', calcAdvance);
    calcAdvance();
}());
</script>
@endpush
