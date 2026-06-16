<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Active Subscription Card --}}
        @if($active)
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="text-indigo-200 text-sm mb-1">Active Plan</p>
                        <h2 class="text-2xl font-bold">{{ $active->plan->name }}</h2>
                        <p class="text-indigo-200 mt-1">
                            {{ $active->formatted_subscription_price }}
                            / {{ $active->plan->billing_cycle }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-semibold">
                            ✅ Active
                        </span>
                        @if($active->ends_at)
                            <p class="text-indigo-200 text-xs mt-2">
                                Expires: {{ $active->ends_at->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- All Subscriptions --}}
        <div class="space-y-4">
            @forelse($subscriptions as $sub)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between flex-wrap gap-3">

                        {{-- Plan --}}
                        <div>
                            <span class="text-xs text-gray-500">Plan</span>
                            <p class="font-bold text-gray-900 dark:text-white">
                                {{ $sub->plan->name }}
                            </p>
                            <span class="text-xs text-gray-400">
                                {{ $sub->subscription_key }}
                            </span>
                        </div>

                        {{-- Price --}}
                        <div>
                            <span class="text-xs text-gray-500">Price</span>
                            <p class="font-bold text-gray-900 dark:text-white">
                                {{ $sub->formatted_subscription_price }}
                            </p>
                        </div>

                        {{-- Status --}}
                        <div>
                            @php
                                $colors = [
                                    'active'    => 'bg-green-100 text-green-700',
                                    'trial'     => 'bg-blue-100 text-blue-700',
                                    'paused'    => 'bg-yellow-100 text-yellow-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'expired'   => 'bg-gray-100 text-gray-700',
                                    'past_due'  => 'bg-orange-100 text-orange-700',
                                ];
                                $color = $colors[$sub->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </div>

                        {{-- Dates --}}
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Period</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $sub->starts_at?->format('d M Y') }}
                                →
                                {{ $sub->ends_at?->format('d M Y') ?? 'Lifetime' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400">
                    <x-filament::icon
                        icon="heroicon-o-credit-card"
                        class="w-12 h-12 mx-auto mb-3 opacity-30"
                    />
                    <p class="text-lg font-medium">No subscriptions yet</p>
                    <p class="text-sm">Your subscriptions will appear here</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>