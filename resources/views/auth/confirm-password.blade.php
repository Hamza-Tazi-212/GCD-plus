<x-guest-layout title="Confirmation du mot de passe">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D; letter-spacing:.15em;">Vérification de sécurité</p>
        <h2 class="fw-bold mb-2">Confirmez votre mot de passe</h2>
        <p class="text-muted">Confirmez votre mot de passe pour poursuivre.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Confirmer') }}
        </x-primary-button>
    </form>
</x-guest-layout>
