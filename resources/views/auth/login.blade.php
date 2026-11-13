<x-guest-layout title="Se connecter">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold mb-1" style="color:#a81d3b; letter-spacing:.15em;">GCD+ · EST de Fès</p>
        <h2 class="fw-bold mb-2">Se connecter</h2>
        <p class="text-muted">Saisissez votre adresse e-mail et votre mot de passe pour accéder à votre espace.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label small" for="remember_me">{{ __('Se souvenir de moi') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a class="small fw-semibold text-decoration-none" href="{{ route('password.request') }}" style="color:#005292;">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif
        </div>

        <button class="btn gcd-action w-100 py-2">
            {{ __('Se connecter') }}
        </button>
    </form>

    <div class="mt-4 pt-4 border-top text-center small text-muted">
        <span>Vous souhaitez postuler ?</span>
        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none ms-1" style="color:#005292;">Créer un compte</a>
    </div>
</x-guest-layout>
