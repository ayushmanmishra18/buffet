@extends('frontend.layouts.app')

@section('main-content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="booking-confirmation-page text-center">
                    <i class="fa fa-check-circle"></i>
                    <h2 class="mt-3 fw-bold">{{ __('frontend.thanks_for_your_booking') }}</h2>
                    <p>{{ __('frontend.confirmation_email') }}
                        <span class="text-danger">
                        {{ $reservation->email }}
                    </span>
                    </p>
                    @if (!empty($reservation))
                        <ul class="list-unstyled mt-3 text-start d-inline-block">
                            <li>{{ __('frontend.date') }}:
                                <strong>{{ $reservation->reservation_date }}</strong></li>
                            <li>{{ __('frontend.time_slot') }}:
                                <strong>{{ optional($reservation->timeSlot)->start_time ? date('h:i A', strtotime($reservation->timeSlot->start_time)) . ' - ' . date('h:i A', strtotime($reservation->timeSlot->end_time)) : '-' }}</strong>
                            </li>
                            <li>{{ __('levels.table') }}:
                                <strong>{{ optional($reservation->table)->name ?? '-' }}</strong></li>
                            <li>{{ __('frontend.number_of_guests') }}:
                                <strong>{{ $reservation->guest_number }}</strong></li>
                        </ul>
                    @endif
                    <a href="{{ route('account.reservations') }}"
                        class="button form-btn-inline mt-3 d-inline-flex align-items-center justify-content-center">
                        {{ __('frontend.check_your_reservation') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
