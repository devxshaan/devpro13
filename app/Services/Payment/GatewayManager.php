<?php

namespace App\Services\Payment;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\CashfreeGateway;
use App\Services\Payment\Gateways\RazorpayGateway;
use App\Services\Payment\Gateways\StripeGateway;

class GatewayManager
{
    public static function resolve(): PaymentGatewayInterface
    {
        $gateway = Setting::get('active_payment_gateway', 'stripe');

        return match($gateway) {
            'stripe'   => new StripeGateway(),
            'razorpay' => new RazorpayGateway(),
            'cashfree' => new CashfreeGateway(),
            default    => throw new \Exception("Payment gateway '{$gateway}' not supported."),
        };
    }
}