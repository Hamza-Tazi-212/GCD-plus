<x-app-layout title="Nouveau sujet de thèse">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <p class="text-muted small mb-4">Affectez le sujet à un encadrant et à une formation doctorale. L’encadrant complétera la fiche avant sa validation et sa publication.</p>

            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="professor_id" value="Encadrant" />
                        <select id="professor_id" name="professor_id" class="form-select" required>
                            <option value="">Sélectionnez un encadrant…</option>
                            @foreach ($professors as $professor)
                                <option value="{{ $professor->id }}" @selected(old('professor_id') == $professor->id)>
                                    {{ $professor->name }} &mdash; {{ $professor->email }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('professor_id')" />
                    </div>

                    <div class="col-md-6">
                        <x-input-label for="department_id" value="Formation doctorale" />
                        <select id="department_id" name="department_id" class="form-select" required>
                            <option value="">Sélectionnez une formation doctorale…</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('department_id')" />
                    </div>

                    <div class="col-12">
                        <x-input-label for="title" value="Titre du sujet" />
                        <x-text-input id="title" name="title" value="{{ old('title') }}" required />
                        <x-input-error :messages="$errors->get('title')" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="campaign" value="Campagne (ex. 2026–2027)" />
                        <x-text-input id="campaign" name="campaign" value="{{ old('campaign', '2026–2027') }}" required />
                        <x-input-error :messages="$errors->get('campaign')" />
                    </div>
                    @include('partials.subject-classification-fields')
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary px-4">Créer le sujet</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
