@extends('public-layout')
@section('title', $subject->title)
@section('content')
<main class="container py-4">
    <a href="{{ route('home') }}">Retour au catalogue</a>
    <div class="row g-4 mt-2">
        <section class="col-lg-8">
            <h1>{{ $subject->title }}</h1>
            <p>{{ $subject->description }}</p>
            @if ($subject->responsibilities)
                <h2 class="h5">Travail attendu</h2>
                <p>{{ $subject->responsibilities }}</p>
            @endif
            @if ($subject->candidate_profile)
                <h2 class="h5">Profil recherché</h2>
                <p>{{ $subject->candidate_profile }}</p>
            @endif
        </section>
        <aside class="col-lg-4">
            <div class="card card-body">
                <p><strong>Formation doctorale</strong><br>{{ $subject->department->name }}</p>
                @if ($subject->laboratory)<p><strong>Laboratoire</strong><br>{{ $subject->laboratory }}</p>@endif
                @if ($subject->field)<p><strong>Domaine</strong><br>{{ $subject->field }}</p>@endif
                <p><strong>Encadrement</strong><br>{{ $subject->professor->name }}</p>
                <p class="mb-2"><strong>Campagne {{ $subject->campaign }}</strong></p>
                @include('partials.campaign-deadline')
                <div class="mb-2">@include('partials.basket-add')</div>
                @if ($period && ! $period->isOpen())
                    <a href="{{ route('calendar') }}">Consulter le calendrier</a>
                @elseif (auth()->guest())
                    <a class="btn gcd-action" href="{{ route('candidate.applications.create', $subject) }}">Se connecter pour postuler</a>
                @else
                    <a class="btn btn-outline-primary" href="{{ route('candidate.subjects.show', $subject) }}">Ouvrir dans mon espace</a>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection
