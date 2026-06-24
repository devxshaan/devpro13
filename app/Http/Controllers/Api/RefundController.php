<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Nexbolt\Core\Services\Payment\RefundService;

class RefundController extends Controller
{
    public function request(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            abort(403);
        }

        if (!Setting::get('allow_user_refund_requests', false)) {
            return response()->json(['message' => 'Refund requests are currently disabled.'], 403);
        }

        $existing = $payment->refunds()
            ->whereIn('status', ['requested', 'pending', 'approved', 'processing'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'A refund request is already in progress for this payment.'], 422);
        }

        $remaining = $payment->amount - $payment->amount_refunded;

        $refund = app(RefundService::class)->request(
            payment: $payment,
            amount: $remaining,
            reason: 'Requested by customer via PWA',
            requestedBy: $user
        );

        return response()->json([
            'message' => 'Refund request submitted successfully.',
            'refund'  => [
                'status' => $refund->status,
                'amount' => $refund->amount,
            ],
        ]);
    }

    
}