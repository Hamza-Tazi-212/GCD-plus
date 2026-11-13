    @if ($application->status === \App\Enums\ApplicationStatus::Accepted)
        <div class="card card-body mb-4">
            <h2 class="h6">Inscription administrative</h2>
            @error('verified')<p class="text-danger" role="alert">{{ $message }}</p>@enderror
            @if ($application->doctoralEnrolment)
                <p class="mb-0">Inscription confirmée pour {{ $application->doctoralEnrolment->academic_year }}.</p>
            @elseif ($application->hasApprovedEnrolmentDocuments())
                <form method="post" action="{{ route('enrolments.confirm', $application) }}">
                    @csrf
                    <label class="d-block mb-2"><input type="checkbox" name="verified" value="1" required> Le dossier administratif a été vérifié.</label>
                    <button class="btn gcd-action">Confirmer l’inscription</button>
                </form>
            @else
                <p>La confirmation sera disponible après validation de toutes les pièces.</p>
                @unless(request()->routeIs('enrolment-documents.show'))
                    <a href="{{ route('enrolment-documents.show', $application) }}">Contrôler les pièces administratives</a>
                @endunless
            @endif
        </div>
    @endif
