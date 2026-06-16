<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    <a href="/" class="text-white text-2xl font-bold tracking-tight">⚡ BOLT</a>
    
    <div class="flex items-center space-x-6 text-white">
        <a href="/" class="hover:text-indigo-200 transition">Home</a>

        @auth
            <div class="relative">
            <livewire:notification-bell />
            </div>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" 
                        class="flex cursor-pointer items-center space-x-2 focus:outline-none transition hover:opacity-80">
                        @php
                            $user = auth()->user();
                            $avatarUrl = auth()->user()->profile?->getFirstMediaUrl('avatar') ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name);
                        @endphp
                        <img src="{{ $avatarUrl}}" 
                            class="w-10 h-10 rounded-full border-2 border-white/30 shadow-sm">
                        <span class="font-medium hidden capitalize sm:block">{{ auth()->user()->name }}</span>
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                    
                    <div class="px-4 py-3 border-b border-gray-50">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Account</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="py-1">
                        @if(auth()->user()->hasRole('super-admin'))
                            <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">Admin Panel</a>
                        @else
                            <a href="/portal" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition">My Dashboard</a>
                        @endif
                    </div>
                    
                    <div class="border-t border-gray-100">
                        <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 cursor-pointer hover:bg-red-50 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <a href="/portal/login" class="hover:text-indigo-200">Login</a>
            <a href="/portal/register" class="bg-white text-indigo-700 px-4 py-1.5 rounded-lg font-bold shadow-sm hover:bg-indigo-50 transition">Register</a>
        @endauth
    </div>
</div>