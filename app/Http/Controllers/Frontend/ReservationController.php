<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\ReservationStatus;
use App\Http\Controllers\FrontendController;
use App\Http\Requests\Frontend\ReservationBookRequest;
use App\Http\Requests\ReservationRequest;
use App\Http\Services\PushNotificationService;
use App\Http\Services\ReservationService;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantPaymentSetting;
use App\Models\Table;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class ReservationController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['site_title'] = 'Restaurant Booking';
    }

    /**
     * Step 1 — Show the booking details + personal info form.
     */
    public function booking(ReservationBookRequest $request)
    {
        $restaurant = Restaurant::findOrFail($request->get('restaurant_id'));
        $timeSlot   = TimeSlot::findOrFail($request->get('time_slot'));
        $guest      = (int) $request->get('qtyInput');

        $reservationService = new ReservationService();
        $reservationDate = date('Y-m-d', strtotime($request->get('reservation_date')));
        $available = $reservationService->SlotAvailability($reservationDate, $guest, $restaurant->id);
        if (!isset($available[$timeSlot->id]) || empty($available[$timeSlot->id]['available'])) {
            return redirect()->back()
                ->withErrors(['time_slot' => 'The selected time slot is no longer available. Please choose another.'])
                ->withInput();
        }

        $table = null;
        if ($request->filled('table_id')) {
            $freeTables = $reservationService->CheckReservation(true, $reservationDate, $guest, $restaurant->id, $timeSlot->id);
            if (!isset($freeTables[(int) $request->get('table_id')])) {
                return redirect()->back()
                    ->withErrors(['table_id' => 'The selected table is no longer available. Please choose another.'])
                    ->withInput();
            }
            $table = Table::find($request->get('table_id'));
        }

        $this->data['table'] = $table;

        // Load payment settings to show advance info
        $paymentSetting = RestaurantPaymentSetting::forRestaurant($restaurant->id);
        $advanceAmount  = $paymentSetting->calculateAdvance($guest);

        $this->data['reservationDate']  = $request->get('reservation_date');
        $this->data['guest']            = $guest;
        $this->data['timeSlot']         = $timeSlot;
        $this->data['restaurant']       = $restaurant;
        $this->data['paymentSetting']   = $paymentSetting;
        $this->data['advanceAmount']    = $advanceAmount;

        return view('frontend.restaurant.booking', $this->data);
    }

    /**
     * Step 2 — Save reservation + advance payment details.
     */
    public function store(ReservationRequest $request)
    {
        $restaurant     = Restaurant::findOrFail($request->get('restaurant_id'));
        $paymentSetting = RestaurantPaymentSetting::forRestaurant($restaurant->id);
        $guest          = (int) $request->get('guest');
        $advanceAmount  = $paymentSetting->calculateAdvance($guest);

        // If advance is required, validate payment method + UTR
        if ($advanceAmount > 0) {
            $request->validate([
                'advance_payment_method'   => 'required|string|in:upi,phonepe_qr,paytm_qr',
                'advance_upi_transaction_id' => 'required|string|min:6|max:200',
            ], [
                'advance_payment_method.required'      => 'Please select a payment method for the advance.',
                'advance_upi_transaction_id.required'  => 'Please enter the UPI / UTR transaction reference.',
            ]);
        }

        $reservationService = new ReservationService();
        $table = $reservationService->CheckReservation(
            true,
            date('Y-m-d', strtotime($request->get('reservation_date'))),
            $guest,
            $request->get('restaurant_id'),
            (int) $request->get('time_slot')
        );
        $tableArray = collect($table)->sortBy('capacity')->toArray();
        if (blank($tableArray)) {
            return redirect()->back()
                ->withErrors(['time_slot' => 'The selected time slot just got fully booked. Please choose another.'])
                ->withInput();
        }

        $reservation                   = new Reservation;
        $reservation->first_name       = $request->get('first_name');
        $reservation->last_name        = $request->get('last_name');
        $reservation->email            = $request->get('email');
        $reservation->phone            = $request->get('countrycode') . $request->get('phone');
        $reservation->reservation_date = date('Y-m-d', strtotime($request->get('reservation_date')));
        $reservation->restaurant_id    = $request->get('restaurant_id');
        $pickedTableId = (int) $request->get('table_id');
        $reservation->table_id = ($pickedTableId && isset($tableArray[$pickedTableId]))
            ? $tableArray[$pickedTableId]['tableID']
            : $table[array_key_first($tableArray)]['tableID'];
        $reservation->time_slot_id     = $request->get('time_slot');
        $reservation->guest_number     = $guest;
        $reservation->user_id          = auth()->user()->id;
        $reservation->status           = ReservationStatus::PENDING;

        // Advance payment fields
        if ($advanceAmount > 0) {
            $reservation->advance_amount           = $advanceAmount;
            $reservation->advance_payment_status   = 1; // 1=pending verification
            $reservation->advance_payment_method   = $request->get('advance_payment_method');
            $reservation->advance_upi_transaction_id = $request->get('advance_upi_transaction_id');
        } else {
            $reservation->advance_amount         = 0;
            $reservation->advance_payment_status = 0; // 0=not required
        }

        $reservation->save();

        try {
            app(PushNotificationService::class)->NotificationReservationRestaurant(
                $reservation, $reservation->restaurant->user, 'store'
            );
            app(PushNotificationService::class)->NotificationReservationCustomer(
                $reservation, auth()->user(), 'customer'
            );
        } catch (\Exception $e) {
            // Non-fatal
        }

        $this->data['reservation'] = $reservation;
        return redirect()->route('reservation.confirmation');
    }

    /**
     * AJAX — return available time slots for selected date/guests.
     */
    public function check(Request $request)
    {
        $reservationService = new ReservationService();
        $timeSlots = $reservationService->SlotAvailability(
            date('Y-m-d', strtotime($request->date)),
            $request->capacity,
            $request->restaurant
        );
        return view('frontend.restaurant.timeSlot', compact('timeSlots'));
    }

    /**
     * AJAX — return tables of one slot with free/booked states.
     */
    public function tables(Request $request)
    {
        $reservationService = new ReservationService();
        $items = $reservationService->SlotTables(
            date('Y-m-d', strtotime($request->date)),
            (int) $request->slot,
            $request->capacity,
            $request->restaurant
        );
        return view('frontend.restaurant.tables', compact('items'));
    }

    /**
     * Booking confirmation page.
     */
    public function confirmation()
    {
        $reservation = Reservation::with(['table', 'timeSlot', 'restaurant'])
            ->where(['user_id' => auth()->user()->id])
            ->orderBy('created_at', 'desc')
            ->first();

        $this->data['reservation'] = $reservation;
        return view('frontend.restaurant.booking-confirmation', $this->data);
    }
}
