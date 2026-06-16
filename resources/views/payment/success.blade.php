@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center">
        
        {{-- Icon --}}
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
        <p class="text-gray-500 mb-8">Thank you! Your transaction has been completed successfully.</p>

        {{-- Status --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-8">
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Status</span>
                <span class="text-green-600 font-semibold">Completed ✅</span>
            </div>
        </div>

        {{-- Button --}}
        <a href="/portal" class="block w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
            Go to Dashboard
        </a>

    </div>
</div>
@endsection