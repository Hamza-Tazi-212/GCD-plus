<x-app-layout title="Dossier de candidature">
    <a href="{{ route('professor.applications.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold mb-3" style="color:#005292;">
        <i class="bi bi-arrow-left"></i> Retour aux candidatures
    </a>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Candidature au sujet {{ $application->subject->title }}</p>
            <h2 class="h4 fw-bold mb-0">{{ $application->fullName() }}</h2>
        </div>
        <x-status-badge :status="$application->status" class="fs-6 px-3 py-2" />
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Informations personnelles</h3>
                </div>
                <div class="card-body">
                    @php($profile = $application->candidate->profile)
                    <div class="row g-3 small">
                        <div class="col-md-6"><span class="text-muted">Nom complet :</span> {{ $application->fullName() }}</div>
                        <div class="col-md-6"><span class="text-muted">Adresse e-mail :</span> {{ $application->candidate->email }}</div>
                        <div class="col-md-6"><span class="text-muted">Date de naissance :</span> {{ $profile?->birth_date?->format('d/m/Y') }}</div>
                        <div class="col-md-6"><span class="text-muted">Lieu de naissance :</span> {{ $profile?->birth_place }}</div>
                        <div class="col-md-6"><span class="text-muted">Nationalité :</span> {{ $profile?->nationality }}</div>
                        <div class="col-md-6"><span class="text-muted">Sexe :</span> {{ $profile?->gender ? match ($profile->gender) { 'male' => 'Masculin', 'female' => 'Féminin', default => $profile->gender } : null }}</div>
                        <div class="col-md-6"><span class="text-muted">CIN ou passeport :</span> {{ $profile?->cin_or_passport_number }}</div>
                        <div class="col-md-6"><span class="text-muted">Téléphone :</span> {{ $profile?->phone }}</div>
                        <div class="col-md-6"><span class="text-muted">Adresse :</span> {{ $profile?->address }}</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Parcours académique</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-md-6"><span class="text-muted">Dernier diplôme :</span> {{ $profile?->last_degree }}</div>
                        <div class="col-md-6"><span class="text-muted">Établissement :</span> {{ $profile?->last_institution }}</div>
                        <div class="col-md-6"><span class="text-muted">Année d’obtention :</span> {{ $profile?->graduation_year }}</div>
                        <div class="col-md-6"><span class="text-muted">Spécialité :</span> {{ $profile?->field_of_study }}</div>
                        @if ($profile?->grade_mention)
                            <div class="col-md-6"><span class="text-muted">Mention :</span> {{ $profile->grade_mention }}</div>
                        @endif
                        @if ($application->motivation_summary)
                            <div class="col-12 mt-2">
                                <span class="text-muted d-block mb-1">Résumé de la motivation :</span>
                                <p class="mb-0">{{ $application->motivation_summary }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-file-earmark-pdf me-2 text-primary"></i>Pièces déposées</h3>
                    <p class="small text-muted mb-0">Cliquez sur une pièce pour la consulter dans le navigateur.</p>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($application->documents as $document)
                            <x-pdf-link :document="$document" />
                        @empty
                            <p class="text-muted mb-0">Aucune pièce déposée.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($application->review_comment)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-chat-square-text me-2 text-primary"></i>Commentaire sur le dossier</h3>
                        <p class="mb-0 small">{{ $application->review_comment }}</p>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historique du dossier</h3>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        @foreach ($application->statusLogs as $log)
                            <li>
                                <p class="small fw-semibold mb-1">{{ $log->to_status->label() }}</p>
                                <p class="small text-muted mb-1">
                                    {{ $log->created_at->timezone('Africa/Casablanca')->format('d/m/Y à H:i') }}
                                    @if ($log->changedBy) &middot; {{ $log->changedBy->name }} @endif
                                </p>
                                @if ($log->comment)
                                    <p class="small mb-0">{{ $log->comment }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
