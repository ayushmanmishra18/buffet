<style>
    #showTimeSlot li.slot-free { border-color: #16a34a !important; color: #16a34a; }
    #showTimeSlot li.slot-full { border-color: #dc2626 !important; color: #dc2626; cursor: not-allowed; }
</style>
@if (!blank($timeSlots))
<input type="hidden" id="TimeSlotId" name="time_slot">
@foreach ($timeSlots as $timeSlot)
    @if (!empty($timeSlot['available']))
        <li class="enable slot-free time-slot" onclick="selected({{ $timeSlot['id'] }})" id="slot_{{ $timeSlot['id'] }}">
            <input type="radio" class="d-none" id="time-slot-{{ $timeSlot['id'] }}" name="time-sloat">
            <label for="time-slot-{{ $timeSlot['id'] }}">
                <p class="d-none time-slot-p">{{ $timeSlot['id'] }}</p>
                {{ date('h:i A', strtotime($timeSlot['start_time'])) }} -
                {{ date('h:i A', strtotime($timeSlot['end_time'])) }}
            </label>
        </li>
    @else
        <li class="disable slot-full time-slot" id="slot_{{ $timeSlot['id'] }}" title="{{ $timeSlot['reason'] === 'booked' ? __('frontend.slot_full') : __('frontend.slot_not_available') }}">
            <label>
                <p class="d-none time-slot-p">{{ $timeSlot['id'] }}</p>
                {{ date('h:i A', strtotime($timeSlot['start_time'])) }} -
                {{ date('h:i A', strtotime($timeSlot['end_time'])) }}
            </label>
        </li>
    @endif
@endforeach
@else
<li class="enable time-slot text-capitalize text-start test">
    <label for="time-slot-0">
        <p class="time-slot-p"> </p>
        {{ __('frontend.slot_not_available') }}
        <span>{{ __('frontend.select_another_date') }}</span>
    </label>
</li>
@endif
