<x-app-layout title="Sujets de thèse">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Subject
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="search" value="Rechercher par titre" />
                    <x-text-input id="search" type="text" name="search" value="{{ request('search') }}" />
                </div>
                <div class="col-md-3">
                    <x-input-label for="professor_id" value="Encadrant" />
                    <select id="professor_id" name="professor_id" class="form-select">
                        <option value="">Tous les encadrants</option>
                        @foreach ($professors as $professor)
                            <option value="{{ $professor->id }}" @selected(request('professor_id') == $professor->id)>{{ $professor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="department_id" value="Formation doctorale" />
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">Toutes les formations doctorales</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="status" value="Statut" />
                    <select id="status" name="status" class="form-select">
                        <option value="">Ouverts et fermés</option>
                        <option value="open" @selected(request('status') === 'open')>Ouvert</option>
                        <option value="closed" @selected(request('status') === 'closed')>Fermé</option>
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
                            <th>Titre</th>
                            <th>Encadrant</th>
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
                                <td>{{ $subject->professor->name }}</td>
                                <td>{{ $subject->department->name }}</td>
                                <td>{{ $subject->applications_count }}</td>
                                <td>
                                    <span class="badge {{ $subject->is_open ? 'bg-success' : 'bg-danger' }}">
                                        {{ $subject->is_open ? 'Ouvert' : 'Fermé' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer le sujet et toutes ses candidatures ?')">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $subjects->links() }}
        </div>
    </div>
</x-app-layout>
