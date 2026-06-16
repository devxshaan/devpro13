<x-filament-panels::page>
    <div class="space-y-6">
        @forelse($orders as $order)
            {{-- Modern Card Design --}}
            <div class="group relative overflow-hidden bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 p-6 transition-all duration-300 hover:shadow-lg hover:border-primary-500/30">
                
                {{-- Subtle Glow Effect on Hover --}}
                <div class="absolute inset-0 bg-gradient-to-r from-primary-500/0 via-primary-500/5 to-primary-500/0 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                <div class="relative flex items-center justify-between flex-wrap gap-6">

                    {{-- Left Side: Info --}}
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700/50 rounded-xl">
                            <x-filament::icon icon="heroicon-o-shopping-bag" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->order_key }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">
                                {{ class_basename($order->orderable_type ?? '') }}
                            </p>
                        </div>
                    </div>

                    {{-- Right Side: Details Grid --}}
                    <div class="flex items-center gap-8">
                        {{-- Amount --}}
                        <div>
                            <span class="block text-[10px] text-gray-400 uppercase tracking-widest mb-1">Amount</span>
                            <p class="font-bold text-gray-900 dark:text-white text-lg">
                                {{ $order->formatted_total }}
                            </p>
                        </div>

                        {{-- Status (Modern Pills) --}}
                        <div>
                            @php
                                $statusStyles = [
                                    'confirmed'  => 'ring-green-500/20 bg-green-500/10 text-green-500',
                                    'completed'  => 'ring-emerald-500/20 bg-emerald-500/10 text-emerald-500',
                                    'pending'    => 'ring-amber-500/20 bg-amber-500/10 text-amber-500',
                                    'processing' => 'ring-blue-500/20 bg-blue-500/10 text-blue-500',
                                    'cancelled'  => 'ring-red-500/20 bg-red-500/10 text-red-500',
                                ];
                                $style = $statusStyles[$order->status] ?? 'ring-gray-500/20 bg-gray-500/10 text-gray-500';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ring-1 {{ $style }}">
                                {{ $order->status }}
                            </span>
                        </div>

                        {{-- Date --}}
                        <div class="text-right">
                            <span class="block text-[10px] text-gray-400 uppercase tracking-widest mb-1">Date</span>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ $order->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-24 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-filament::icon icon="heroicon-o-shopping-bag" class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-lg font-bold text-gray-700 dark:text-gray-200">No orders yet</p>
                <p class="text-sm text-gray-500">Your recent purchases will appear here.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>