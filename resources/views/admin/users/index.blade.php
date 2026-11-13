<x-app-layout title="Gérer les comptes">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
            <div class="col-md-3">
                <x-input-label for="search" value="Rechercher" />
                <x-text-input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou adresse e-mail…" />
            </div>
            <div class="col-md-2">
                <x-input-label for="role" value="Rôle" />
                <select id="role" name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <option value="admin" @selected(request('role') === 'admin')>Administrateur</option>
                    <option value="professor" @selected(request('role') === 'professor')>Encadrant</option>
                    <option value="candidate" @selected(request('role') === 'candidate')>Candidat</option>
                    <option value="staff" @selected(request('role') === 'staff')>Personnel</option>
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
            <div class="col-md-2">
                <x-input-label for="status" value="Statut" />
                <select id="status" name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrer</button>
            </div>
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus me-1"></i> New User
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Nom</th>
                            <th>Adresse e-mail</th>
                            <th>Rôle</th>
                            <th>Formation doctorale</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge {{ $user->role->badgeClass() }}">{{ $user->role->label() }}</span></td>
                                <td>{{ $user->department->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}">
                                                    {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete-user-{{ $user->id }}">
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @if ($user->id !== auth()->id())
                                <x-modal id="delete-user-{{ $user->id }}" title="Supprimer {{ $user->name }} ?">
                                    <p class="mb-0">La suppression définitive du compte effacera les données associées, notamment les sujets et les candidatures.</p>
                                    <x-slot name="footer">
                                        <x-secondary-button data-bs-dismiss="modal">Annuler</x-secondary-button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button>Supprimer définitivement</x-danger-button>
                                        </form>
                                    </x-slot>
                                </x-modal>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
