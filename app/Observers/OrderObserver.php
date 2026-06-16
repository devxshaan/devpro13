<?php

namespace App\Observers;

use App\Events\StatusUpdated;
use App\Models\Order;
use App\Services\Notify;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // 1. Sirf tabhi fire karo jab status change hua ho
        if ($order->wasChanged('status')) {
            
            // 2. Broadcasting (Real-time update)
            // StatusUpdated class mein ShouldBroadcast hai, toh sirf event() fire karne se hi broadcast ho jayega
            event(new StatusUpdated($order));

            // 3. Notification (UI alert)
            $messages = [
                'confirmed'  => "Order #{$order->order_key} has been confirmed! ✅",
                'processing' => "Order #{$order->order_key} is being processed. 🔄",
                'completed'  => "Order #{$order->order_key} has been completed! 🎉",
                'cancelled'  => "Order #{$order->order_key} has been cancelled. ❌",
                'refunded'   => "Order #{$order->order_key} refund has been processed. 💰",
            ];

            $types = [
                'confirmed'  => 'success',
                'processing' => 'info',
                'completed'  => 'success',
                'cancelled'  => 'danger',
                'refunded'   => 'warning',
            ];

            $status = $order->status;

            if (isset($messages[$status])) {
                Notify::send(
                    $order->user,
                    $messages[$status],
                    $types[$status],
                    '/portal/orders'
                );
            }
        }
    }
}