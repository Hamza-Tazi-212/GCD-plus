@extends('public-layout')
@section('title', 'Calendrier')
@section('content')
<main class="container py-4">
    <h1>Calendrier des campagnes</h1>
    <p>Dates de candidature, d’inscription et de réinscription. Les dates de clôture sont incluses et les périodes suivent l’heure de Fès.</p>
    @include('partials.campaign-periods')
</main>
@endsection
