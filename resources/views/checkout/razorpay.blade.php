@extends('layouts.app') {{-- Tumhari main layout file ka naam --}}

@section('content')
<div class="h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-blue-500 mx-auto mb-4"></div>
        <h2 class="text-xl font-semibold text-gray-700">Redirecting to Payment...</h2>
        <p class="text-gray-500">Please do not refresh this page.</p>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ $response['key_id'] }}",
        "amount": "{{ $response['amount'] * 100 }}",
        "currency": "{{ $response['currency'] }}",
        "name": "{{ config('app.name') }}",
        "order_id": "{{ $response['gateway_order_id'] }}",
        "handler": function (response) {
            // ✅ Seedha redirect — POST nahi
            window.location.href = '/payment/success?razorpay_payment_id=' 
                + response.razorpay_payment_id;
        },
        "modal": {
            "ondismiss": function() {
                window.location.href = '/payment/cancel';
            }
        }
    };

    var rzp1 = new Razorpay(options);
    rzp1.open();
</script>
@endsection