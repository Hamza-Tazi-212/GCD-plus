<x-app-layout title="Dossier de candidature">
    <a href="{{ route('admin.applications.index') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none small fw-semibold mb-3" style="color:#005292;">
        <i class="bi bi-arrow-left"></i> Retour aux candidatures
    </a>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold mb-1" style="color:#44A66D;">Candidature au sujet {{ $application->subject->title }}</p>
            <h2 class="h4 fw-bold mb-0">{{ $application->fullName() }}</h2>
            @if ($application->oral_exam_at)
                <p class="small text-muted mb-0"><i class="bi bi-calendar-event me-1"></i>Entretien oral le {{ $application->oral_exam_at->timezone('Africa/Casablanca')->format('d/m/Y à H:i') }}</p>
            @endif
            @if ($application->program_start_at)
                <p class="small text-muted mb-0"><i class="bi bi-calendar-check me-1"></i>Début de la formation le {{ $application->program_start_at->format('d/m/Y') }}</p>
            @endif
        </div>
        <x-status-badge :status="$application->status" class="fs-6 px-3 py-2" />
    </div>

    @if ($application->notification_sent_at)
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-envelope-check-fill"></i>
            <span>Notification envoyée au candidat le {{ $application->notification_sent_at->timezone('Africa/Casablanca')->format('d/m/Y à H:i') }}.</span>
        </div>
    @elseif ($application->notification_error)
        <div class="alert alert-danger mb-4" role="alert">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-envelope-exclamation-fill mt-1"></i>
                    <div>
                        <div class="fw-semibold">L’envoi de la notification par e-mail a échoué.</div>
                        <div class="small text-muted">{{ $application->notification_error }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.applications.resend-notification', $application) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-clockwise me-1"></i>Renvoyer</button>
                </form>
            </div>
        </div>
    @elseif ($application->notificationPending())
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
            <span><i class="bi bi-hourglass-split me-2"></i>La notification par e-mail est en attente d’envoi.</span>
            <form method="POST" action="{{ route('admin.applications.resend-notification', $application) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-clockwise me-1"></i>Envoyer maintenant</button>
            </form>
        </div>
    @endif

    @include('renewals.enrolment-confirm')
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
                </div>
                <div class="card-body">
                    @include('partials.application-documents')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-2">Sujet de thèse</h3>
                    <p class="mb-1">{{ $application->subject->title }}</p>
                    <p class="small text-muted mb-1">Encadré par {{ $application->subject->professor->name }}</p>
                    <p class="small text-muted mb-0">{{ $application->subject->department->name }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-clipboard-check me-2 text-primary"></i>Décision sur le dossier</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.applications.review', $application) }}">
                        @csrf
                        @method('PATCH')

                        @php($selectedStatus = old('status', $application->status->value))

                        <div class="mb-3">
                            <x-input-label for="status" value="Nouveau statut" />
                            <select id="status" name="status" class="form-select" required>
                                @foreach ($statuses as $status)
                                    @if ($status->value !== 'pending')
                                        <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="waitlist_rank" value="Rang (obligatoire pour la liste d’attente)" />
                            <x-text-input id="waitlist_rank" type="number" name="waitlist_rank" min="1" max="999999"
                                value="{{ old('waitlist_rank', $application->waitlist_rank) }}" />
                            <x-input-error :messages="$errors->get('waitlist_rank')" />
                        </div>
                        <div class="mb-3 {{ $selectedStatus === 'under_review' ? '' : 'd-none' }}" id="oral-exam-date-field">
                            <x-input-label for="oral_exam_date" value="Date et heure de l’entretien" />
                            <x-text-input id="oral_exam_date" type="datetime-local" name="oral_exam_date"
                                value="{{ old('oral_exam_date', optional($application->oral_exam_at)->format('Y-m-d\TH:i')) }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}" :required="$selectedStatus === 'under_review'" />
                            <x-input-error :messages="$errors->get('oral_exam_date')" />
                        </div>

                        <div class="mb-3 {{ $selectedStatus === 'accepted' ? '' : 'd-none' }}" id="program-start-date-field">
                            <x-input-label for="program_start_date" value="Date de début de la formation" />
                            <x-text-input id="program_start_date" type="date" name="program_start_date"
                                value="{{ old('program_start_date', optional($application->program_start_at)->format('Y-m-d')) }}"
                                min="{{ now()->toDateString() }}" :required="$selectedStatus === 'accepted'" />
                            <x-input-error :messages="$errors->get('program_start_date')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="comment" value="Commentaire (facultatif)" />
                            <textarea id="comment" name="comment" rows="4" class="form-control" placeholder="Saisissez un commentaire pour le candidat…">{{ old('comment') }}</textarea>
                            <x-input-error :messages="$errors->get('comment')" />
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2-circle me-1"></i> Enregistrer le statut
                        </button>
                    </form>
                </div>
            </div>

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
                    <h3 class="h6 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historique des statuts</h3>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        @foreach ($application->statusLogs as $log)
                            <li>
                                <p class="small fw-semibold mb-1">{{ $log->to_status->label() }}@if($log->waitlist_rank) · Rang {{ $log->waitlist_rank }}@endif</p>
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
