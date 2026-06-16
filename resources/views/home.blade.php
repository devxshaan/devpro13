@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] text-center px-4">
    <span class="px-4 py-1.5 mb-6 text-sm font-semibold text-indigo-700 bg-indigo-100 rounded-full">
        v1.0 is Live ⚡
    </span>

    <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-6">
        Build your <span class="text-indigo-600">next big idea</span><br>
        at lightning speed.
    </h1>

    <p class="text-xl text-gray-600 max-w-2xl mb-10">
        Bolt is a production-ready boilerplate built with Laravel 13, Filament v5, and Livewire v4. 
        Designed for SaaS, E-Commerce, and everything in between.
    </p>

    <div class="flex gap-4">
        <a href="/portal/register" class="px-8 py-4 text-lg font-bold text-white bg-indigo-600 rounded-xl shadow-lg hover:bg-indigo-700 transition transform hover:-translate-y-1">
            Get Started
        </a>
        <a href="#" class="px-8 py-4 text-lg font-bold text-indigo-600 bg-white border-2 border-indigo-100 rounded-xl hover:bg-indigo-50 transition">
            View Documentation
        </a>
    </div>
    
</div>

@foreach(\App\Models\Plan::where('is_active', true)->get() as $plan)
    <div class="p-6 border rounded-lg">
        <h3>{{ $plan->name }}</h3>
        
        <p>{{ $plan->formatted_price }} / {{ $plan->billing_cycle }}</p>

        @auth
            <form method="POST" action="{{ route('checkout', $plan) }}">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Subscribe Now</button>
            </form>
        @else
            <a href="{{ route('filament.portal.auth.login') }}" class="text-indigo-600 font-bold">
                Login to Subscribe
            </a>
        @endauth
    </div>
@endforeach
@endsection