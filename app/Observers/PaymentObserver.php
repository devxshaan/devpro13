<?php

namespace App\Observers;

use Akaunting\Money\Money;
use App\Events\StatusUpdated;
use App\Models\Payment;
use App\Services\Notify;

// app/Observers/PaymentObserver.php
class PaymentObserver
{
    public function updated(Payment $payment): void
    {

        $amount = Money::{$payment->currency}($payment->amount);
        if ($payment->wasChanged('status')) {
            $messages = [
                'completed'      => "Payment of {$amount->format()} successful! ✅",
                'failed'         => "Payment of {$amount->format()} failed. ❌",
                'refunded'       => "Refund of {$amount->format()} processed successfully. 💰",
                'partial_refund' => "A partial refund of {$amount->format()} has been processed. 💰",
            ];

            $types = [
                'completed'      => 'success',
                'failed'         => 'danger',
                'refunded'       => 'success',
                'partial_refund' => 'warning',
            ];

            $status = $payment->status;
            broadcast(new StatusUpdated($payment));
            if (isset($messages[$status])) {
                Notify::send(
                    $payment->user,
                    $messages[$status],
                    $types[$status],
                    '/portal/payments'
                );
            }
        }
    }
}
