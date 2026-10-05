$(document).ready(function () {
    'use strict';

    function loadTimeSlots() {
        let date = $('#datePick').val();
        let capacity = $('#qtyInput').val();
        let restaurant = $('#restaurant_id').val();
        if (!date || !restaurant) {
            return;
        }

        $("#showTables").html('');
        $('#TableId').remove();

        $.ajax({
            type: 'POST',
            url: reservationUrl,
            data: {date: date, capacity: capacity, restaurant: restaurant},
            success: function (response) {
                $("#showTimeSlot").html(response);

                $("#showTimeSlot .time-slot").each(function () {
                    let timeSlot = $(this);
                    $(this).find('input').on('change', function () {
                        let timeSlotValID = timeSlot.find('p').text();
                        $('#TimeSlotId').val(timeSlotValID);
                        $("input:radio").removeAttr("checked");
                        $('#time-slot-' + timeSlotValID).prop("checked", true);
                        loadTables();
                    });
                });
            }
        });
    }

    function loadTables() {
        let date = $('#datePick').val();
        let capacity = $('#qtyInput').val();
        let restaurant = $('#restaurant_id').val();
        let slot = $('#TimeSlotId').val();
        if (!date || !restaurant || !slot) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: reservationTablesUrl,
            data: {date: date, capacity: capacity, restaurant: restaurant, slot: slot},
            success: function (response) {
                $("#showTables").html(response);

                $("#showTables .table-pill").each(function () {
                    let pill = $(this);
                    $(this).find('input').on('change', function () {
                        let tableID = pill.find('p').text();
                        $('#TableId').val(tableID);
                        $('#time-table-' + tableID).prop("checked", true);
                    });
                });
            }
        });
    }

    $(document).on('change', '#datePick', function () {
        loadTimeSlots();
    });

    $(document).on("click", '.qminus, .qplus, .plusMinusBtn', function () {
        // let the counter update first, then reload slots
        setTimeout(loadTimeSlots, 50);
    });

    // initial load whenever the booking modal opens
    $(document).on('shown.bs.modal', '#booking-modal', function () {
        loadTimeSlots();
    });

});

 
