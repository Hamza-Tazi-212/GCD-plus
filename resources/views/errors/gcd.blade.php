<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · GCD+</title>
    @include('partials.favicons')
    <style>
        body{margin:0;background:#f3f6fb;color:#112b51;font:1rem/1.5 system-ui,sans-serif;border-top:4px solid #a81d3b}
        main{max-width:36rem;margin:12vh auto;padding:1.5rem}
        a{color:#17487b}
    </style>
</head>
<body>
<main>
    <p>GCD+ · EST de Fès</p>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    <a href="{{ url('/') }}">Retour à l’accueil</a>
</main>
</body>
</html>
