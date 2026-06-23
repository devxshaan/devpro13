<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\Request;
use Nexbolt\Core\Services\CurrencyConverter;
use Nexbolt\Core\Services\Payment\PaymentService;

class CheckoutController extends Controller
{
    public function initiate(Request $request, Plan $plan)
    {
        $user            = $request->user();
        $displayCurrency = Setting::get('default_currency', 'USD');
        $converter       = app(CurrencyConverter::class);
        $planCurrency    = $plan->currency ?? $displayCurrency;

        $order = Order::where('user_id', $user->id)
            ->where('orderable_type', Plan::class)
            ->where('orderable_id', $plan->id)
            ->whereIn('status', ['draft', 'pending'])
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->first();

        if (!$order) {
            $convertedPrice = $converter->convert($plan->price, $planCurrency, $displayCurrency);

            $order = Order::create([
                'user_id'          => $user->id,
                'orderable_type'   => Plan::class,
                'orderable_id'     => $plan->id,
                'subtotal'         => $convertedPrice,
                'total'            => $convertedPrice,
                'currency'         => $displayCurrency,
                'status'           => 'draft',
                'fulfillment_type' => 'digital',
                'ip_address'       => $request->ip(),
                'metadata'         => [
                    'original_price'    => $plan->price,
                    'original_currency' => $planCurrency,
                    'exchange_rate'     => $converter->getRate($planCurrency, $displayCurrency),
                    'converted_at'      => now()->toDateTimeString(),
                ],
            ]);
        }

        $response = app(PaymentService::class)->checkout($user, $order);

        return response()->json([
            'checkout_url' => $response['checkout_url'] ?? null,
            'order_key'    => $order->order_key,
            'raw_response' => $response,
        ]);
    }

    public function success(Request $request)
    {
        $paymentId = app(PaymentService::class)->extractPaymentId($request);

        if (!$paymentId) {
            return response()->json(['error' => 'Invalid Payment ID.'], 422);
        }

        $result = app(PaymentService::class)->verify($paymentId);

        return response()->json($result);
    }

    public function cancel(Request $request)
    {
        return response()->json(['message' => 'Payment was cancelled.']);
    }
}