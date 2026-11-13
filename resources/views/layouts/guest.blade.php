<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Mon compte' }} · GCD+</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('gcd-public.css') }}">
</head>
<body>
@include('partials.public-nav')
<main class="container py-5">
    <div class="auth-card mx-auto">{{ $slot }}</div>
</main>
@include('partials.public-footer')
</body>
</html>
