<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Algérie Télécom') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="antialiased bg-gray-50 text-[#1A202C]">
        <div id="flash-toast-app"></div>
        <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-[450px]">
            <div class="w-full px-8 py-10 bg-white shadow-xl shadow-slate-900/5 overflow-hidden rounded-3xl border border-[#E2E8F0]">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Algérie Télécom - Direction des Systèmes d'Information
            </p>
            </div>
        </div>
    </body>
</html>