@php $readOnly = $user->isProfessor(); @endphp

<section>
    <header class="mb-4">
        <h3 class="h5 fw-bold mb-1">{{ __('Informations du compte') }}</h3>
        <p class="text-muted small mb-0">
            {{ $readOnly ? __('Vos informations sont gérées par l’administration.') : __("Modifiez les informations et l’adresse e-mail de votre compte.") }}
        </p>
    </header>

    @if ($readOnly)
        <div class="mb-3">
            <x-input-label value="{{ __('Nom') }}" />
            <p class="form-control-plaintext fw-semibold">{{ $user->name }}</p>
        </div>

        <div class="mb-3">
            <x-input-label value="{{ __('Adresse e-mail') }}" />
            <p class="form-control-plaintext fw-semibold">{{ $user->email }}</p>
        </div>

        <div class="mb-0">
            <x-input-label value="{{ __('Téléphone') }}" />
            <p class="form-control-plaintext fw-semibold">{{ $user->phone ?: '—' }}</p>
        </div>

        <p class="small text-muted mb-0"><i class="bi bi-info-circle me-1"></i>{{ __('Contactez l’administration pour modifier ces informations.') }}</p>
    @else
        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="mb-3">
                <x-input-label for="name" :value="__('Nom')" />
                <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="mb-3">
                <x-input-label for="email" :value="__('Adresse e-mail')" />
                <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="mb-3">
                <x-input-label for="phone" :value="__('Téléphone')" />
                <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" />
            </div>

            <div class="d-flex align-items-center gap-3">
                <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p class="small text-success mb-0">{{ __('Modifications enregistrées.') }}</p>
                @endif
            </div>
        </form>
    @endif
</section>
