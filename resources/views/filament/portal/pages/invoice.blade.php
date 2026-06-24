<x-filament-panels::page>
    <div class="space-y-4">
        @forelse($invoices as $invoice)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between flex-wrap gap-3">

                    {{-- Invoice Number --}}
                    <div>
                        <span class="text-xs text-gray-500">Invoice</span>
                        <p class="font-mono font-bold text-gray-900 dark:text-white">
                            {{ $invoice->invoice_number }}
                        </p>
                        <span class="text-xs text-gray-400">
                            {{ $invoice->item_description }}
                        </span>
                    </div>

                    {{-- Amount --}}
                    <div class="text-right">
                        <span class="text-xs text-gray-500">Amount</span>
                        <p class="font-bold text-lg text-gray-900 dark:text-white">
                            {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                        </p>
                    </div>

                    {{-- Status --}}
                    <div>
                        @php
                            $colors = [
                                'draft'  => 'bg-gray-100 text-gray-700',
                                'issued' => 'bg-green-100 text-green-700',
                                'void'   => 'bg-red-100 text-red-700',
                            ];
                            $color = $colors[$invoice->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>

                    {{-- Date --}}
                    <div class="text-right">
                        <span class="text-xs text-gray-500">Issued On</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $invoice->issued_at?->format('d M Y') ?? '—' }}
                        </p>
                    </div>

                    {{-- View / Download --}}
                    <div class="flex gap-2">
                        @if($invoice->pdf_path)
                            
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($invoice->pdf_path) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-semibold hover:bg-primary-100"
                            >
                                <x-filament::icon icon="heroicon-o-eye" class="w-4 h-4" />
                                View
                            </a>
                            
                                href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($invoice->pdf_path) }}"
                                download
                                class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                                Download
                            </a>
                        @else
                            <span class="text-xs text-gray-400">No PDF</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-gray-400">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="w-12 h-12 mx-auto mb-3 opacity-30"
                />
                <p class="text-lg font-medium">No invoices yet</p>
                <p class="text-sm">Your billing invoices will appear here</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>