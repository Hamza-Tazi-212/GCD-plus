@php
    $byType = $profile->documents->groupBy(fn ($document) => $document->type->value);
    $has = fn (string $type) => $byType->has($type);
    $track = old('degree_track', $profile->degree_track?->value);
@endphp
<x-app-layout title="Parcours académique">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Profil candidat</p>
            <h2 class="h4 fw-bold mb-1">Parcours académique</h2>
            <p class="text-muted mb-0">Ces informations sont réutilisées pour chaque candidature. Vous pouvez enregistrer une saisie partielle et la reprendre plus tard.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('candidate.profile.academic.update') }}" enctype="multipart/form-data" novalidate
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
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-signpost-split me-2 text-primary"></i>Parcours de formation</h3>
                <p class="small text-muted mb-0">Sélectionnez le parcours correspondant à votre diplôme.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="degree_track" id="track_master" value="master" autocomplete="off" @checked($track === 'master')>
                        <label class="degree-track-card" for="track_master">
                            <span class="degree-track-icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi bi-mortarboard fs-4"></i>
                            </span>
                            <span class="flex-grow-1">
                                <span class="fw-bold d-block mb-1">Master</span>
                                <span class="small text-muted">La licence est obligatoire pour ce parcours.</span>
                            </span>
                            <i class="bi bi-check-circle-fill degree-track-check"></i>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <input type="radio" class="btn-check" name="degree_track" id="track_engineer" value="engineer" autocomplete="off" @checked($track === 'engineer')>
                        <label class="degree-track-card" for="track_engineer">
                            <span class="degree-track-icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi bi-gear fs-4"></i>
                            </span>
                            <span class="flex-grow-1">
                                <span class="fw-bold d-block mb-1">Diplôme d’ingénieur</span>
                                <span class="small text-muted">La licence est facultative pour ce parcours.</span>
                            </span>
                            <i class="bi bi-check-circle-fill degree-track-check"></i>
                        </label>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('degree_track')" class="mt-2" />
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-award me-2 text-primary"></i>Dernier diplôme</h3>
                <p class="small text-muted mb-0">Diplôme de niveau le plus élevé que vous avez obtenu.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="last_degree" value="Dernier diplôme obtenu" />
                        <x-text-input id="last_degree" name="last_degree" value="{{ old('last_degree', $profile->last_degree) }}" placeholder="Ex. master en informatique" required />
                        <x-input-error :messages="$errors->get('last_degree')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="last_institution" value="Établissement" />
                        <x-text-input id="last_institution" name="last_institution" value="{{ old('last_institution', $profile->last_institution) }}" required />
                        <x-input-error :messages="$errors->get('last_institution')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="graduation_year" value="Année d’obtention" />
                        <x-text-input id="graduation_year" type="number" name="graduation_year" value="{{ old('graduation_year', $profile->graduation_year) }}" min="1980" max="{{ now()->year }}" required />
                        <x-input-error :messages="$errors->get('graduation_year')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="field_of_study" value="Spécialité" />
                        <x-text-input id="field_of_study" name="field_of_study" value="{{ old('field_of_study', $profile->field_of_study) }}" required />
                        <x-input-error :messages="$errors->get('field_of_study')" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="grade_mention" value="Mention (facultative)" />
                        <x-text-input id="grade_mention" name="grade_mention" value="{{ old('grade_mention', $profile->grade_mention) }}" placeholder="e.g. Très Bien" />
                        <x-input-error :messages="$errors->get('grade_mention')" />
                    </div>
                    <div class="col-12">
                        <hr>
                        @if ($has('last_diploma_certificate'))
                            @foreach ($byType->get('last_diploma_certificate', []) as $document)
                                <x-profile-document-chip :document="$document" />
                            @endforeach
                        @endif
                        <x-file-input name="last_diploma_certificate" label="Attestation du dernier diplôme" required :max-size-mb="10"
                            :hint="$has('last_diploma_certificate') ? 'Choose a new file to replace the one above.' : null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Licence</h3>
                <p class="small text-muted mb-0">Obligatoire pour le parcours master, facultatif pour le parcours ingénieur.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="license_institution" value="Établissement" />
                        <x-text-input id="license_institution" name="license_institution" value="{{ old('license_institution', $profile->license_institution) }}" :required="$track === 'master'" data-required-for-track="master" />
                        <x-input-error :messages="$errors->get('license_institution')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="license_field_of_study" value="Spécialité" />
                        <x-text-input id="license_field_of_study" name="license_field_of_study" value="{{ old('license_field_of_study', $profile->license_field_of_study) }}" :required="$track === 'master'" data-required-for-track="master" />
                        <x-input-error :messages="$errors->get('license_field_of_study')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="license_graduation_year" value="Année d’obtention" />
                        <x-text-input id="license_graduation_year" type="number" name="license_graduation_year" value="{{ old('license_graduation_year', $profile->license_graduation_year) }}" min="1980" max="{{ now()->year }}" :required="$track === 'master'" data-required-for-track="master" />
                        <x-input-error :messages="$errors->get('license_graduation_year')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="license_grade_mention" value="Mention (facultative)" />
                        <x-text-input id="license_grade_mention" name="license_grade_mention" value="{{ old('license_grade_mention', $profile->license_grade_mention) }}" placeholder="Ex. Bien" />
                        <x-input-error :messages="$errors->get('license_grade_mention')" />
                    </div>
                    <div class="col-12">
                        <hr>
                        @if ($has('license_certificate'))
                            @foreach ($byType->get('license_certificate', []) as $document)
                                <x-profile-document-chip :document="$document" />
                            @endforeach
                        @endif
                        <x-file-input name="license_certificate" label="Attestation de licence" :required="$track === 'master'" :max-size-mb="10"
                            data-required-for-track="master"
                            hint="Obligatoire pour le parcours master, facultatif pour le parcours ingénieur." />
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-patch-check me-2 text-primary"></i>Baccalauréat</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="bac_institution" value="Établissement" />
                        <x-text-input id="bac_institution" name="bac_institution" value="{{ old('bac_institution', $profile->bac_institution) }}" required />
                        <x-input-error :messages="$errors->get('bac_institution')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="bac_field_of_study" value="Spécialité" />
                        <x-text-input id="bac_field_of_study" name="bac_field_of_study" value="{{ old('bac_field_of_study', $profile->bac_field_of_study) }}" placeholder="e.g. Sciences Mathématiques" required />
                        <x-input-error :messages="$errors->get('bac_field_of_study')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="bac_graduation_year" value="Année d’obtention" />
                        <x-text-input id="bac_graduation_year" type="number" name="bac_graduation_year" value="{{ old('bac_graduation_year', $profile->bac_graduation_year) }}" min="1980" max="{{ now()->year }}" required />
                        <x-input-error :messages="$errors->get('bac_graduation_year')" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="bac_grade_mention" value="Mention (facultative)" />
                        <x-text-input id="bac_grade_mention" name="bac_grade_mention" value="{{ old('bac_grade_mention', $profile->bac_grade_mention) }}" placeholder="Ex. Assez bien" />
                        <x-input-error :messages="$errors->get('bac_grade_mention')" />
                    </div>
                    <div class="col-12">
                        <hr>
                        @if ($has('baccalaureat_certificate'))
                            @foreach ($byType->get('baccalaureat_certificate', []) as $document)
                                <x-profile-document-chip :document="$document" />
                            @endforeach
                        @endif
                        <x-file-input name="baccalaureat_certificate" label="Attestation du baccalauréat" required :max-size-mb="10"
                            :hint="$has('baccalaureat_certificate') ? 'Choose a new file to replace the one above.' : null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-file-earmark-pdf me-2 text-primary"></i>Relevés de notes et CV</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @foreach ($byType->get('transcript', []) as $document)
                            <x-profile-document-chip :document="$document" />
                        @endforeach
                        <x-file-input name="transcript" label="Relevés de notes" required multiple :max-size-mb="10"
                            hint="Vous pouvez ajouter plusieurs fichiers, par exemple un relevé par année." />
                    </div>
                    <div class="col-md-6">
                        @foreach ($byType->get('cv', []) as $document)
                            <x-profile-document-chip :document="$document" />
                        @endforeach
                        <x-file-input name="cv" label="CV avec photo" required :max-size-mb="5"
                            :hint="$has('cv') ? 'Choose a new file to replace the one above.' : null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Pièces facultatives</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @foreach ($byType->get('publication', []) as $document)
                            <x-profile-document-chip :document="$document" />
                        @endforeach
                        <x-file-input name="publication" label="Publications" multiple :max-size-mb="10" hint="Scientific articles, conference papers, etc." />
                    </div>
                    <div class="col-md-6">
                        @foreach ($byType->get('recommendation_letter', []) as $document)
                            <x-profile-document-chip :document="$document" />
                        @endforeach
                        <x-file-input name="recommendation_letter" label="Lettre de recommandation" :max-size-mb="5"
                            :hint="$has('recommendation_letter') ? 'Choose a new file to replace the one above.' : null" />
                    </div>
                    <div class="col-md-6">
                        @foreach ($byType->get('other', []) as $document)
                            <x-profile-document-chip :document="$document" />
                        @endforeach
                        <x-file-input name="other" label="Autre justificatif" :max-size-mb="10"
                            :hint="$has('other') ? 'Choose a new file to replace the one above.' : null" />
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
