@if (in_array($subject->id, session('basket', []), true))
    <a class="btn btn-outline-primary btn-sm" href="{{ route('basket') }}">Dans le panier</a>
@else
    <form method="post" action="{{ route('basket.add', $subject) }}">
        @csrf
        <button class="btn gcd-action btn-sm">Ajouter au panier</button>
    </form>
@endif
