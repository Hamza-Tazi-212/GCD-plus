<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · GCD+</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('gcd-public.css') }}">
</head>
<body>
@include('partials.public-nav')
@if (session('basket_status'))
    <aside class="alert basket-notice mb-0" aria-label="Notification du panier">
        <span class="basket-notice-icon" aria-hidden="true">✓</span>
        <div>
            <p class="mb-1 fw-semibold" role="status">{{ session('basket_status') }}</p>
            <a class="basket-notice-link" href="{{ route('basket') }}">Voir le panier</a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer la notification"></button>
    </aside>
@endif
@yield('content')
@include('partials.public-footer')
</body>
</html>
