<x-app-layout title="Consulter les sujets de thèse">
    @include('partials.catalogue-filters', ['catalogueUrl' => route('candidate.subjects.index')])

    @if ($subjects->isEmpty())
        <div class="alert alert-info">Aucun sujet ne correspond à votre recherche.</div>
    @else
        <div class="row g-4">
            @foreach ($subjects as $subject)
                <div class="col-md-6 col-lg-4">
                    <div class="subject-card card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary-subtle text-primary mb-2 align-self-start">{{ $subject->department->name }}</span>
                            <h3 class="h5 fw-bold mb-2">{{ $subject->title }}</h3>
                            @if ($subject->laboratory || $subject->field)
                                <p class="small text-muted mb-2">{{ implode(' · ', array_filter([$subject->laboratory, $subject->field])) }}</p>
                            @endif
                            <p class="text-muted small mb-3 flex-grow-1">{{ \Illuminate\Support\Str::limit($subject->description, 110) }}</p>
                            <p class="small text-muted mb-3">
                                <i class="bi bi-person-badge me-1"></i>{{ $subject->professor->name }}
                            </p>
                            <a href="{{ route('candidate.subjects.show', $subject) }}" class="btn btn-outline-primary btn-sm mt-auto">Voir le détail</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $subjects->links() }}
        </div>
    @endif
</x-app-layout>
