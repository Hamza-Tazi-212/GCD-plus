<x-app-layout title="Candidatures reçues">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="search" value="Rechercher par nom" />
                    <x-text-input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Nom du candidat" />
                </div>
                <div class="col-md-3">
                    <x-input-label for="subject_id" value="Sujet de thèse" />
                    <select id="subject_id" name="subject_id" class="form-select">
                        <option value="">Tous les sujets</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="status" value="Statut" />
                    <select id="status" name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="campaign" value="Campagne" />
                    <select id="campaign" name="campaign" class="form-select">
                        <option value="">Toutes les campagnes</option>
                        @foreach ($campaigns as $campaign)
                            <option @selected(request('campaign') === $campaign)>{{ $campaign }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-3 align-items-center">
                    <a href="{{ route('professor.applications.index') }}">Réinitialiser</a>
                    <button type="submit" class="btn gcd-action"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($applications->isEmpty())
                <p class="text-muted mb-0 text-center py-4">Aucune candidature ne correspond aux filtres.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Candidat</th>
                                <th>Sujet</th>
                                <th>Campagne</th>
                                <th>Statut</th>
                                <th>Date d’envoi</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                <tr>
                                    <td>{{ $application->fullName() }}</td>
                                    <td>{{ $application->subject->title }}</td>
                                    <td>{{ $application->campaign }}</td>
                                    <td><x-status-badge :status="$application->status" /></td>
                                    <td class="text-muted small">{{ $application->submitted_at?->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y') }}</td>
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
</x-app-layout>
