@extends('public-layout')
@section('title', 'Panier')
@section('content')
<main class="container py-4">
    <a href="{{ route('home') }}">Retour au catalogue</a>
    <h1 class="mt-3">Votre panier</h1>
    <p>Vous pouvez envoyer jusqu’à trois candidatures par campagne. Remplissez un dossier pour chaque sujet choisi.</p>
    @if ($subjects->isEmpty() && $unavailable === [])
        <p>Votre panier est vide.</p>
    @endif
    <div class="list-group">
        @foreach ($subjects as $subject)
            @php($period = $applicationPeriods->get($subject->campaign))
            <div class="list-group-item d-flex flex-wrap align-items-center gap-3 py-3">
                <div class="me-auto">
                    <a class="fw-semibold" href="{{ route('public.subjects.show', $subject) }}">{{ $subject->title }}</a><br>
                    <small>{{ $subject->department->name }} · Campagne {{ $subject->campaign }}</small>
                    @include('partials.campaign-deadline')
                </div>
                @if ((! $period || $period->isOpen()) && (auth()->guest() || auth()->user()->isCandidate()))
                    <a class="btn gcd-action btn-sm" href="{{ route('candidate.applications.create', $subject) }}">Postuler</a>
                @endif
                @include('partials.basket-remove', ['id' => $subject->id])
            </div>
        @endforeach
        @foreach ($unavailable as $id)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <span>Sujet indisponible</span>
                @include('partials.basket-remove', ['id' => $id])
            </div>
        @endforeach
    </div>
</main>
@endsection
