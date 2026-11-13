<x-guest-layout title="Mot de passe oublié">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D; letter-spacing:.15em;">Récupération du compte</p>
        <h2 class="fw-bold mb-2">Mot de passe oublié ?</h2>
        <p class="text-muted">Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Envoyer le lien de réinitialisation') }}
        </x-primary-button>
    </form>
</x-guest-layout>
