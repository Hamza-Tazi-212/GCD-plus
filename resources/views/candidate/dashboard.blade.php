<x-app-layout title="Tableau de bord candidat">
    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('candidate.notifications.index') }}">
            Notifications
            @if ($unreadCount > 0)
                <span class="badge text-bg-primary">{{ $unreadCount }} {{ $unreadCount === 1 ? 'non lue' : 'non lues' }}</span>
            @endif
        </a>
    </div>
    @if ($actions !== [])
        <section class="card border-0 shadow-sm mb-4" aria-labelledby="candidate-actions">
            <div class="card-header bg-white py-3">
                <h2 id="candidate-actions" class="h6 fw-bold mb-0">À faire dans mes dossiers</h2>
            </div>
            <div class="list-group list-group-flush">
                @foreach ($actions as $action)
                    <div class="list-group-item d-flex flex-wrap align-items-center gap-2 py-3">
                        <div class="me-auto">
                            <p class="fw-semibold mb-0">{{ $action['subject'] }}</p>
                            <p class="small text-muted mb-0">{{ $action['detail'] }}</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="{{ $action['url'] }}">{{ $action['title'] }}</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    <div class="row g-3 mb-4">
        @foreach ([
            ['Candidatures', $overview['total_applications'], 'bi-file-earmark-text', 'primary'],
            ['En attente', $overview['status_counts']['pending'], 'bi-hourglass-split', 'secondary'],
            ['Admissibles à l’oral', $overview['status_counts']['under_review'], 'bi-calendar-event', 'primary'],
            ['Retenues', $overview['status_counts']['accepted'], 'bi-check-circle-fill', 'success'],
        ] as [$label, $value, $icon, $color])
            <div class="col-sm-6 col-xl-3">
                <x-stat-card :label="$label" :value="$value" :icon="$icon" :color="$color" />
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <section class="col-lg-8" aria-labelledby="recent-applications">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2 py-3">
                    <h2 class="h6 fw-bold mb-0" id="recent-applications">Candidatures récentes</h2>
                    <a href="{{ route('candidate.applications.index') }}">Tout voir</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($overview['applications']->take(5) as $application)
                        <div class="list-group-item d-flex flex-wrap align-items-center gap-2 py-3">
                            <div class="me-auto">
                                <a class="fw-semibold" href="{{ route('candidate.applications.show', $application) }}">{{ $application->subject->title }}</a>
                                <div class="small text-muted">Campagne {{ $application->campaign }}
                                    @if ($application->submitted_at)
                                        · Envoyée le {{ $application->submitted_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y') }}
                                    @endif
                                </div>
                            </div>
                            <x-status-badge :status="$application->status" />
                        </div>
                    @empty
                        <p class="p-3 text-muted mb-0">Vous n’avez encore envoyé aucune candidature.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="col-lg-4 d-flex flex-column gap-3">
            @if ($interviews->isNotEmpty())
                <section class="card card-body border-0 shadow-sm">
                    <h2 class="h6 fw-bold">Prochains entretiens</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach ($interviews as $interview)
                            <li class="mt-2">
                                <a href="{{ route('candidate.applications.show', $interview) }}">{{ $interview->subject->title }}</a>
                                <div class="small text-muted">{{ $interview->oral_exam_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y à H:i') }}</div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
            <section class="card card-body border-0 shadow-sm">
                <h2 class="h6 fw-bold">Préparer une candidature</h2>
                @if (! $overview['profile_complete'])
                    <p class="text-muted">Renseignez vos informations personnelles et académiques avant de postuler.</p>
                    <a href="{{ route('candidate.profile.overview') }}" class="btn gcd-action align-self-start">Compléter mon profil</a>
                @elseif (session('basket', []) !== [])
                    <p class="text-muted">Retrouvez votre sélection pour préparer vos candidatures.</p>
                    <a href="{{ route('basket') }}" class="btn gcd-action align-self-start">Voir mon panier</a>
                @else
                    <p class="text-muted">Parcourez les sujets de doctorat ouverts.</p>
                    <a href="{{ route('home') }}" class="btn gcd-action align-self-start">Consulter les sujets</a>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
