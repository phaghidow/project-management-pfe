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
    <body class="antialiased bg-[#F8F9FC] text-[#1A202C]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            
            <div class="mb-4">
                <a href="/">
                    <img src="{{ asset('images/logo-at.png') }}" alt="Logo" class="w-24 h-auto">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-2xl shadow-blue-900/10 overflow-hidden sm:rounded-3xl border border-[#E2E8F0]">
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-slate-400">
                &copy; {{ date('Y') }} Algérie Télécom - Direction des Systèmes d'Information
            </p>
        </div>
    </body>
</html>