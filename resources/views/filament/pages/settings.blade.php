<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <div class="flex justify-end">
        <x-filament::button wire:click="save" size="lg">
            Save Settings
        </x-filament::button>
    </div>
</x-filament-panels::page>