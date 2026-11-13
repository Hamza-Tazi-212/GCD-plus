<nav class="nav flex-column gap-1">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
    </a>
    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <i class="bi bi-people me-2"></i>Comptes
    </a>
    <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">
        <i class="bi bi-building me-2"></i>Formations doctorales
    </a>
    <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}" href="{{ route('admin.subjects.index') }}">
        <i class="bi bi-journal-richtext me-2"></i>Sujets de thèse
    </a>
    <a class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}">
        <i class="bi bi-file-earmark-text me-2"></i>Candidatures
    </a>
    <a class="nav-link {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}" href="{{ route('admin.feedback.index') }}">
        <i class="bi bi-inboxes me-2"></i>Remarques reçues
    </a>
    <a class="nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.index') }}">
        <i class="bi bi-chat-square-text me-2"></i>Vos remarques
    </a>
    <a class="nav-link {{ request()->routeIs('renewals.*') ? 'active' : '' }}" href="{{ route('renewals.index') }}">
        <i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Réinscriptions
    </a>
    <a class="nav-link" href="{{ route('admin.staff.index') }}">Affectations du personnel</a>
    <a class="nav-link" href="{{ route('admin.calendar.edit') }}">Calendrier des campagnes</a>
    <hr>
    <a class="nav-link" href="{{ route('home') }}">
        <i class="bi bi-globe me-2"></i>Accueil public
    </a>
</nav>
