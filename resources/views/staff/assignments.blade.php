<x-app-layout title="Affectations du personnel">
    <form method="post" action="{{ route('admin.staff.store') }}" class="card card-body mb-3">
        @csrf
        @foreach ($errors->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
        <div class="row g-3">
            <label class="col-md-4">Compte
                <select class="form-select" name="user_id" required>
                    @foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>@endforeach
                </select>
            </label>
            <label class="col-md-4">Fonction
                <select class="form-select" name="function" required>
                    @foreach (\App\Enums\StaffFunction::cases() as $function)<option value="{{ $function->value }}">{{ $function->label() }}</option>@endforeach
                </select>
            </label>
            <label class="col-md-4">Sujet
                <select class="form-select" name="research_subject_id" required>
                    @foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->title }}</option>@endforeach
                </select>
            </label>
        </div>
        <button class="btn gcd-action align-self-start mt-3">Attribuer la fonction</button>
    </form>
    <section class="card card-body">
        <h2 class="h5">Affectations en cours</h2>
        @forelse ($assignments as $assignment)
            <div class="d-flex flex-wrap justify-content-between align-items-center border-top py-2 gap-2">
                <div><strong>{{ $assignment->user->name }}</strong> · {{ $assignment->function->label() }}<br>{{ $assignment->subject->title }}</div>
                <form method="post" action="{{ route('admin.staff.destroy', $assignment) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-secondary btn-sm">Retirer</button>
                </form>
            </div>
        @empty
            <p class="mb-0">Aucune affectation enregistrée.</p>
        @endforelse
        {{ $assignments->links() }}
    </section>
</x-app-layout>
