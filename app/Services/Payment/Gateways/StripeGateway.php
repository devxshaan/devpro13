<?php

namespace App\Services\Payment\Gateways;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(Setting::get('stripe_secret_key'));
    }

    public function createPayment(array $data): array
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => strtolower($data['currency']),
                    'unit_amount'  => (int)($data['amount'] * 100), // Cents
                    'product_data' => [
                        'name' => $data['description'],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'           => 'payment',
            'success_url'    => $data['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => $data['cancel_url'],
            'customer_email' => $data['user_email'],
            'metadata'       => [
                'order_key'  => $data['order_key'],
                'user_id'    => $data['user_id'],
            ],
        ]);

        return [
            'gateway'          => 'stripe',
            'gateway_order_id' => $session->id,
            'checkout_url'     => $session->url, // Yahan redirect karo
        ];
    }

    public function verifyPayment(string $paymentId): array
    {
        $session = Session::retrieve($paymentId);

        return [
            'status'             => $session->payment_status === 'paid' ? 'completed' : 'pending',
            'gateway_payment_id' => $session->payment_intent,
            'amount'             => $session->amount_total / 100,
            'currency'           => strtoupper($session->currency),
        ];
    }

    public function refund(string $paymentId, float $amount): array
    {
        $refund = Refund::create([
            'payment_intent' => $paymentId,
            'amount'         => (int)($amount * 100),
        ]);

        return [
            'gateway_refund_id' => $refund->id,
            'status'            => $refund->status,
            'amount'            => $amount,
        ];
    }

    public function verifyWebhook(string $rawPayload, string $signature): bool
    {
        try {
            Webhook::constructEvent(
                $rawPayload,
                $signature,
                Setting::get('stripe_webhook_secret')
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}