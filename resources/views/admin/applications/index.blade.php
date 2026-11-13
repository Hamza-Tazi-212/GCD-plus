<x-app-layout title="Candidatures">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <x-input-label for="search" value="Rechercher par candidat ou par sujet" />
                    <x-text-input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Nom du candidat ou titre du sujet…" />
                </div>
                <div class="col-md-4">
                    <x-input-label for="professor_id" value="Encadrant" />
                    <select id="professor_id" name="professor_id" class="form-select">
                        <option value="">Tous les encadrants</option>
                        @foreach ($professors as $professor)
                            <option value="{{ $professor->id }}" @selected(request('professor_id') == $professor->id)>{{ $professor->name }}</option>
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
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Candidat</th>
                            <th>Sujet</th>
                            <th>Encadrant</th>
                            <th>Statut</th>
                            <th>Envoyée le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr>
                                <td>{{ $application->fullName() }}</td>
                                <td>{{ $application->subject->title }}</td>
                                <td>{{ $application->subject->professor->name }}</td>
                                <td><x-status-badge :status="$application->status" /></td>
                                <td class="text-muted small">{{ $application->submitted_at?->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $applications->links() }}
        </div>
    </div>
</x-app-layout>
