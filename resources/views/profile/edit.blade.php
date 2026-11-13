<x-app-layout title="Mon profil">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D; letter-spacing:.15em;">Gestion du profil</p>
                    <h2 class="h4 fw-bold mb-1">Mettre à jour les informations du compte</h2>
                    <p class="text-muted mb-0">Gérez vos informations personnelles, votre mot de passe et votre compte.</p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-12" id="update-password-form">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm border-danger-subtle">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
