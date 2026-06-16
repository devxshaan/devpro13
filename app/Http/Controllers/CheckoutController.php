<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use App\Services\CurrencyConverter;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function initiate(Plan $plan)
    {
        $user            = auth()->user();
        $displayCurrency = Setting::get('default_currency', 'USD');
        $converter       = app(CurrencyConverter::class);
        $planCurrency    = $plan->currency ?? $displayCurrency;

        // Plan price ko display currency mein convert karo
        $convertedPrice = $converter->convert(
            $plan->price,
            $planCurrency,
            $displayCurrency
        );

        $order = Order::create([
            'user_id'          => $user->id,
            'orderable_type'   => Plan::class,
            'orderable_id'     => $plan->id,
            'subtotal'         => $convertedPrice,
            'total'            => $convertedPrice,
            'currency'         => $displayCurrency,
            'status'           => 'draft',
            'fulfillment_type' => 'digital',
            'metadata'         => [
                'original_price'    => $plan->price,
                'original_currency' => $planCurrency,
                'exchange_rate'     => $converter->getRate($planCurrency, $displayCurrency),
                'converted_at'      => now()->toDateTimeString(),
            ],
        ]);

        $response = app(PaymentService::class)->checkout($user, $order);

        if (!empty($response['checkout_url'])) {
            return redirect($response['checkout_url']);
        }

        return view('checkout.razorpay', compact('response', 'order'));
    }

    public function success(Request $request)
    {
        $gateway = Setting::get('active_payment_gateway', 'stripe');

        $paymentId = match($gateway) {
            'stripe'   => $request->query('session_id'),
            'razorpay' => $request->query('razorpay_payment_id'),
            'cashfree' => $request->query('cf_payment_id'),
            default    => $request->query('payment_id'),
        };

        if (!$paymentId) {
            return redirect()->route('payment.cancel')
                ->with('error', 'Invalid Payment ID.');
        }

        $result = app(PaymentService::class)->verify((string) $paymentId);

        return view('payment.success', compact('result'));
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}