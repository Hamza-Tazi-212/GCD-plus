<nav class="nav flex-column gap-1">
    <a class="nav-link {{ request()->routeIs('professor.dashboard') ? 'active' : '' }}" href="{{ route('professor.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
    </a>
    <a class="nav-link {{ request()->routeIs('professor.subjects.*') ? 'active' : '' }}" href="{{ route('professor.subjects.index') }}">
        <i class="bi bi-journal-richtext me-2"></i>Mes sujets
    </a>
    <a class="nav-link {{ request()->routeIs('professor.applications.*') ? 'active' : '' }}" href="{{ route('professor.applications.index') }}">
        <i class="bi bi-file-earmark-text me-2"></i>Candidatures reçues
    </a>
    <a class="nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.index') }}">
        <i class="bi bi-chat-square-text me-2"></i>Vos remarques
    </a>
    <a class="nav-link d-flex align-items-center" href="{{ route('profile.edit') }}#update-password-form">
        <i class="bi bi-key me-2"></i>Changer le mot de passe
        @if (auth()->user()->needsPasswordChange())
            <i class="bi bi-exclamation-triangle-fill text-warning ms-2" title="Pensez à changer votre mot de passe initial."></i>
        @endif
    </a>
    <a class="nav-link {{ request()->routeIs('renewals.*') ? 'active' : '' }}" href="{{ route('renewals.index') }}">
        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Réinscriptions
    </a>
    <hr>
    <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-globe me-2"></i>Accueil public
    </a>
</nav>
