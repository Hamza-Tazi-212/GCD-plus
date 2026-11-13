<x-app-layout title="Déposer une candidature">
    @include('partials.campaign-deadline')
    @if ($period && ! $period->isOpen())
        <p><a href="{{ route('calendar') }}">Consulter le calendrier</a></p>
    @else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Sujet de candidature</p>
            <h2 class="h4 fw-bold mb-1">{{ $subject->title }}</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-person-badge me-1"></i>{{ $subject->professor->name }}
                &middot; <i class="bi bi-building me-1"></i>{{ $subject->department->name }}
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h3 class="h6 fw-bold mb-0"><i class="bi bi-person-check me-2 text-primary"></i>Votre profil</h3>
            <a href="{{ route('candidate.profile.overview') }}" class="btn btn-sm btn-outline-secondary">Modifier le profil</a>
        </div>
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-md-6"><span class="text-muted">Nom complet :</span> {{ $profile->fullName() }}</div>
                <div class="col-md-6"><span class="text-muted">Nationalité :</span> {{ $profile->nationality }}</div>
                <div class="col-md-6"><span class="text-muted">Dernier diplôme :</span> {{ $profile->last_degree }}</div>
                <div class="col-md-6"><span class="text-muted">Établissement :</span> {{ $profile->last_institution }}</div>
            </div>
            @if ($profile->documents->isNotEmpty())
                <hr>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($profile->documents as $document)
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-2 px-2">
                            <i class="bi {{ $document->type->icon() }}"></i>{{ $document->type->label() }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('candidate.applications.store', $subject) }}" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="h6 fw-bold mb-0"><i class="bi bi-envelope-paper me-2 text-primary"></i>Motivation pour ce sujet</h3>
                <p class="small text-muted mb-0">Renseignez cette partie pour chaque sujet choisi.</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <x-input-error :messages="$errors->get('application')" />
                        <p class="small"><a href="{{ route('calendar') }}">Consulter le calendrier des campagnes</a></p>
                        <x-file-input name="motivation_letter" label="Lettre de motivation" required :max-size-mb="5" />
                    </div>
                    <div class="col-12">
                        <x-input-label for="motivation_summary" value="Résumé de la motivation (facultatif)" />
                        <textarea id="motivation_summary" name="motivation_summary" rows="4" class="form-control" placeholder="Expliquez brièvement votre intérêt pour ce sujet.">{{ old('motivation_summary') }}</textarea>
                        <x-input-error :messages="$errors->get('motivation_summary')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('candidate.subjects.show', $subject) }}" class="btn btn-outline-secondary">Annuler</a>
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-send me-1"></i> Envoyer ma candidature
            </button>
        </div>
    </form>
    @endif
</x-app-layout>
