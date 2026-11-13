@extends('public-layout')
@section('title', 'À propos de GCD+')
@section('content')
<main class="container py-4">
    <h1>À propos de GCD+</h1>
    <p>GCD+ est un projet de gestion du cycle doctoral pour l’École Supérieure de Technologie de Fès, rattachée à l’Université Sidi Mohamed Ben Abdellah.</p>
    <p>Le portail permet de consulter les sujets, de déposer et suivre les candidatures, puis de gérer l’inscription et la réinscription.</p>
    <a class="btn gcd-action" href="{{ route('home') }}">Consulter les sujets</a>
</main>
@endsection
