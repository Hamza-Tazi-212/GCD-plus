<form method="post" enctype="multipart/form-data" action="{{ route('renewals.resubmit', $renewal) }}" class="mt-3">
    @csrf
    <input type="hidden" name="round" value="{{ count($renewal->correction_history) }}">
    <label class="form-label" for="report-fix-{{ $renewal->id }}">Bilan annuel corrigé</label>
    <textarea class="form-control mb-2" id="report-fix-{{ $renewal->id }}" name="report" rows="4" required maxlength="12000">{{ old('report', $renewal->report) }}</textarea>
    <label class="form-label" for="attachment-fix-{{ $renewal->id }}">Nouveau justificatif (facultatif, PDF de 5 Mo maximum)</label>
    <input class="form-control" type="file" id="attachment-fix-{{ $renewal->id }}" name="attachment" accept=".pdf,application/pdf">
    <x-document-capture :target="'attachment-fix-'.$renewal->id" />
    <button class="btn gcd-action mt-2">Renvoyer le dossier corrigé</button>
</form>
