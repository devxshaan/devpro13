<?php

namespace App\Services\Payment\Gateways;

use App\Models\Setting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Razorpay\Api\Api;

class RazorpayGateway implements PaymentGatewayInterface
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(
            Setting::get('razorpay_key_id'),
            Setting::get('razorpay_key_secret')
        );
    }

    public function createPayment(array $data): array
    {
        $order = $this->api->order->create([
            'amount'          => (int)($data['amount'] * 100), // Paise
            'currency'        => $data['currency'],
            'receipt'         => $data['order_key'],
            'notes'           => [
                'user_id'    => $data['user_id'],
                'order_key'  => $data['order_key'],
                'user_email' => $data['user_email'],
            ],
        ]);

        return [
            'gateway'          => 'razorpay',
            'gateway_order_id' => $order->id,
            'amount'           => $data['amount'],
            'currency'         => $data['currency'],
            'key_id'           => Setting::get('razorpay_key_id'),
            'user_name'        => $data['user_name'],
            'user_email'       => $data['user_email'],
            'description'      => $data['description'],
            'checkout_url'     => null, // Razorpay frontend JS se handle hota hai
        ];
    }

    public function verifyPayment(string $paymentId): array
    {
        $payment = $this->api->payment->fetch($paymentId);

        return [
            'status'             => in_array($payment->status, ['captured', 'authorized'])
                                        ? 'completed'
                                        : 'failed',
            'gateway_payment_id' => $payment->id,
            'amount'             => $payment->amount / 100,
            'currency'           => strtoupper($payment->currency),
            'gateway'            => 'razorpay',
        ];
    }

    public function refund(string $paymentId, float $amount): array
    {
        $refund = $this->api->payment->fetch($paymentId)->refund([
            'amount' => (int)($amount * 100),
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
            $this->api->utility->verifyWebhookSignature(
                $rawPayload,
                $signature,
                Setting::get('razorpay_webhook_secret')
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}