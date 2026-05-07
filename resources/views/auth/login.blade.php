<x-guest-layout>
    <div x-data="{ 
        login: '', 
        password: '', 
        showPassword: false,
        get isFormValid() { return this.login.trim().length > 0 && this.password.trim().length > 0 }
    }">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/logo-at.png') }}" alt="Algérie Télécom" class="h-20 mx-auto mb-4">
            <h2 class="text-2xl font-bold text-[#1A202C]">Connexion au portail</h2>
            <p class="text-sm text-slate-500">Veuillez entrer vos identifiants professionnels</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Identifiant -->
            <div>
                <x-input-label for="login" :value="__('Nom d\'utilisateur ou Email')" class="text-[#1A202C] font-semibold mb-1" />
                <x-text-input id="login" 
                    x-model="login"
                    class="block w-full border-[#E2E8F0] focus:border-[#2E3192] focus:ring-[#2E3192] rounded-2xl shadow-sm text-gray-900 py-4 px-5" 
                    type="text" 
                    name="login" 
                    :value="old('login')" 
                    required 
                    autofocus />
                <x-input-error :messages="$errors->get('login')" class="mt-2" />
            </div>

            <!-- Mot de passe -->
            <div>
                <x-input-label for="password" :value="__('Mot de passe')" class="text-[#1A202C] font-semibold mb-1" />
                <div class="relative group">
                    <x-text-input id="password" 
                        x-model="password"
                        class="block w-full border-[#E2E8F0] focus:border-[#2E3192] focus:ring-[#2E3192] rounded-2xl shadow-sm text-gray-900 py-4 px-5 pr-12"
                        x-bind:type="showPassword ? 'text' : 'password'" 
                        name="password" 
                        required 
                        autocomplete="current-password" />
                    
                    <!-- Toggle Visibilité -->
                    <button type="button" 
                        @click="showPassword = !showPassword" 
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#2E3192] transition-colors focus:outline-none">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Options -->
            <div class="flex items-center justify-between mt-4">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#2E3192] shadow-sm focus:ring-[#2E3192]" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Se souvenir de moi') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-[#2E3192] hover:underline font-medium" href="{{ route('password.request') }}">
                        {{ __('Mot de passe oublié ?') }}
                    </a>
                @endif
            </div>

            <!-- Bouton de Connexion -->
            <div class="pt-2">
                <button type="submit" 
                    x-bind:disabled="!isFormValid"
                    x-bind:class="isFormValid 
                        ? 'bg-[#2E3192] hover:bg-[#1E216D] shadow-lg shadow-blue-900/20 active:scale-[0.98]' 
                        : 'bg-gray-100 text-gray-400 cursor-not-allowed shadow-none'"
                    class="w-full text-white font-bold py-4 px-6 rounded-2xl transition-all duration-300 uppercase tracking-widest text-sm">
                    {{ __('Se connecter') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>