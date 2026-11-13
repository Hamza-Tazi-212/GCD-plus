<x-app-layout title="Mon profil">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Profil candidat</p>
            <h2 class="h4 fw-bold mb-1">Compléter mon profil</h2>
            <p class="h6 mb-0 " style="color:#ea0808;">Renseignez ce profil une fois pour toutes vos candidatures. Joignez une lettre de motivation pour chaque sujet.</p>
        </div>
    </div>

    @if ($profile->isComplete())
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                Votre profil est complet
                @if ($profile->completed_at)
                    <span class="text-muted">(depuis {{ $profile->completed_at->format('d/m/Y') }})</span>
                @endif
                Vous pouvez postuler aux sujets ouverts.
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="h6 fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Informations personnelles</h3>
                        @if ($profile->personalComplete())
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-lg"></i></span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">À compléter</span>
                        @endif
                    </div>
                    <p class="small text-muted flex-grow-1">Identité, coordonnées et pièce d’identité.</p>
                    <a href="{{ route('candidate.profile.personal.edit') }}" hx-boost="true" class="btn btn-sm btn-outline-primary">
                        {{ $profile->personalComplete() ? 'Modifier' : 'Compléter' }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="h6 fw-bold mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Parcours académique</h3>
                        @if ($profile->academicComplete())
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-lg"></i></span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">À compléter</span>
                        @endif
                    </div>
                    <p class="small text-muted flex-grow-1">Parcours, diplômes, relevés de notes, CV et justificatifs.</p>
                    <a href="{{ route('candidate.profile.academic.edit') }}" hx-boost="true" class="btn btn-sm btn-outline-primary">
                        {{ $profile->academicComplete() ? 'Modifier' : 'Compléter' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @unless ($profile->isComplete())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-2"><i class="bi bi-list-check me-2 text-warning"></i>Éléments manquants</h3>
                <ul class="mb-0 small">
                    @foreach ($profile->missingRequirements() as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endunless

    @if ($profile->isComplete())
        <div class="d-flex justify-content-end">
            @if ($intendedSubject)
                <a href="{{ route('candidate.applications.create', $intendedSubject) }}" class="btn btn-success px-4">
                    <i class="bi bi-send me-1"></i> Poursuivre ma candidature au sujet « {{ $intendedSubject->title }} »
                </a>
            @else
                <a href="{{ route('candidate.subjects.index') }}" class="btn btn-success px-4">
                    <i class="bi bi-search me-1"></i> Consulter les sujets ouverts
                </a>
            @endif
        </div>
    @endif
</x-app-layout>
