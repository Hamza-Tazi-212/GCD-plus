<x-app-layout title="Fiche du sujet">
    <a href="{{ route('professor.subjects.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold mb-3" style="color:#005292;">
        <i class="bi bi-arrow-left"></i> Retour à mes sujets
    </a>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <span class="badge bg-primary-subtle text-primary mb-2">{{ $subject->department->name }}</span>
            <h2 class="h4 fw-bold mb-0">{{ $subject->title }}</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('professor.subjects.edit', $subject) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete-subject-modal">
                <i class="bi bi-trash me-1"></i>Supprimer
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Description</h3>
                    <p class="mb-3">{{ $subject->description }}</p>

                    @if ($subject->responsibilities)
                        <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Travail attendu du doctorant</h3>
                        <p class="mb-3">{{ $subject->responsibilities }}</p>
                    @endif

                    @if ($subject->candidate_profile)
                        <h3 class="h6 fw-bold text-uppercase text-muted mb-2">Profil candidat</h3>
                        <p class="mb-3">{{ $subject->candidate_profile }}</p>
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

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0">Candidatures</h3>
                </div>
                <div class="card-body">
                    @if ($applications->isEmpty())
                        <p class="text-muted mb-0">Aucune candidature reçue pour ce sujet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Candidat</th>
                                        <th>Statut</th>
                                        <th>Envoyée le</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($applications as $application)
                                        <tr>
                                            <td>{{ $application->fullName() }}</td>
                                            <td><x-status-badge :status="$application->status" /></td>
                                            <td class="text-muted small">{{ $application->submitted_at?->format('d/m/Y') }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('professor.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Voir le dossier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $applications->links() }}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">Informations sur le sujet</h3>
                    <p class="mb-1"><i class="bi bi-hash me-2 text-primary"></i>{{ $applications->total() }} candidature(s)</p>
                    <p class="mb-0">
                        <span class="badge {{ $subject->is_open ? 'bg-success' : 'bg-danger' }}">
                            {{ $subject->is_open ? 'Ouvert aux candidatures' : 'Fermé' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="delete-subject-modal" title="Supprimer ce sujet de thèse ?">
        <p class="mb-0">Cette action supprimera définitivement « {{ $subject->title }} » et les candidatures associées.</p>
        <x-slot name="footer">
            <x-secondary-button data-bs-dismiss="modal">Annuler</x-secondary-button>
            <form method="POST" action="{{ route('professor.subjects.destroy', $subject) }}">
                @csrf
                @method('DELETE')
                <x-danger-button>Supprimer définitivement</x-danger-button>
            </form>
        </x-slot>
    </x-modal>
</x-app-layout>
