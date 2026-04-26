<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil - Gestion de Projets Algérie Télécom</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F8F9FC] text-[#1A202C] antialiased flex items-center justify-center min-h-screen p-6">
    
    <div class="max-w-md w-full text-center bg-white p-10 rounded-3xl shadow-2xl shadow-blue-900/10 border border-[#E2E8F0]">
        
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('images/logo-at.png') }}" alt="Algérie Télécom" class="h-24 object-contain">
        </div>

        <h1 class="text-3xl font-extrabold mb-2 text-[#1A202C]">Gestion de Projets</h1>
        <p class="text-slate-600 mb-10 text-lg">Plateforme interne de suivi et de collaboration.</p>
        
        <div class="space-y-4">
            @auth
                <a href="{{ route('dashboard') }}" 
                   class="block w-full bg-[#2E3192] hover:bg-[#1E216D] text-white font-bold py-4 px-6 rounded-2xl transition duration-200 shadow-lg shadow-blue-900/20">
                    Accéder au Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" 
                   class="block w-full bg-[#2E3192] hover:bg-[#1E216D] text-white font-bold py-4 px-6 rounded-2xl transition duration-200 shadow-lg shadow-blue-900/20">
                    Se connecter
                </a>
                
                <div class="mt-8 border-t border-[#E2E8F0] pt-6">
                    <p class="text-sm text-slate-500 italic">
                        L'accès est exclusivement réservé au personnel autorisé.
                    </p>
                    <p class="text-sm text-slate-700 font-medium mt-2">
                        Domaines autorisés : @at.dz ou @algerietelecom.dz
                    </p>
                </div>
            @endauth
        </div>
    </div>

</body>
</html>