@extends('admin.app')

@section('css')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.9/jquery.datetimepicker.min.css"
        integrity="sha512-f0tzWhCwVFS3WeYaofoLWkTP62ObhewQ1EZn65oSYDZUg1+CyywGKkWzm8BxaJj5HGKI72PnMH9jYyIFz+GH7g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="custome-breadcrumb">
                {{ Breadcrumbs::render('coupons/add') }}
            </div>
        </div>


        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">{{ __('levels.coupon') }}</h3>
                </div>
                <div class="db-card-body">
                    <form action="{{ route('admin.coupon.store') }}" method="POST" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf
                        <div class="row">
                            @if (auth()->user()->myrole != App\Enums\UserRole::RESTAURANTOWNER)
                                <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                    <label class="db-field-title">{{ __('levels.restaurants') }}</label>
                                    <div class="db-field-down-arrow">
                                        <select name="restaurant_id" id="area"
                                            class="db-field-control select2 appearance-none @error('restaurant_id') invalid @enderror">
                                            <option value="0">{{ __('levels.select_restaurant') }}</option>
                                            @if (!blank($restaurants))
                                                @foreach ($restaurants as $restaurant)
                                                    <option value="{{ $restaurant->id }}"
                                                        {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                                                        {{ $restaurant->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    @error('restaurant_id')
                                        <small class="db-field-alert">{{ $message }}</small>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="restaurant_id" value="{{ auth()->user()->restaurant->id }}">
                            @endif

                            @if (auth()->user()->myrole != App\Enums\UserRole::RESTAURANTOWNER)
                                <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                    <label class="db-field-title required">{{ __('levels.name') }}</label>
                                    <input type="text" name="name"
                                        class="db-field-control @error('name') invalid @enderror"
                                        value="{{ old('name') }}">

                                    @error('name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                    @enderror
                                </div>
                            @else
                                <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                    <label class="db-field-title required">{{ __('levels.name') }}</label>
                                    <input type="text" name="name"
                                        class="db-field-control @error('name') invalid @enderror"
                                        value="{{ old('name') }}">

                                    @error('name')
                                        <small class="db-field-alert">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endif

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required">{{ __('levels.discount_type') }}</label>
                                <div class="db-field-down-arrow">
                                    <select name="discount_type" id="discount_type_select"
                                        class="db-field-control select2 appearance-none @error('discount_type') invalid @enderror"
                                        onchange="
                                            var v=this.value,
                                                l=document.getElementById('amount_label'),
                                                h=document.getElementById('amount_hint'),
                                                s=document.getElementById('amount_suffix'),
                                                i=document.getElementById('amount_field');
                                            if(v=='10'){l.textContent='Discount Percentage';if(s)s.textContent='%';if(h)h.textContent='E.g. 15 = 15% off';if(i){i.max=99;i.placeholder='e.g. 15';}}
                                            else if(v=='5'){l.textContent='Discount Amount (₹)';if(s)s.textContent='₹';if(h)h.textContent='E.g. 100 = ₹100 flat off';if(i){i.removeAttribute(\'max\');i.placeholder='e.g. 100';}}
                                            else{l.textContent='Discount Amount';if(s)s.textContent='';if(h)h.textContent='Select discount type first';}
                                        ">
                                        <option value="">{{ __('levels.select_coupon_type') }}</option>
                                        @foreach (trans('discount_types') as $key => $discount_type)
                                            <option value="{{ $key }}"
                                                {{ old('discount_type') == $key ? 'selected' : '' }}>
                                                {{ $discount_type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @error('discount_type')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required" id="amount_label">Amount</label>
                                <div style="position:relative;">
                                    <input type="number" name="amount" id="amount_field"
                                        class="db-field-control @error('amount') invalid @enderror"
                                        value="{{ old('amount') }}"
                                        placeholder="e.g. 10"
                                        min="0.01" step="0.01">
                                    <span id="amount_suffix" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:700;color:#EE1D48;pointer-events:none;"></span>
                                </div>
                                <small id="amount_hint" style="color:#888;font-size:11px;margin-top:4px;display:block;">Select discount type first</small>

                                @error('amount')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title">{{ __('levels.minimum_order_amount') }}</label>
                                <input type="text" name="minimum_order_amount"
                                    class="db-field-control @error('minimum_order_amount') invalid @enderror"
                                    value="{{ old('minimum_order_amount') }}">

                                @error('minimum_order_amount')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required">{{ __('levels.limit') }}</label>
                                <input type="number" name="limit"
                                    class="db-field-control @error('limit') invalid @enderror" value="{{ old('limit') }}">

                                @error('limit')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required"
                                    for="customFile">{{ __('levels.per_user_limit') }}</label>

                                <input type="number" name="user_limit" id="customFile"
                                    class="db-field-control custom-file-input @error('user_limit') invalid @enderror"
                                    value="{{ old('user_limit') }}">

                                @error('user_limit')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required">{{ __('levels.starts_at') }}</label>
                                <input type="datetime-local" onclick="this.showPicker()" name="from_date"
                                    class="db-field-control datepicker @error('from_date') invalid @enderror"
                                    value="{{ old('from_date') }}">

                                @error('from_date')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-col-12 sm:form-col-6 md:form-col-4">
                                <label class="db-field-title required">{{ __('levels.ends_at') }}</label>
                                <input type="datetime-local" onclick="this.showPicker()" name="to_date"
                                    class="db-field-control datepicker @error('to_date') invalid @enderror"
                                    value="{{ old('to_date') }}">

                                @error('to_date')
                                    <small class="db-field-alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="db-btn text-white bg-primary">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>{{ __('levels.save') }}</span>
                                </button>
                            </div>



                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.9/jquery.datetimepicker.full.min.js"
        integrity="sha512-hDFt+089A+EmzZS6n/urree+gmentY36d9flHQ5ChfiRjEJJKFSsl1HqyEOS5qz7jjbMZ0JU4u/x1qe211534g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/coupon/create.js') }}"></script>
@endsection

@push('js')
<script>
(function () {
    function updateAmountField() {
        try {
            var type   = document.querySelector('[name="discount_type"]');
            var label  = document.getElementById('amount_label');
            var suffix = document.getElementById('amount_suffix');
            var hint   = document.getElementById('amount_hint');
            var input  = document.getElementById('amount_field');
            if (!type || !label) return;

            if (type.value == '10') {
                label.textContent  = 'Discount Percentage';
                if (suffix) suffix.textContent = '%';
                if (hint)   hint.textContent   = 'Enter 1–99. E.g. "15" means 15% off the order total.';
                if (input)  input.setAttribute('max', '99');
                if (input)  input.setAttribute('placeholder', 'e.g. 15');
            } else if (type.value == '5') {
                label.textContent  = 'Discount Amount (₹)';
                if (suffix) suffix.textContent = '₹';
                if (hint)   hint.textContent   = 'Flat rupee discount. E.g. "100" means ₹100 off.';
                if (input)  input.removeAttribute('max');
                if (input)  input.setAttribute('placeholder', 'e.g. 100');
            } else {
                label.textContent  = 'Discount Amount';
                if (suffix) suffix.textContent = '';
                if (hint)   hint.textContent   = 'Select a discount type first.';
                if (input)  input.setAttribute('placeholder', 'e.g. 10');
            }
        } catch(e) {}
    }

    function init() {
        var select = document.querySelector('[name="discount_type"]');
        if (select) {
            select.addEventListener('change', updateAmountField);
            updateAmountField();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
</script>
@endpush
