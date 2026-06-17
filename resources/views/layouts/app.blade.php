<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Bolt App' }}</title>
    {!! SEO::generate() !!}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body class="flex flex-col min-h-screen">

    
        <header class="fixed top-0 w-full z-50 bg-indigo-600 shadow-md">
            @include('layouts.partial.header')
        </header>

        <main class="flex-grow pt-20 pb-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @isset($slot) {{ $slot }} @else @yield('content') @endisset
            </div>
        </main>

        <footer class="bg-indigo-900 text-gray-400 py-6">
            @include('layouts.partial.footer')
        </footer>
    

    @livewireScripts
</body>
</html>