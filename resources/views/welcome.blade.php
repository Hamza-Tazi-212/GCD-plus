@extends('public-layout')
@section('title', 'Sujets de doctorat')
@section('content')
<main>
    <section class="gcd-hero py-5">
        <div class="container">
            <p class="fw-semibold mb-2">École Supérieure de Technologie de Fès</p>
            <h1 class="display-5 fw-bold">Trouver un sujet de doctorat</h1>
            <p class="mb-0">Consultez les sujets ouverts et découvrez les profils recherchés.</p>
        </div>
    </section>
    <div class="container py-4">
        @include('partials.catalogue-filters', ['catalogueUrl' => route('home')])

        <h2 class="h3">Sujets proposés</h2>
        <p class="text-muted">{{ $subjects->total() }} {{ $subjects->total() === 1 ? 'sujet' : 'sujets' }}</p>
        <div class="row g-3">
            @forelse ($subjects as $subject)
                <div class="col-md-6 col-lg-4">
                    <article class="card subject-card h-100">
                        <div class="card-body d-flex flex-column gap-2">
                            <span class="badge bg-primary-subtle text-primary align-self-start">{{ $subject->department->name }}</span>
                            <h3 class="h5 mb-0">{{ $subject->title }}</h3>
                            @if ($subject->laboratory || $subject->field)
                                <p class="small text-muted mb-2">{{ implode(' · ', array_filter([$subject->laboratory, $subject->field])) }}</p>
                            @endif
                            <p class="text-muted">{{ \Illuminate\Support\Str::limit($subject->description, 130) }}</p>
                            <p class="small">{{ $subject->professor->name }}</p>
                            @include('partials.campaign-deadline', ['period' => $applicationPeriods->get($subject->campaign)])
                            <div class="d-flex gap-2 flex-wrap mt-auto">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('public.subjects.show', $subject) }}">Voir le sujet</a>
                                @include('partials.basket-add')
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <p>Aucun sujet ne correspond à votre recherche.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $subjects->links() }}</div>
    </div>
</main>
@endsection
