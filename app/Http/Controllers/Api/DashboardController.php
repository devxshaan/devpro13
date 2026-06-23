<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function orders(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Order $order) => [
                'order_key' => $order->order_key,
                'status'    => $order->status,
                'total'     => $order->total,
                'currency'  => $order->currency,
                'created_at' => $order->created_at,
            ]);

        return response()->json($orders);
    }

    public function payments(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Payment $payment) => [
                'payment_key'     => $payment->payment_key,
                'amount'          => $payment->amount,
                'amount_refunded' => $payment->amount_refunded,
                'currency'        => $payment->currency,
                'status'          => $payment->status,
                'gateway'         => $payment->gateway,
                'paid_at'         => $payment->paid_at,
            ]);

        return response()->json($payments);
    }

    public function subscriptions(Request $request)
    {
        $subscriptions = Subscription::where('user_id', $request->user()->id)
            ->with('plan')
            ->latest()
            ->get()
            ->map(fn (Subscription $sub) => [
                'subscription_key' => $sub->subscription_key,
                'plan_name'         => $sub->plan?->name,
                'status'            => $sub->status,
                'price'             => $sub->price_at_subscription,
                'currency'          => $sub->currency,
                'starts_at'         => $sub->starts_at,
                'ends_at'           => $sub->ends_at,
            ]);

        return response()->json($subscriptions);
    }

    public function activeSubscription(Request $request)
    {
        $active = Subscription::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        if (!$active) {
            return response()->json(null);
        }

        return response()->json([
            'plan_name' => $active->plan?->name,
            'status'    => $active->status,
            'ends_at'   => $active->ends_at,
        ]);
    }
}