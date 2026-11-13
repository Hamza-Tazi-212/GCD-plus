<x-app-layout title="Notifications">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="mb-0 text-muted">{{ $unreadCount }} {{ $unreadCount === 1 ? 'notification non lue' : 'notifications non lues' }}</p>
        @if ($unreadCount > 0)
            <form method="post" action="{{ route('candidate.notifications.read-all') }}">
                @csrf
                <button class="btn btn-outline-primary btn-sm">Tout marquer comme lu</button>
            </form>
        @endif
    </div>

    <div class="list-group mb-3">
        @forelse ($notifications as $notification)
            <article class="list-group-item d-flex justify-content-between align-items-start gap-3 flex-wrap py-3">
                <div>
                    <h2 class="h6 mb-1">{{ $notification->data['title'] }}
                        @unless ($notification->read_at)<span class="badge text-bg-primary ms-1">Non lue</span>@endunless
                    </h2>
                    <p class="mb-1">{{ $notification->data['subject'] }}</p>
                    <time class="text-muted small" datetime="{{ $notification->created_at->toIso8601String() }}">
                        {{ $notification->created_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y à H:i') }}
                    </time>
                </div>
                <form method="post" action="{{ route('candidate.notifications.open', $notification->id) }}">
                    @csrf
                    <button class="btn btn-outline-primary btn-sm">Ouvrir le dossier</button>
                </form>
            </article>
        @empty
            <p class="list-group-item text-muted mb-0">Vous n’avez reçu aucune notification.</p>
        @endforelse
    </div>

    {{ $notifications->links() }}
</x-app-layout>
