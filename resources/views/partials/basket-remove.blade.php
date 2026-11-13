<form method="post" action="{{ route('basket.remove', $id) }}">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline-secondary btn-sm">Retirer</button>
</form>
