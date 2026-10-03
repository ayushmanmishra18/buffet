<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Coupon;
use Livewire\Component;
use App\Models\Discount;
use App\Enums\CouponType;
use App\Models\Restaurant;
use App\Enums\DeliveryType;
use App\Enums\DiscountType;
use App\Enums\DeliveryStatus;
use App\Enums\DiscountStatus;

class OrderCart extends Component
{
    public $carts = [];
    public $totalQty = 0;
    public $totalItem = 0;
    public $totalAmount = 0;
    public $subTotalAmount = 0;
    public $delivery_charge = 0;
    public $totalPayAmount = 0;
    public $restaurant;
    public $totalItemQtyAmount;
    public $totalItemQty;
    public $qty = 1;
    public $coupon = '';
    public $discountAmount = 0;
    public $min_order = 0;
    public $max_order = 0;
    public $msg = '';
    public $couponID = 0;
    public $delivery_type;
    public $free_delivery;
    public bool $isActive = false;
    public bool $isActiveCheckout = true;

    protected $listeners = ['addCart'];

    public function mount()
    {
        $restaurant  = Restaurant::find(session()->get('session_cart_restaurant_id'));
        if (isset($restaurant)) {
            if ($restaurant->pickup_status == DeliveryStatus::ENABLE && $restaurant->delivery_status == DeliveryStatus::DISABLE) {
                $this->delivery_charge = 0;
                $this->delivery_type = true;
            } elseif ($restaurant->pickup_status == DeliveryStatus::DISABLE && $restaurant->delivery_status == DeliveryStatus::DISABLE) {
                $this->isActiveCheckout = false;
            }
        }

        $this->carts = session()->get('cart');
        if (!blank($this->carts)) {
            $this->totalCartAmount();
        }
    }

    public function isUpdating()
    {
        $this->delivery_type = $this->isActive;
        if (!blank($this->carts)) {
            $this->totalCartAmount();
        }
    }

    public function render()
    {
        return view('livewire.order-cart');
    }

    public function addCart($item)
    {
        $status = true;
        if (!blank($this->carts) && count($this->carts['items']) != 0) {
            foreach ($this->carts['items'] as $key => $cart) {
                if ($item['id'] == $this->carts['items'][$key]['id'] && $item['variationID'] == $this->carts['items'][$key]['variationID']) {
                    $this->carts['items'][$key]['qty'] += $item['qty'];
                    $status = false;
                } elseif ($item['id'] == $this->carts['items'][$key]['id']) {
                    $this->carts['items'][$key]['qty'] += $item['qty'];
                    $status = false;
                }
            }
        }
        if ($status) {
            $this->carts['items'][] = $item;
        }

        $this->totalCartAmount();
    }

    public function changeEvent($id)
    {
        if ($this->carts['items'][$id]['qty'] != '' && $this->carts['items'][$id]['qty'] != 0) {
            $this->totalCartAmount();
        }
    }
    public function addItemQty($id)
    {
        $this->carts['items'][$id]['qty']++;
        $this->addCoupon();
        $this->totalCartAmount();
    }
    public function removeItemQty($id)
    {
        if (!blank($this->carts['items'])) {
            $this->carts['items'][$id]['qty']--;
            if ($this->carts['items'][$id]['qty'] == 0) {
                unset($this->carts['items'][$id]);
                $this->carts['items'] = array_values($this->carts['items']);
            }
        }
        $this->totalCartAmount();
    }


    public function totalCartAmount()
    {
        $this->totalItem = 0;
        $this->totalAmount = 0;
        $this->totalQty = 0;
        $this->subTotalAmount = 0;
        if (!blank($this->carts['items'])) {
            foreach ($this->carts['items'] as $key => $cart) {
                $this->totalItem += 1;
                $this->totalQty += $cart['qty'];
                $this->totalAmount += $cart['price'] * $cart['qty'];
                $this->carts['items'][$key]['totalPrice'] =  $cart['price'] * $cart['qty'];
                $this->delivery_charge = setting('basic_delivery_charge');
            }

            if ($this->delivery_type == DeliveryType::PICKUP) {
                $this->delivery_charge = 0;
                $this->delivery_type = true;
            }
            $this->carts['couponID'] = $this->couponID;
            $this->subTotalAmount = $this->totalAmount;
            $this->carts['subTotalAmount'] = $this->totalAmount;
            $this->totalAmount  = $this->totalAmount - $this->discountAmount;
            $this->carts['totalQty'] = $this->totalQty;

            $this->carts['totalPayAmount'] = $this->totalAmount + $this->delivery_charge;
            $this->carts['totalAmount'] = $this->totalAmount;
            $this->carts['delivery_charge'] = $this->delivery_charge;


            $this->carts['min_order'] = $this->min_order;
            $this->carts['max_order'] = $this->max_order;
            $this->carts['coupon_amount'] = $this->discountAmount;
            $this->carts['delivery_type'] = $this->delivery_type;
            $this->carts['free_delivery'] = $this->free_delivery;
            $this->totalPayAmount = $this->totalAmount + $this->delivery_charge;
        } else {
            $this->totalAmount = 0;
            $this->delivery_charge = 0;
            $this->delivery_type = false;
            $this->free_delivery = false;
            $this->min_order = 0;
            $this->max_order = 0;
            $this->totalPayAmount = 0;
            $this->subTotalAmount = 0;
            $this->carts['couponID'] = 0;
            $this->carts['subTotalAmount'] = 0;
            $this->carts['totalQty'] = 0;
            $this->carts['totalPayAmount'] = 0;
            $this->carts['totalAmount'] = 0;
            $this->carts['delivery_charge'] = 0;
            $this->carts['coupon_amount'] = 0;
        }

        $this->dispatch('showCartQty', ['qty' => $this->totalQty]);

        session()->put('cart', $this->carts);
        $this->carts = session()->get('cart');
    }

    public function removeItem($id)
    {
        unset($this->carts['items'][$id]);
        session()->put('cart', $this->carts);
        $this->totalCartAmount();
    }



    public function addCoupon()
    {
        $this->msg = '';
        $this->discountAmount = 0.0;
        $this->couponID = 0;

        if (blank($this->coupon)) {
            $this->totalCartAmount();
            return;
        }

        $now = now();
        $restaurantId = session('session_cart_restaurant_id');
        $totalAmount = (float) data_get($this->carts, 'totalAmount', 0);
        $coupon = Coupon::where('slug', $this->coupon)->first();

        if (!$coupon) {
            $this->msg = 'This Coupon is Invalid';
            $this->totalCartAmount();
            return;
        }

        $totalUsed = Discount::where('coupon_id', $coupon->id)
            ->where('status', DiscountStatus::ACTIVE)
            ->count();

        $userLimit = Discount::where([
            'coupon_id' => $coupon->id,
            'user_id'   => auth()->id() ?? 0,
        ])
            ->where('status', DiscountStatus::ACTIVE)
            ->count();

        if ($coupon->coupon_type == CouponType::VOUCHER && $coupon->restaurant_id != $restaurantId) {
            $this->msg = 'This Coupon is Invalid';
        } elseif ($totalUsed >= (int) $coupon->limit) {
            $this->msg = 'This Coupon is Expired';
        } elseif ($now->lt(Carbon::parse($coupon->from_date)) || $now->gt(Carbon::parse($coupon->to_date))) {
            $this->msg = 'This Coupon is Expired';
        } elseif ($userLimit >= (int) $coupon->user_limit) {
            $this->msg = 'This Coupon is Expired';
        } elseif ($totalAmount < (float) $coupon->minimum_order_amount) {
            $this->msg = 'Minimum Order Amount for This Coupon is ' . currencyFormat($coupon->minimum_order_amount);
        }

        if (!blank($this->msg)) {
            $this->totalCartAmount();
            return;
        }
        $this->couponID = $coupon->id;

        $base = (float) data_get(
            $this->carts,
            'discountableAmount',
            data_get($this->carts, 'subTotal', $totalAmount)
        );
        if ($coupon->discount_type == DiscountType::FIXED) {
            $discount = (float) $coupon->amount;
        } else {
            $percent = (float) $coupon->amount;
            if ($percent > 0 && $percent <= 1) {
                $percent = $percent * 100;
            }

            if (function_exists('bcdiv')) {
                $discount = bcdiv(bcmul((string)$base, (string)$percent, 4), '100', 2);
                $discount = (float) $discount;
            } else {
                $discount = round(($base * $percent) / 100, 2, PHP_ROUND_HALF_UP);
            }

            if (!empty($coupon->max_discount)) {
                $discount = min($discount, (float) $coupon->max_discount);
            }
        }
        $discount = min($discount, $base);

        $this->discountAmount = (float) $discount;
        $this->totalCartAmount();
    }
}
