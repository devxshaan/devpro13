<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    
    public function createPayment(array $data): array;

    public function verifyPayment(string $paymentId): array;

    public function refund(string $paymentId, float $amount): array;
    
    public function verifyWebhook(string $rawPayload, string $signature): bool;
}