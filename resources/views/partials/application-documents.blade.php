@php
    $user = auth()->user();
    $canRequest = $application->allowsDocumentCorrections() && ($user->isAdmin()
        || $user->hasStaffAssignment(\App\Enums\StaffFunction::Selection, $application->subject_id));
    $canReplace = $application->allowsDocumentCorrections() && $user->isCandidate() && $application->candidate_id === $user->id;
@endphp
@foreach (['document', 'reason', 'correction'] as $field)
    @foreach ($errors->get($field) as $message)<p class="text-danger" role="alert">{{ $message }}</p>@endforeach
@endforeach
@forelse ($application->documents as $document)
    @php($openCorrection = $document->corrections->firstWhere('resolved_at', null))
    <div class="border-top py-3" id="piece-{{ $document->id }}">
        <x-pdf-link :document="$document" />
        @if ($openCorrection)
            <p class="my-2"><strong>À remplacer</strong> · {{ $openCorrection->reason }}</p>
            @if ($canReplace)
                <form method="post" enctype="multipart/form-data" action="{{ route('application-documents.replace', $document) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="correction" value="{{ $openCorrection->id }}">
                    <label class="form-label" for="replacement-{{ $document->id }}">Nouveau PDF (5 Mo maximum)</label>
                    <input class="form-control" type="file" id="replacement-{{ $document->id }}" name="document" accept=".pdf,application/pdf" required>
                    <x-document-capture :target="'replacement-'.$document->id" />
                    <button class="btn gcd-action btn-sm mt-2">Renvoyer la pièce</button>
                </form>
            @endif
        @elseif ($canRequest)
            <details class="mt-2">
                <summary>Demander un remplacement</summary>
                <form class="mt-2" method="post" action="{{ route('application-documents.correction', $document) }}">
                    @csrf
                    <label class="form-label" for="reason-{{ $document->id }}">Motif de la demande</label>
                    <textarea class="form-control mb-2" id="reason-{{ $document->id }}" name="reason" required maxlength="2000"></textarea>
                    <button class="btn btn-outline-primary btn-sm">Envoyer la demande</button>
                </form>
            </details>
        @endif
        @if ($document->corrections->contains(fn ($correction) => $correction->resolved_at !== null))
            <details class="mt-2">
                <summary>Historique des remplacements</summary>
                <ul class="small">
                    @foreach ($document->corrections->whereNotNull('resolved_at') as $correction)
                        <li>{{ $correction->reason }} · Pièce renvoyée le {{ $correction->resolved_at->timezone('Africa/Casablanca')->format('d/m/Y H:i') }}</li>
                    @endforeach
                </ul>
            </details>
        @endif
    </div>
@empty
    <p class="text-muted mb-0">Aucune pièce déposée.</p>
@endforelse
