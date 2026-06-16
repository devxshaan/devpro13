<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;

class CashfreeGateway implements PaymentGatewayInterface
{
    public function createPayment(array $data): array
    {
        throw new \Exception('Cashfree gateway not configured yet.');
    }

    public function verifyPayment(string $paymentId): array
    {
        throw new \Exception('Cashfree gateway not configured yet.');
    }

    public function refund(string $paymentId, float $amount): array
    {
        throw new \Exception('Cashfree gateway not configured yet.');
    }

    public function verifyWebhook(string $rawPayload, string $signature): bool
    {
        throw new \Exception('Cashfree gateway not configured yet.');
    }
}