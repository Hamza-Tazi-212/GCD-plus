<x-guest-layout title="Créer un compte">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D; letter-spacing:.15em;">Inscription du candidat</p>
        <h2 class="fw-bold mb-2">Créer votre compte</h2>
        <p class="text-muted">Créez un compte candidat pour déposer et suivre vos candidatures.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" :value="__('Nom complet')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="phone" :value="__('Téléphone')" />
            <x-text-input id="phone" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirmation du mot de passe')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <a class="small fw-semibold text-decoration-none" href="{{ route('login') }}" style="color:#005292;">
                {{ __('Déjà inscrit ?') }}
            </a>

            <x-primary-button>
                {{ __('S’inscrire') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
