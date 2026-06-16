@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center">

        {{-- Icon --}}
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
        <p class="text-gray-500 mb-8">
            Your payment was not completed. No charges have been made.
        </p>

        @if(session('error'))
            <div class="bg-red-50 text-red-600 rounded-xl p-4 mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Buttons --}}
        <div class="space-y-3">
            <a href="/" class="block w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
                Try Again
            </a>
            <a href="/portal" class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">
                Go to Dashboard
            </a>
        </div>

    </div>
</div>
@endsection