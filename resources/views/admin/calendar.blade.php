<x-app-layout title="Calendrier des campagnes">
    <form class="card card-body mb-4" method="post" action="{{ route('admin.calendar.store') }}">
        @csrf
        <p class="text-muted">Pour modifier une période, renseignez la même campagne et la même étape.</p>
        @foreach ($errors->all() as $error)<p class="text-danger" role="alert">{{ $error }}</p>@endforeach
        <div class="row g-3">
            <label class="col-md-3">Campagne
                <input class="form-control" name="academic_year" value="{{ old('academic_year') }}" placeholder="2026–2027" required>
            </label>
            <label class="col-md-3">Étape
                <select class="form-select" name="phase" required>
                    @foreach (\App\Models\CampaignPeriod::PHASES as $key => $label)
                        <option value="{{ $key }}" @selected(old('phase') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="col-md-3">Ouverture<input class="form-control" name="opens_on" type="date" value="{{ old('opens_on') }}" required></label>
            <label class="col-md-3">Clôture<input class="form-control" name="closes_on" type="date" value="{{ old('closes_on') }}" required></label>
        </div>
        <button class="btn gcd-action mt-3 align-self-start">Enregistrer la période</button>
    </form>
    @include('partials.campaign-periods')
    @include('admin.campaign-checklist')
</x-app-layout>
