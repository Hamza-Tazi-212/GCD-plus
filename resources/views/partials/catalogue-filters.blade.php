<form action="{{ $catalogueUrl }}" method="get" class="gcd-catalogue-filters row g-3 align-items-end rounded-3 p-3 mx-0 mb-3">
    <div class="col-lg-3 col-sm-6">
        <label class="form-label" for="search">Rechercher un sujet</label>
        <input id="search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Titre ou mot-clé" maxlength="160">
    </div>
    @foreach (['department_id' => ['Formation doctorale', 'Toutes les formations'], 'laboratory' => ['Laboratoire', 'Tous les laboratoires'], 'field' => ['Domaine', 'Tous les domaines']] as $key => [$label, $empty])
        <div class="col-lg-3 col-sm-6">
            <label class="form-label" for="{{ $key }}">{{ $label }}</label>
            <select id="{{ $key }}" name="{{ $key }}" class="form-select">
                <option value="">{{ $empty }}</option>
                @foreach ($options[$key] as $value => $text)
                    <option value="{{ $value }}" @selected((string) ($filters[$key] ?? '') === (string) $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
    <div class="col-12 d-flex flex-wrap gap-2">
        <button class="btn gcd-action">Rechercher</button>
        @if ($activeFilters)
            <a class="btn btn-outline-primary" href="{{ $catalogueUrl }}">Réinitialiser</a>
        @endif
    </div>
</form>
@if ($activeFilters)
    <ul class="list-unstyled d-flex gap-2 flex-wrap mb-4" aria-label="Filtres actifs">
        @foreach ($activeFilters as $label => $value)
            <li class="badge text-bg-primary text-wrap">{{ $label }} · {{ $value }}</li>
        @endforeach
    </ul>
@endif
