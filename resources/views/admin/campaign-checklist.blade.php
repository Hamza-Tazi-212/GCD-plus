<section class="card card-body mt-4">
    <h2 class="h5">Pièces obligatoires pour l’inscription administrative</h2>
    <p>Sans liste spécifique, la campagne utilise la pièce d’identité, le diplôme et la charte signée.</p>
    <form method="post" action="{{ route('admin.calendar.checklist') }}">
        @csrf
        <label class="form-label" for="checklist-campaign">Campagne</label>
        <input id="checklist-campaign" class="form-control mb-3" name="campaign" value="{{ old('campaign') }}" placeholder="2026–2027" required>
        <fieldset>
            <legend class="h6">Pièces à demander</legend>
            @foreach (\App\Models\EnrolmentDocument::AVAILABLE as $kind => $label)
                <label class="d-block mb-1">
                    <input type="checkbox" name="kinds[]" value="{{ $kind }}" @checked(in_array($kind, (array) old('kinds', array_keys(\App\Models\EnrolmentDocument::REQUIRED)), true))>
                    {{ $label }}
                </label>
            @endforeach
        </fieldset>
        <button class="btn gcd-action mt-2">Enregistrer les pièces</button>
    </form>
    @foreach ($checklists as $checklist)
        <p class="mt-3 mb-0"><strong>{{ $checklist->campaign }}</strong> · {{ implode(', ', array_intersect_key(\App\Models\EnrolmentDocument::AVAILABLE, array_flip($checklist->kinds))) }}</p>
    @endforeach
</section>
