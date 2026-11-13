@if ($renewal->extension)
    @php($extensionDecision = $renewal->extensionDecision())
    <section class="my-2">
        <h4 class="h6">Demande de prolongation</h4>
        @if ($extensionDecision)
            <p class="mb-1"><strong>{{ $extensionDecision->approved ? 'Prolongation accordée' : 'Prolongation refusée' }}</strong>
                · {{ $extensionDecision->user->name }}
                · {{ $extensionDecision->created_at->timezone('Africa/Casablanca')->format('d/m/Y H:i') }}
            </p>
            <p class="text-break">{{ $extensionDecision->comment }}</p>
        @elseif (in_array($renewal->status, ['confirmed', 'rejected'], true))
            <p>Demande clôturée sans décision de prolongation enregistrée.</p>
        @elseif ($renewal->status === 'favorable' && $renewal->hasRequiredApprovals())
            <p>Décision du chef d’établissement attendue.</p>
        @else
            <p>La demande de prolongation sera examinée après les avis et accords requis.</p>
        @endif
    </section>
@endif
