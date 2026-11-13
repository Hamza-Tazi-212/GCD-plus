<nav class="nav flex-column gap-1">
    <a class="nav-link {{ request()->routeIs('candidate.dashboard') ? 'active' : '' }}" href="{{ route('candidate.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
    </a>
    <a class="nav-link {{ request()->routeIs('candidate.subjects.*') ? 'active' : '' }}" href="{{ route('candidate.subjects.index') }}">
        <i class="bi bi-search me-2"></i>Catalogue des sujets
    </a>
    <a class="nav-link" href="{{ route('basket') }}">
        <i class="bi bi-basket me-2" aria-hidden="true"></i>Mon panier ({{ count(session('basket', [])) }})
    </a>
    <a class="nav-link {{ request()->routeIs('candidate.applications.*') ? 'active' : '' }}" href="{{ route('candidate.applications.index') }}">
        <i class="bi bi-file-earmark-text me-2"></i>Mes candidatures
    </a>
    <a class="nav-link {{ request()->routeIs('candidate.profile.*') ? 'active' : '' }}" href="{{ route('candidate.profile.overview') }}" hx-boost="true">
        <i class="bi bi-person-badge me-2"></i>Mon profil
        @unless (auth()->user()->profile?->isComplete())
            <span class="badge bg-warning-subtle text-warning ms-1">À compléter</span>
        @endunless
    </a>
    <a class="nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.index') }}">
        <i class="bi bi-chat-square-text me-2"></i>Vos remarques
    </a>
    <a class="nav-link {{ request()->routeIs('renewals.*') ? 'active' : '' }}" href="{{ route('renewals.index') }}">
        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Mes réinscriptions
    </a>
    <a class="nav-link {{ request()->routeIs('candidate.notifications.*') ? 'active' : '' }}" href="{{ route('candidate.notifications.index') }}">
        <i class="bi bi-bell me-2" aria-hidden="true"></i>Notifications
    </a>
    <hr>
    <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-globe me-2"></i>Accueil public
    </a>
</nav>
