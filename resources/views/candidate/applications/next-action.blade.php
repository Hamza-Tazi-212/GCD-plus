@php
    $toReplace = $application->allowsDocumentCorrections()
        ? $application->documents->filter(fn ($document) => $document->corrections->contains('resolved_at', null))
        : collect();
@endphp
<section class="alert {{ $toReplace->isNotEmpty() ? 'alert-warning' : 'alert-info' }}" aria-labelledby="dossier-next-action">
    <h3 id="dossier-next-action" class="h6 fw-bold">Prochaine action</h3>
    @if ($toReplace->isNotEmpty())
        <ul class="mb-0">
            @foreach ($toReplace as $document)
                <li class="mb-2">
                    <strong>{{ $document->type->label() }} à remplacer</strong>
                    <a class="btn btn-sm btn-outline-dark ms-1" href="#replacement-{{ $document->id }}">Accéder au dépôt</a>
                </li>
            @endforeach
        </ul>
    @elseif ($application->doctoralEnrolment)
        <p class="mb-1">Inscription confirmée pour {{ $application->doctoralEnrolment->academic_year }}.</p>
        <a class="alert-link" href="{{ route('renewals.index', ['inscription' => $application->doctoralEnrolment->id]) }}">Accéder à mes réinscriptions</a>
    @elseif ($application->status === \App\Enums\ApplicationStatus::Accepted)
        <p class="mb-1">Vous êtes retenu. Consultez les pièces requises et l’état de leur contrôle.</p>
        <a class="alert-link" href="{{ route('enrolment-documents.show', $application) }}">Déposer ou suivre mes pièces administratives</a>
    @elseif ($application->status === \App\Enums\ApplicationStatus::Waitlisted)
        <p class="mb-0">
            Votre candidature est sur liste d’attente.
            @if ($application->waitlist_rank)
                <strong>Rang {{ $application->waitlist_rank }}</strong> pour ce sujet et cette campagne.
            @else
                Votre rang est en cours d’attribution.
            @endif
            La décision sera affichée ici si une place vous est attribuée.
        </p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::UnderReview && $application->oral_exam_at?->isFuture())
        <p class="mb-0">Présentez-vous à l’entretien le {{ $application->oral_exam_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y à H:i') }} (heure de Fès).</p>
    @elseif ($application->status === \App\Enums\ApplicationStatus::Rejected)
        <p class="mb-0">La candidature est clôturée. Consultez le commentaire et l’historique du dossier.</p>
    @else
        <p class="mb-0">Votre dossier est en cours de traitement. Les prochaines étapes seront affichées ici.</p>
    @endif
</section>
