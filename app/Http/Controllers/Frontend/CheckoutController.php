<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\PaymentMethod;
use App\Http\Controllers\FrontendController;
use App\Http\Services\PaymentService;
use App\Http\Services\PushNotificationService;
use App\Models\Address;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantPaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['site_title'] = 'Checkout';
    }

    // ── Show checkout page ────────────────────────────────────────────
    public function index()
    {
        if (blank(session()->get('cart'))) {
            return redirect('/');
        }

        $restaurant = Restaurant::find(session('session_cart_restaurant_id'));

        $this->data['addresses'] = Address::where('user_id', auth()->user()->id)->get();
        $this->data['lastAddress'] = '';

        $lastAddress = Order::select('address')->where('user_id', auth()->user()->id)->latest()->first();
        if (!blank($lastAddress)) {
            if (isJson($lastAddress->address)) {
                $this->data['lastAddress'] = Address::where(
                    'address', json_decode($lastAddress->address, true)['address']
                )->first();
            }
        }
        if (blank($this->data['lastAddress'])) {
            $this->data['lastAddress'] = Address::where('user_id', auth()->user()->id)->first();
        }

        $this->data['menuitems']    = session()->get('cart');
        $this->data['totalPayment'] = session()->get('cart')['totalPayAmount'];
        $this->data['restaurant']   = $restaurant;

        // Load restaurant's India payment settings
        $this->data['paymentSettings'] = $restaurant
            ? RestaurantPaymentSetting::forRestaurant($restaurant->id)
            : null;

        return view('frontend.restaurant.checkout', $this->data);
    }

    // ── Process order ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $sessionRestaurantId = session('session_cart_restaurant_id');
        if (blank($sessionRestaurantId)) {
            return redirect(route('checkout.index'))->withError('Restaurant not found.');
        }

        $this->setDeliveryCharge($request);
        $restaurant = Restaurant::find($sessionRestaurantId);

        $validation = [
            'mobile'       => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'payment_type' => 'required|numeric',
        ];
        if (!$request->delivery_type) {
            $validation['address'] = 'required|string';
        }

        $messages = ['mobile.required' => 'The phone number field is required.'];

        $validator = Validator::make($request->all(), $validation, $messages);

        $validator->after(function ($v) use ($request) {
            // Wallet: check sufficient balance
            if ((int) $request->payment_type === PaymentMethod::WALLET) {
                $needed = (float)(session()->get('cart')['totalAmount'] + session()->get('delivery_charge'));
                if ((float) auth()->user()->balance->balance < $needed) {
                    $v->errors()->add('payment_type', 'Insufficient credit balance for this payment.');
                }
            }

            // UPI payments: transaction ID is mandatory
            if (in_array((int) $request->payment_type, [PaymentMethod::UPI, PaymentMethod::PHONEPE_QR, PaymentMethod::PAYTM_QR])) {
                if (blank($request->upi_transaction_id)) {
                    $v->errors()->add('upi_transaction_id', 'Please enter the UPI / UTR transaction reference number.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect(route('checkout.index'))
                ->withErrors($validator)
                ->withInput();
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        session()->put('checkoutRequest', $request->all());

        // All India methods go through the default payment flow
        return $this->processDefaultPayment($request);
    }

    // ── Default payment processor (COD / Wallet / UPI / QR) ──────────
    protected function processDefaultPayment(Request $request)
    {
        $orderService = app(PaymentService::class)->payment(true);
        return $this->handleOrderServiceResponse($orderService);
    }

    protected function handleOrderServiceResponse($orderService)
    {
        if ($orderService->status) {
            $order = Order::find($orderService->order_id);
            $this->clearSessionData();
            $this->sendOrderNotifications($order);
            return redirect(route('account.order.show', $order->id))
                ->withSuccess('Your order has been placed successfully!');
        }
        return redirect(route('checkout.index'))
            ->withError($orderService->message ?? 'Something went wrong. Please try again.');
    }

    protected function clearSessionData()
    {
        session()->forget([
            'cart',
            'checkoutRequest',
            'session_cart_restaurant_id',
            'session_cart_restaurant',
            'delivery_charge',
        ]);
    }

    protected function sendOrderNotifications($order)
    {
        if ($order) {
            try {
                app(PushNotificationService::class)->sendOrderNotificationToRestaurant($order);
                app(PushNotificationService::class)->sendOrderNotificationToCustomer($order);
            } catch (\Exception $e) {
                // Non-fatal — log silently
                \Illuminate\Support\Facades\Log::info('Push notification failed: ' . $e->getMessage());
            }
        }
    }

    protected function setDeliveryCharge($request)
    {
        session()->put('delivery_charge', $request->total_delivery_charge ?: 0);
    }
}
