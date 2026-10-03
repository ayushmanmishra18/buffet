@extends('admin.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="custome-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.plan.index') }}">Plans</a></li>
                <li class="breadcrumb-item active">Create Plan</li>
            </ol>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">Create New Plan</h3>
            </div>
            <div class="db-card-body">
                <form action="{{ route('admin.plan.store') }}" method="POST" id="planForm">
                    @csrf

                    <div class="row">
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required">Plan Name</label>
                            <input type="text" name="name" class="db-field-control @error('name') invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Starter, Professional, Enterprise">
                            @error('name')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required">Billing Type</label>
                            <div class="db-field-down-arrow">
                                <select name="billing_type" id="billing_type"
                                        class="db-field-control appearance-none @error('billing_type') invalid @enderror"
                                        onchange="toggleBillingFields()">
                                    <option value="">— Select Type —</option>
                                    @foreach ($billingTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('billing_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('billing_type')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        {{-- Subscription fields --}}
                        <div class="form-col-12 sm:form-col-6" id="priceField" style="display:none;">
                            <label class="db-field-title">Monthly Price ({{ setting('currency_symbol') ?? '₹' }})</label>
                            <input type="number" name="price" class="db-field-control @error('price') invalid @enderror"
                                   value="{{ old('price', 0) }}" step="0.01" min="0" placeholder="0.00">
                            @error('price')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        {{-- Commission fields --}}
                        <div class="form-col-12 sm:form-col-6" id="commissionField" style="display:none;">
                            <label class="db-field-title">Commission Rate (%)</label>
                            <input type="number" name="commission_rate"
                                   class="db-field-control @error('commission_rate') invalid @enderror"
                                   value="{{ old('commission_rate', 0) }}" step="0.01" min="0" max="100" placeholder="e.g. 10">
                            @error('commission_rate')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Free Trial Days</label>
                            <input type="number" name="trial_days"
                                   class="db-field-control @error('trial_days') invalid @enderror"
                                   value="{{ old('trial_days', 0) }}" min="0" placeholder="0 = no trial">
                            @error('trial_days')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title">Description</label>
                            <input type="text" name="description"
                                   class="db-field-control @error('description') invalid @enderror"
                                   value="{{ old('description') }}" placeholder="Short description shown on the plan card">
                            @error('description')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-col-12">
                            <label class="db-field-title">Features <span class="text-gray-400 text-xs">(one per line)</span></label>
                            <textarea name="features" class="db-field-control @error('features') invalid @enderror"
                                      rows="5" placeholder="Unlimited menu items&#10;Priority support&#10;Analytics dashboard&#10;Custom QR code">{{ old('features') }}</textarea>
                            @error('features')<small class="db-field-alert">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required">Status</label>
                            <div class="db-field-down-arrow">
                                <select name="status" class="db-field-control appearance-none">
                                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">Mark as Featured / Popular</label>
                            <div class="flex items-center gap-3 mt-2">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" id="is_featured"
                                       {{ old('is_featured') ? 'checked' : '' }}
                                       class="w-5 h-5 rounded accent-primary cursor-pointer">
                                <label for="is_featured" class="text-sm text-gray-600 cursor-pointer">
                                    Show "Popular" badge on registration page
                                </label>
                            </div>
                        </div>

                        <div class="form-col-12 mt-2">
                            <button type="submit" class="db-btn text-white bg-primary">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Create Plan</span>
                            </button>
                            <a href="{{ route('admin.plan.index') }}" class="db-btn ml-2 bg-gray-100 text-gray-600">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function toggleBillingFields() {
    var type = document.getElementById('billing_type').value;
    document.getElementById('priceField').style.display      = (type == '5') ? 'block' : 'none';
    document.getElementById('commissionField').style.display = (type == '10') ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleBillingFields);
</script>
@endpush
