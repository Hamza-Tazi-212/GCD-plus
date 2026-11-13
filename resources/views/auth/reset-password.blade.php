<x-guest-layout title="Réinitialiser le mot de passe">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D; letter-spacing:.15em;">Récupération du compte</p>
        <h2 class="fw-bold mb-2">Réinitialisez votre mot de passe</h2>
        <p class="text-muted">Choisissez un nouveau mot de passe robuste.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Réinitialiser le mot de passe') }}
        </x-primary-button>
    </form>
</x-guest-layout>
