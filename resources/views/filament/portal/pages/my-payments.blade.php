<x-filament-panels::page>
    <div class="space-y-4">
        @forelse($payments as $payment)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between flex-wrap gap-3">

                    {{-- Payment ID --}}
                    <div>
                        <span class="text-xs text-gray-500">Payment ID</span>
                        <p class="font-mono font-bold text-gray-900 dark:text-white">
                            {{ $payment->payment_key }}
                        </p>
                        <span class="text-xs text-gray-400 uppercase">
                            {{ $payment->gateway }}
                        </span>
                    </div>

                    {{-- Amount --}}
                    <div class="text-right">
                        <span class="text-xs text-gray-500">Amount</span>
                        <p class="font-bold text-lg text-gray-900 dark:text-white">
                            {{ $payment->formatted_amount }}
                        </p>
                        @if($payment->amount_refunded > 0)
                            <p class="text-xs text-orange-500">
                                Refunded: {{ $payment->amount_refunded }}
                            </p>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div>
                        @php
                            $colors = [
                                'completed'      => 'bg-green-100 text-green-700',
                                'pending'        => 'bg-yellow-100 text-yellow-700',
                                'processing'     => 'bg-blue-100 text-blue-700',
                                'failed'         => 'bg-red-100 text-red-700',
                                'cancelled'      => 'bg-red-100 text-red-700',
                                'refunded'       => 'bg-orange-100 text-orange-700',
                                'partial_refund' => 'bg-orange-100 text-orange-700',
                            ];
                            $color = $colors[$payment->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
                            {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                        </span>
                    </div>

                    {{-- Date --}}
                    <div class="text-right">
                        <span class="text-xs text-gray-500">
                            {{ $payment->paid_at ? 'Paid At' : 'Created' }}
                        </span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}
                        </p>
                    </div>

                    {{-- Refund Action --}}
                    <div>
                        @if($payment->status === 'completed' && $payment->amount_refunded < $payment->amount && $this->refundsEnabled)
                            @php
                                $existingRequest = $payment->refunds()
                                    ->whereIn('status', ['requested', 'pending', 'approved', 'processing'])
                                    ->first();
                            @endphp

                            @if($existingRequest)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    Refund {{ ucfirst($existingRequest->status) }}
                                </span>
                            @else
                                <x-filament::button
                                    size="sm"
                                    color="danger"
                                    outlined
                                    wire:click="requestRefund({{ $payment->id }})"
                                    wire:confirm="Are you sure you want to request a refund for this payment?"
                                >
                                    Request Refund
                                </x-filament::button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-gray-400">
                <x-filament::icon
                    icon="heroicon-o-credit-card"
                    class="w-12 h-12 mx-auto mb-3 opacity-30"
                />
                <p class="text-lg font-medium">No payments yet</p>
                <p class="text-sm">Your payment history will appear here</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>