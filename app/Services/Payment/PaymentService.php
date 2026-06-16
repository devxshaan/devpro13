<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Notify;

class PaymentService
{
    private \App\Services\Payment\Contracts\PaymentGatewayInterface $gateway;
    private string $activeGateway;

    public function __construct()
    {
        $this->activeGateway = Setting::get('active_payment_gateway', 'stripe');
        $this->gateway       = GatewayManager::resolve();
    }

    // ── Checkout ──────────────────────────────────────────
    public function checkout(User $user, Order $order): array
    {
        $currency = $order->currency
            ?? Setting::get('default_currency', 'USD');

        $data = [
            'amount'      => $order->total,
            'currency'    => $currency,
            'order_key'   => $order->order_key,
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'user_name'   => $user->name,
            'description' => "Payment for Order #{$order->order_key}",
            'success_url' => route('payment.success'),
            'cancel_url'  => route('payment.cancel'),
        ];

        // Gateway se response lo
        $response = $this->gateway->createPayment($data);

        // Payment record create karo
        Payment::create([
            'user_id'          => $user->id,
            'gateway'          => $this->activeGateway,
            'gateway_order_id' => $response['gateway_order_id'] ?? null,
            'amount'           => $order->total,
            'currency'         => $currency,
            'status'           => 'pending',
            'ip_address'       => request()->ip(),
            'metadata'         => $response,
        ]);

        // Order pending mein update karo
        $order->update(['status' => 'pending']);

        return $response;
    }

    public function verify(string $paymentId): array
    {
        $result = $this->gateway->verifyPayment($paymentId);

        $result['gateway'] = $this->activeGateway;

        // ✅ Har possible column se dhundho
        $payment = Payment::where('gateway_payment_id', $paymentId)
                        ->orWhere('gateway_order_id', $paymentId)
                        ->orWhereJsonContains('metadata->gateway_order_id', $paymentId)
                        ->latest()
                        ->first();

        if (!$payment) {
            // Razorpay case — payment_id se order_id nikalo
            $payment = Payment::where('gateway', $this->activeGateway)
                            ->where('status', 'pending')
                            ->where('user_id', auth()->id())
                            ->latest()
                            ->first();
        }

        if ($payment) {
            $payment->update([
                'status'             => $result['status'],
                'gateway_payment_id' => $paymentId,
                'gateway_response'   => $result,
                'paid_at'            => $result['status'] === 'completed' ? now() : null,
            ]);

            // Order update karo
            $order = Order::where('user_id', $payment->user_id)
                        ->whereIn('status', ['draft', 'pending'])
                        ->latest()
                        ->first();

            if ($order && $result['status'] === 'completed') {
                $order->update([
                    'status'       => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Subscription create karo
                $this->createSubscription($payment, $order);

                // Notify
                Notify::send(
                    $payment->user,
                    "Payment of {$payment->amount} {$payment->currency} successful! ✅",
                    'success',
                    '/portal/dashboard'
                );
            }
        }

        return array_merge($result, ['payment' => $payment]);
    }

    // ── Subscription Auto Create ──────────────────────────────
    private function createSubscription(Payment $payment, ?Order $order): void
    {
        if (!$order) return;
        if ($order->orderable_type !== Plan::class) return;

        $plan = Plan::find($order->orderable_id);
        if (!$plan) return;

        // Already active subscription hai?
        $existing = Subscription::where('user_id', $payment->user_id)
                                ->where('plan_id', $plan->id)
                                ->where('status', 'active')
                                ->first();

        if ($existing) return;

        // Trial days check
        $trialEndsAt = $plan->trial_days > 0
            ? now()->addDays($plan->trial_days)
            : null;

        // Ends at — billing cycle se
        $endsAt = match($plan->billing_cycle) {
            'daily'    => now()->addDay(),
            'weekly'   => now()->addWeek(),
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            'one_time' => null,
            'per_use'  => now()->addDay(),
            default    => now()->addMonth(),
        };

        Subscription::create([
            'user_id'               => $payment->user_id,
            'plan_id'               => $plan->id,
            'price_at_subscription' => $plan->price,
            'currency'              => $payment->currency,
            'status'                => $trialEndsAt ? 'trial' : 'active',
            'trial_ends_at'         => $trialEndsAt,
            'starts_at'             => now(),
            'ends_at'               => $endsAt,
            'gateway'               => $payment->gateway,
        ]);
    }

    // ── Refund ────────────────────────────────────────────
    public function refund(Payment $payment, float $amount, string $reason = ''): array
    {
        // Amount check karo
        $refundable = $payment->amount - $payment->amount_refunded;

        if ($amount > $refundable) {
            throw new \Exception("Refund amount exceeds refundable amount: {$refundable}");
        }

        // Gateway se refund karo
        $response = $this->gateway->refund(
            $payment->gateway_payment_id,
            $amount
        );

        // Refund record banao
        Refund::create([
            'user_id'           => $payment->user_id,
            'payment_id'        => $payment->id,
            'order_id'          => $payment->payable_type === Order::class
                                    ? $payment->payable_id
                                    : null,
            'amount'            => $amount,
            'currency'          => $payment->currency,
            'reason'            => $reason,
            'gateway'           => $this->activeGateway,
            'gateway_refund_id' => $response['gateway_refund_id'] ?? null,
            'gateway_response'  => $response,
            'status'            => $response['status'] === 'succeeded' ? 'completed' : 'processing',
            'processed_by'      => auth()->id(),
            'processed_at'      => now(),
        ]);

        // Payment update karo
        $newRefunded = $payment->amount_refunded + $amount;
        $payment->update([
            'amount_refunded' => $newRefunded,
            'status'          => $newRefunded >= $payment->amount
                                    ? 'refunded'
                                    : 'partial_refund',
            'refunded_at'     => now(),
        ]);

        // User ko notify karo
        Notify::send(
            $payment->user,
            "Refund of {$amount} {$payment->currency} has been processed.",
            'success',
            '/portal/payments'
        );

        return $response;
    }

    // ── Webhook Handle ────────────────────────────────────
    public function handleWebhook(string $rawPayload, string $signature): void
    {
        $isValid = $this->gateway->verifyWebhook($rawPayload, $signature);

        if (!$isValid) {
            throw new \Exception('Invalid webhook signature.');
        }

        $payload = json_decode($rawPayload, true);
        $event   = $payload['type']                         // Stripe
            ?? $payload['event']                            // Razorpay
            ?? null;

        $status = $this->resolveStatus($event);

        if ($status) {
            $this->updatePaymentStatus($payload, $status);
        }
    }

    // ── Status Map — Gateway Agnostic ─────────────────────
    private function resolveStatus(?string $event): ?string
    {
        $map = [
            // Stripe
            'payment_intent.succeeded'      => 'completed',
            'payment_intent.payment_failed' => 'failed',
            'charge.refunded'               => 'refunded',

            // Razorpay
            'payment.captured'              => 'completed',
            'payment.failed'                => 'failed',
            'refund.created'                => 'refunded',

            // Cashfree (future)
            'PAYMENT_SUCCESS'               => 'completed',
            'PAYMENT_FAILED'                => 'failed',
            'REFUND_SUCCESS'                => 'refunded',
        ];

        return $map[$event] ?? null;
    }

    // ── Payment Status Update ─────────────────────────────
    private function updatePaymentStatus(array $payload, string $status): void
    {
        // Gateway payment ID nikalo
        $gatewayPaymentId =
            $payload['data']['object']['id']                        // Stripe
            ?? $payload['payload']['payment']['entity']['id']       // Razorpay
            ?? null;

        if (!$gatewayPaymentId) return;

        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)
                         ->orWhere('gateway_order_id', $gatewayPaymentId)
                         ->first();

        if (!$payment) return;

        $payment->update([
            'status'             => $status,
            'gateway_payment_id' => $gatewayPaymentId,
            'gateway_response'   => $payload,
            'paid_at'            => $status === 'completed' ? now() : null,
            'refunded_at'        => $status === 'refunded'  ? now() : null,
            'failed_at'          => $status === 'failed'    ? now() : null,
        ]);

        // Order update karo
        if ($payment->payable_type === Order::class) {
            $payment->payable?->update([
                'status' => match($status) {
                    'completed' => 'confirmed',
                    'failed'    => 'failed',
                    'refunded'  => 'refunded',
                    default     => 'pending',
                }
            ]);
        }

        // User notify karo
        match($status) {
            'completed' => Notify::send(
                $payment->user,
                "Payment of {$payment->amount} {$payment->currency} successful! ✅",
                'success',
                '/portal/payments'
            ),
            'failed' => Notify::send(
                $payment->user,
                "Payment of {$payment->amount} {$payment->currency} failed. ❌",
                'danger',
                '/portal/payments'
            ),
            'refunded' => Notify::send(
                $payment->user,
                "Refund of {$payment->amount} {$payment->currency} processed. 💰",
                'success',
                '/portal/payments'
            ),
            default => null,
        };
    }
}