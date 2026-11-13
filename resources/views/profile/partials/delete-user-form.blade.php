<section>
    <header class="mb-3">
        <h3 class="h5 fw-bold mb-1 text-danger">{{ __('Supprimer le compte') }}</h3>
        <p class="text-muted small mb-0">
            {{ __('La suppression du compte effacera définitivement les données associées. Téléchargez auparavant les informations que vous souhaitez conserver.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        {{ __('Supprimer le compte') }}
    </button>

    <x-modal id="confirm-user-deletion" title="{{ __('Confirmer la suppression de votre compte ?') }}">
        <form method="post" action="{{ route('profile.destroy') }}" id="delete-account-form">
            @csrf
            @method('delete')

            <p class="text-muted small">
                {{ __('Saisissez votre mot de passe pour confirmer la suppression définitive du compte et des données associées.') }}
            </p>

            <x-input-label for="password_delete" value="{{ __('Mot de passe') }}" class="visually-hidden" />
            <x-text-input id="password_delete" name="password" type="password" placeholder="{{ __('Mot de passe') }}" />
            <x-input-error :messages="$errors->userDeletion->get('password')" />
        </form>

        <x-slot name="footer">
            <x-secondary-button data-bs-dismiss="modal">{{ __('Annuler') }}</x-secondary-button>
            <x-danger-button type="submit" form="delete-account-form">{{ __('Supprimer le compte') }}</x-danger-button>
        </x-slot>
    </x-modal>
</section>
