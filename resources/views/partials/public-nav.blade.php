<nav class="navbar navbar-dark" style="background-color:#112b51">
    <div class="container gap-2">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">GCD<span style="color:#ffadb9">+</span> <small class="ms-2">EST de Fès · USMBA</small></a>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-light btn-sm" href="{{ route('calendar') }}">Calendrier</a>
            <a class="btn btn-outline-light btn-sm" href="{{ route('basket') }}">Panier ({{ count(session('basket', [])) }})</a>
        @auth
            <a class="btn btn-outline-light btn-sm" href="{{ route('dashboard') }}">Mon espace</a>
        @else
            <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Se connecter</a>
        @endauth
        </div>
    </div>
</nav>
