<x-app-layout title="Pièces d’inscription administrative">
    <p class="fw-semibold">{{ $application->subject->title }} · {{ $application->campaign }}</p>
    @foreach ($errors->all() as $error)<p class="alert alert-danger" role="alert">{{ $error }}</p>@endforeach
    @include('partials.campaign-deadline')
    <p><a href="{{ route('calendar') }}">Consulter le calendrier des campagnes</a></p>
    @php($confirmed = $application->doctoralEnrolment)
    @foreach ($checklist as $kind => $label)
        @php($document = $documents->get($kind))
        <section class="card card-body mb-3">
            <h2 class="h6">{{ $label }}</h2>
            <p>{{ match ($document?->status) { 'approved' => 'Conforme', 'correction' => 'À remplacer', 'pending' => 'En attente de contrôle', default => 'À déposer' } }}</p>
            @if ($document)
                <p><a href="{{ route('enrolment-documents.preview', [$application, $document]) }}">Consulter le PDF</a></p>
                @if ($document->comment)<p>{{ $document->comment }}</p>@endif
            @endif
            @if (! $confirmed && ! $manager && ((! $document && (! $period || $period->isOpen())) || $document?->status === 'correction'))
                <form method="post" enctype="multipart/form-data" action="{{ route('enrolment-documents.upload', [$application, $kind]) }}">
                    @csrf
                    <label class="form-label" for="file-{{ $kind }}">Document PDF · 5 Mo maximum</label>
                    <input class="form-control mb-2" id="file-{{ $kind }}" name="document" type="file" accept="application/pdf,.pdf" required>
                    <x-document-capture :target="'file-'.$kind" />
                    <button class="btn gcd-action btn-sm mt-2">{{ $document ? 'Remplacer la pièce' : 'Déposer la pièce' }}</button>
                </form>
            @elseif (! $confirmed && $manager && $document?->status === 'pending')
                <form method="post" action="{{ route('enrolment-documents.review', [$application, $document]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="version" value="{{ hash('sha256', $document->path) }}">
                    <label class="form-label" for="comment-{{ $kind }}">Commentaire (obligatoire pour demander un remplacement)</label>
                    <textarea class="form-control mb-2" id="comment-{{ $kind }}" name="comment" maxlength="2000"></textarea>
                    <button class="btn gcd-action btn-sm" name="status" value="approved">Valider la pièce</button>
                    <button class="btn btn-outline-secondary btn-sm" name="status" value="correction">Demander un remplacement</button>
                </form>
            @endif
        </section>
    @endforeach
    @if ($manager)
        @include('renewals.enrolment-confirm')
    @elseif ($confirmed)
        <p class="alert alert-success">Inscription confirmée pour {{ $confirmed->academic_year }}.</p>
    @endif
</x-app-layout>
