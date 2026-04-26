<x-guest-layout>
    <div class="mb-8 text-center">
        <img src="{{ asset('images/logo-at.png') }}" alt="Algérie Télécom" class="h-20 mx-auto mb-4">
        <h2 class="text-2xl font-bold text-[#1A202C]">Connexion au portail</h2>
        <p class="text-sm text-slate-500">Veuillez entrer vos identifiants professionnels</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="login" :value="__('Nom d\'utilisateur ou Email')" class="text-[#1A202C] font-semibold" />
            <x-text-input id="login" 
                class="block mt-1 w-full border-[#E2E8F0] focus:border-[#2E3192] focus:ring-[#2E3192] rounded-xl shadow-sm text-gray-900" 
                type="text" name="login" :value="old('login')" required autofocus />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Mot de passe')" class="text-[#1A202C] font-semibold" />
            <x-text-input id="password" 
                class="block mt-1 w-full border-[#E2E8F0] focus:border-[#2E3192] focus:ring-[#2E3192] rounded-xl shadow-sm text-gray-900"
                type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#2E3192] shadow-sm focus:ring-[#2E3192]" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Se souvenir de moi') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#2E3192] hover:underline font-medium" href="{{ route('password.request') }}">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-[#2E3192] hover:bg-[#1E216D] text-white font-bold py-4 px-6 rounded-2xl transition duration-200 shadow-lg shadow-blue-900/20">
                {{ __('Se connecter') }}
            </button>
        </div>
    </form>
</x-guest-layout>