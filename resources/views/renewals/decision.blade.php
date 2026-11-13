<form method="post" action="{{ route('renewals.decide', $renewal) }}">
    @csrf
    <label class="form-label" for="decision-{{ $renewal->id }}">Motif de la décision</label>
    <textarea class="form-control mb-2" id="decision-{{ $renewal->id }}" name="comment" required maxlength="4000"></textarea>
    @if ($renewal->status === 'favorable' && $renewal->hasRequiredApprovals()
        && (! $renewal->extension || $renewal->extensionDecision()?->approved))
        <label class="d-block mb-2"><input type="checkbox" name="verified" value="1"> J’ai vérifié la conformité du dossier.</label>
        <button class="btn gcd-action btn-sm" name="status" value="confirmed">Confirmer la réinscription</button>
    @elseif ($renewal->status === 'favorable')
        <p class="small text-muted">La confirmation nécessite tous les avis favorables et accords requis, ainsi qu’une prolongation accordée si elle a été demandée.</p>
    @endif
    <button class="btn btn-outline-secondary btn-sm" name="status" value="rejected">Refuser la demande</button>
</form>
<details class="mt-3">
    <summary>Demander un complément</summary>
    <form class="mt-2" method="post" action="{{ route('renewals.correction', $renewal) }}">
        @csrf
        <label class="form-label" for="correction-{{ $renewal->id }}">Précisez le complément attendu</label>
        <textarea class="form-control mb-2" id="correction-{{ $renewal->id }}" name="reason" required maxlength="4000"></textarea>
        <button class="btn btn-outline-primary btn-sm">Envoyer la demande de complément</button>
    </form>
</details>
