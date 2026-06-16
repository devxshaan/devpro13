<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 ">
    
        {{-- Form component ko direct call karo --}}
        <form wire:submit="save" enctype="multipart/form-data">
            
            {{ $this->form }}
            
            <div class="mt-10 ">
                <x-filament::button type="submit" size="lg">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>