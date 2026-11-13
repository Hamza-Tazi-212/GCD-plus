<x-app-layout title="Mes sujets de thèse">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-10">
                    <x-input-label for="search" value="Rechercher par titre" />
                    <x-text-input id="search" type="text" name="search" value="{{ request('search') }}" />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($subjects->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-journal-richtext display-4 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">
                        @if (request('search'))
                            Aucun sujet ne correspond à votre recherche.
                        @else
                            Aucun sujet ne vous est affecté. Contactez l’administration pour en ajouter un.
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Titre</th>
                                <th>Formation doctorale</th>
                                <th>Candidatures</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $subject)
                                <tr>
                                    <td class="fw-semibold">{{ $subject->title }}</td>
                                    <td>{{ $subject->department->name }}</td>
                                    <td>{{ $subject->applications_count }}</td>
                                    <td>
                                        <span class="badge {{ $subject->is_open ? 'bg-success' : 'bg-danger' }}">
                                            {{ $subject->is_open ? 'Ouvert' : 'Fermé' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('professor.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                        <a href="{{ route('professor.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $subjects->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
