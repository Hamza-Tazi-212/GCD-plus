@php
    $hasCin = $profile->hasDocument(\App\Enums\DocumentType::CinPassport);
@endphp
<x-app-layout title="Informations personnelles">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Profil candidat</p>
            <h2 class="h4 fw-bold mb-1">Informations personnelles</h2>
            <p class="text-muted mb-0">Ces informations sont réutilisées pour chaque candidature.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('candidate.profile.personal.update') }}" enctype="multipart/form-data" novalidate
          hx-boost="true" hx-encoding="multipart/form-data" hx-target="body" hx-swap="outerHTML">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Corrigez les points suivants avant d’enregistrer cette étape.</p>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Informations personnelles</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="first_name" value="Prénom" />
                        <x-text-input id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}" required />
                        <x-input-error :messages="$errors->get('first_name')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="last_name" value="Nom" />
                        <x-text-input id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}" required />
                        <x-input-error :messages="$errors->get('last_name')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="birth_date" value="Date de naissance" />
                        <x-text-input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $profile->birth_date?->format('Y-m-d')) }}" max="{{ now()->subYears(18)->format('Y-m-d') }}" required />
                        <x-input-error :messages="$errors->get('birth_date')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="birth_place" value="Lieu de naissance" />
                        <x-text-input id="birth_place" name="birth_place" value="{{ old('birth_place', $profile->birth_place) }}" required />
                        <x-input-error :messages="$errors->get('birth_place')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="nationality" value="Nationalité" />
                        <x-text-input id="nationality" name="nationality" value="{{ old('nationality', $profile->nationality ?? 'Moroccan') }}" required />
                        <x-input-error :messages="$errors->get('nationality')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="gender" value="Sexe" />
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">Sélectionnez…</option>
                            <option value="male" @selected(old('gender', $profile->gender) === 'male')>Masculin</option>
                            <option value="female" @selected(old('gender', $profile->gender) === 'female')>Féminin</option>
                        </select>
                        <x-input-error :messages="$errors->get('gender')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="cin_or_passport_number" value="Numéro de CIN ou de passeport" />
                        <x-text-input id="cin_or_passport_number" name="cin_or_passport_number" value="{{ old('cin_or_passport_number', $profile->cin_or_passport_number) }}" required />
                        <x-input-error :messages="$errors->get('cin_or_passport_number')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="phone" value="Téléphone" />
                        <x-text-input id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" required />
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>
                    <div class="col-md-8">
                        <x-input-label for="address" value="Adresse du domicile" />
                        <x-text-input id="address" name="address" value="{{ old('address', $profile->address) }}" required />
                        <x-input-error :messages="$errors->get('address')" />
                    </div>
                    <div class="col-12">
                        <hr>
                        @if ($hasCin)
                            @foreach ($profile->documents->where('type', \App\Enums\DocumentType::CinPassport) as $document)
                                <x-profile-document-chip :document="$document" />
                            @endforeach
                        @endif
                        <x-file-input name="cin_passport" label="CIN ou passeport" required :max-size-mb="5"
                            :hint="$hasCin ? 'Choose a new file to replace the one above.' : null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('candidate.profile.overview') }}" hx-boost="true" class="btn btn-outline-secondary">Annuler</a>
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-1"></i> Enregistrer et continuer
            </button>
        </div>
    </form>
</x-app-layout>
