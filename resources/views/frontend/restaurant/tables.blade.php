@if (!blank($items))
<input type="hidden" id="TableId" name="table_id">
@foreach ($items as $item)
    @if (!empty($item['free']))
        <li class="enable slot-free time-slot table-pill" onclick="selectTable({{ $item['tableID'] }})" id="table_{{ $item['tableID'] }}">
            <input type="radio" class="d-none" id="time-table-{{ $item['tableID'] }}" name="time-table">
            <label for="time-table-{{ $item['tableID'] }}">
                <p class="d-none table-pill-p">{{ $item['tableID'] }}</p>
                {{ $item['name'] }}
                <small>({{ $item['capacity'] }} {{ __('frontend.guests') }})</small>
            </label>
        </li>
    @else
        <li class="disable slot-full time-slot table-pill" id="table_{{ $item['tableID'] }}" title="{{ __('frontend.table_booked') }}">
            <label>
                <p class="d-none table-pill-p">{{ $item['tableID'] }}</p>
                {{ $item['name'] }}
                <small>({{ $item['capacity'] }} {{ __('frontend.guests') }})</small>
            </label>
        </li>
    @endif
@endforeach
@else
<li class="enable time-slot text-capitalize text-start test">
    <label>
        <p class="table-pill-p"> </p>
        {{ __('frontend.slot_not_available') }}
    </label>
</li>
@endif
