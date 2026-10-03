<?php

namespace App\Enums;

interface PaymentMethod
{
    // ── India-only payment methods ──────────────────────────────────────
    const CASH_ON_DELIVERY = 5;   // Pay cash on delivery / at counter
    const WALLET           = 20;  // Platform credit balance
    const UPI              = 40;  // Customer pays via any UPI app to restaurant's UPI ID
    const PHONEPE_QR       = 41;  // Customer scans restaurant's PhonePe QR code
    const PAYTM_QR         = 42;  // Customer scans restaurant's Paytm QR code

    // ── Legacy — kept for backward-compatibility with existing order records ──
    const PAYTM            = 32;
    const PHONEPE          = 33;
}
