<x-app-layout title="Nouveau compte">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users._form', ['user' => null])
            </form>
        </div>
    </div>
</x-app-layout>
