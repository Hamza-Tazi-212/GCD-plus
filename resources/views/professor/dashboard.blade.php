<x-app-layout title="Tableau de bord encadrant">
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <x-stat-card label="Mes sujets" :value="$overview['total_subjects']" icon="bi-journal-richtext" color="primary" />
        </div>
        <div class="col-sm-6 col-lg-3">
            <x-stat-card label="Sujets ouverts" :value="$overview['open_subjects']" icon="bi-unlock" color="success" />
        </div>
        <div class="col-sm-6 col-lg-3">
            <x-stat-card label="Candidatures reçues" :value="$overview['total_applicants']" icon="bi-people" color="primary" />
        </div>
        <div class="col-sm-6 col-lg-3">
            <x-stat-card label="En attente ou admissibles à l’oral" :value="$overview['status_counts']['pending'] + $overview['status_counts']['under_review']" icon="bi-hourglass-split" color="secondary" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-bold mb-0">Candidatures récentes</h3>
                    <a href="{{ route('professor.applications.index') }}" class="small text-decoration-none">Tout voir</a>
                </div>
                <div class="card-body">
                    @if ($overview['recent_applications']->isEmpty())
                        <p class="text-muted mb-0">Vous n’avez reçu aucune candidature.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Candidat</th>
                                        <th>Sujet</th>
                                        <th>Statut</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overview['recent_applications'] as $application)
                                        <tr>
                                            <td>{{ $application->fullName() }}</td>
                                            <td>{{ $application->subject->title }}</td>
                                            <td><x-status-badge :status="$application->status" /></td>
                                            <td class="text-end">
                                                <a href="{{ route('professor.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Voir le dossier</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm gcd-hero text-white h-100">
                <div class="card-body d-flex flex-column">
                    <p class="text-uppercase small fw-semibold mb-2" style="letter-spacing:.15em; color:#eafff0;">Mes sujets</p>
                    <h3 class="h5 fw-bold">Gérer mes sujets</h3>
                    <p class="text-white-50 flex-grow-1">Consultez vos sujets et mettez à jour leur description, le profil recherché et leur ouverture aux candidatures.</p>
                    <a href="{{ route('professor.subjects.index') }}" class="btn btn-light mt-3">
                        <i class="bi bi-journal-richtext me-1"></i> View Mes sujets
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
