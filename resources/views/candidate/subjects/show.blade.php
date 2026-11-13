<x-app-layout title="Fiche du sujet">
    <a href="{{ route('candidate.subjects.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold mb-3" style="color:#005292;">
        <i class="bi bi-arrow-left"></i> Retour aux sujets
    </a>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <span class="badge bg-primary-subtle text-primary mb-2">{{ $subject->department->name }}</span>
                    @if (! $subject->is_open)
                        <span class="badge bg-secondary mb-2">Fermé</span>
                    @endif
                    <h2 class="h3 fw-bold mb-3">{{ $subject->title }}</h2>
                    @if ($subject->laboratory || $subject->field)
                        <p class="small text-muted">{{ implode(' · ', array_filter([$subject->laboratory, $subject->field])) }}</p>
                    @endif

                    <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Description</h3>
                    <p class="mb-4">{{ $subject->description }}</p>

                    @if ($subject->responsibilities)
                        <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Travail attendu du doctorant</h3>
                        <p class="mb-4">{{ $subject->responsibilities }}</p>
                    @endif

                    @if ($subject->candidate_profile)
                        <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Profil candidat</h3>
                        <p class="mb-4">{{ $subject->candidate_profile }}</p>
                    @endif

                    @if ($subject->keywords)
                        <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Mots-clés</h3>
                        <p class="mb-0">
                            @foreach (explode(',', $subject->keywords) as $keyword)
                                <span class="badge bg-light text-dark border me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">Encadrant</h3>
                    <p class="mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>{{ $subject->professor->name }}</p>
                    <p class="mb-1 small text-muted"><i class="bi bi-envelope me-2"></i>{{ $subject->professor->email }}</p>
                    <hr>
                    <p class="mb-0"><i class="bi bi-building me-2 text-primary"></i>{{ $subject->department->name }}</p>
                </div>
            </div>

            @if (auth()->user()->isCandidate())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        @if ($alreadyApplied)
                            <p class="text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i>Vous avez déjà postulé à ce sujet.</p>
                            <a href="{{ route('candidate.applications.index') }}" class="btn btn-outline-primary w-100">Voir mes candidatures</a>
                        @elseif (! $subject->is_open)
                            <p class="text-muted mb-0">Les candidatures sont fermées pour ce sujet.</p>
                        @elseif (! $profileComplete)
                            <p class="text-muted small mb-2">Complétez votre profil avant de postuler.</p>
                            <a href="{{ route('candidate.applications.create', $subject) }}" class="btn btn-warning w-100 py-2">
                                <i class="bi bi-person-badge me-1"></i> Compléter mon profil pour postuler
                            </a>
                        @else
                            <a href="{{ route('candidate.applications.create', $subject) }}" class="btn btn-success w-100 py-2">
                                <i class="bi bi-send me-1"></i> Postuler à ce sujet
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
