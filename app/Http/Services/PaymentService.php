<?php

namespace App\Http\Services;

use App\Enums\OrderTypeStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Restaurant;

class PaymentService
{
    public $data = [];

    /**
     * Build order data from session and call OrderService.
     * $paymentSuccess is kept for signature compatibility but is always true
     * for India-only methods (we trust the UTR / offline cash).
     */
    public function payment(bool $paymentSuccess = true): object
    {
        $restaurant = Restaurant::find(session('session_cart_restaurant_id'));
        $request    = session()->get('checkoutRequest');
        $cart       = session()->get('cart');

        // ── Delivery type & charge ────────────────────────────────────
        if (
            ($cart && isset($cart['delivery_type']) && $cart['delivery_type'] == true)
            || ($cart && isset($cart['free_delivery']) && $cart['free_delivery'])
        ) {
            $deliveryCharge = 0;
            $orderType      = OrderTypeStatus::PICKUP;
        } else {
            $deliveryCharge = session()->get('delivery_charge', 0);
            $orderType      = OrderTypeStatus::DELIVERY;
        }

        // ── Line items ────────────────────────────────────────────────
        $items     = [];
        $cartItems = $cart['items'] ?? [];

        foreach ($cartItems as $cartItem) {
            $items[] = [
                'restaurant_id'          => $restaurant->id,
                'menu_item_variation_id'  => $cartItem['variation']['id'] ?? null,
                'menu_item_id'            => $cartItem['menuItem_id'],
                'unit_price'              => (float) $cartItem['price'],
                'quantity'                => (int) $cartItem['qty'],
                'discounted_price'        => (float) $cartItem['discount'],
                'variation'               => $cartItem['variation'] ?? null,
                'options'                 => $cartItem['options'] ?? null,
                'instructions'            => $cartItem['instructions'] ?? null,
            ];
        }

        // ── Payment status based on India method ─────────────────────
        $paymentType = (int) ($request['payment_type'] ?? PaymentMethod::CASH_ON_DELIVERY);

        switch ($paymentType) {
            case PaymentMethod::WALLET:
                // Deducted from internal wallet — mark as paid immediately
                $this->data['payment_method'] = PaymentMethod::WALLET;
                $this->data['payment_status'] = PaymentStatus::PAID;
                $this->data['paid_amount']    = (float) $cart['totalAmount'] + $deliveryCharge;
                break;

            case PaymentMethod::UPI:
            case PaymentMethod::PHONEPE_QR:
            case PaymentMethod::PAYTM_QR:
                // Customer has paid via UPI/QR and submitted UTR — mark as paid
                // Admin can verify UTR reference in order details if needed
                $this->data['payment_method'] = $paymentType;
                $this->data['payment_status'] = PaymentStatus::PAID;
                $this->data['paid_amount']    = (float) $cart['totalAmount'] + $deliveryCharge;
                break;

            case PaymentMethod::CASH_ON_DELIVERY:
            default:
                // Cash paid on delivery / at counter
                $this->data['payment_method'] = PaymentMethod::CASH_ON_DELIVERY;
                $this->data['payment_status'] = PaymentStatus::UNPAID;
                $this->data['paid_amount']    = 0;
                break;
        }

        // ── Assemble full order data ──────────────────────────────────
        $this->data['coupon_id']      = $cart['couponID']     ?? null;
        $this->data['coupon_amount']  = $cart['coupon_amount'] ?? null;
        $this->data['items']          = $items;
        $this->data['order_type']     = $orderType;
        $this->data['restaurant_id']  = session('session_cart_restaurant_id');
        $this->data['user_id']        = auth()->user()->id;
        $this->data['total']          = (float) ($cart['totalAmount'] ?? 0);
        $this->data['delivery_charge'] = $deliveryCharge;
        $this->data['address']        = $request['address'] ?? '';
        $this->data['mobile']         = ($request['countrycode'] ?? '') . ($request['mobile'] ?? '');

        // Store UPI transaction reference in order remarks for admin visibility
        if (!blank($request['upi_transaction_id'] ?? null)) {
            $this->data['remarks'] = 'UPI Ref: ' . $request['upi_transaction_id'];
        }

        return app(OrderService::class)->order($this->data);
    }
}
