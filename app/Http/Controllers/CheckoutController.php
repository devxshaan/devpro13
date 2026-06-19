<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use Nexbolt\Core\Services\CurrencyConverter;
use Nexbolt\Core\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function initiate(Plan $plan)
    {
        $user            = auth()->user();
        $displayCurrency = Setting::get('default_currency', 'USD');
        $converter       = app(CurrencyConverter::class);
        $planCurrency    = $plan->currency ?? $displayCurrency;

        // ✅ Pehle check karo — same user, same plan ka koi incomplete draft already hai?
        $order = Order::where('user_id', $user->id)
            ->where('orderable_type', Plan::class)
            ->where('orderable_id', $plan->id)
            ->whereIn('status', ['draft', 'pending'])
            ->where('created_at', '>=', now()->subHours(24)) // bahut purana na ho, warna stale rate use ho jayega
            ->latest()
            ->first();

        if (!$order) {
            // Naya order banao sirf jab purana exist nahi karta
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
                'ip_address'       => request()->ip() ?? request()->ip(),
                'metadata'         => [
                    'original_price'    => $plan->price,
                    'original_currency' => $planCurrency,
                    'exchange_rate'     => $converter->getRate($planCurrency, $displayCurrency),
                    'converted_at'      => now()->toDateTimeString(),
                ],
            ]);
        }

        $response = app(PaymentService::class)->checkout($user, $order);

        if (!empty($response['checkout_url'])) {
            return redirect($response['checkout_url']);
        }

        return view('checkout.razorpay', compact('response', 'order'));
    }

    public function success(Request $request)
    {
        // ✅ Ab Controller ko pata hi nahi gateway kaun hai, ya woh kis param mein
        // payment ID bhejta hai — PaymentService apne active gateway se khud nikal lega
        $paymentId = app(PaymentService::class)->extractPaymentId($request);

        if (!$paymentId) {
            return redirect()->route('payment.cancel')
                ->with('error', 'Invalid Payment ID.');
        }

        $result = app(PaymentService::class)->verify($paymentId);

        return view('payment.success', compact('result'));
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}