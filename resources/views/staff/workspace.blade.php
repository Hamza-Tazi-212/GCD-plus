<x-app-layout :title="$function->label()">
    <p>{{ $function->remit() }}</p>
    @foreach ($errors->all() as $error)<p class="alert alert-danger" role="alert">{{ $error }}</p>@endforeach
    @if (auth()->user()->isProfessor())
        <a href="{{ route('professor.dashboard') }}" class="d-inline-block mb-3">Tableau de bord encadrant</a>
    @elseif (auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="d-inline-block mb-3">Tableau de bord administratif</a>
    @endif
    @if ($assignments->count() > 1)
        <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Mes fonctions">
            @foreach ($assignments->keys() as $key)
                <a class="btn btn-sm {{ $function->value === $key ? 'gcd-action' : 'btn-outline-primary' }}" href="{{ route('staff.workspace', ['fonction' => $key]) }}" @if($function->value === $key) aria-current="page" @endif>{{ \App\Enums\StaffFunction::from($key)->label() }}</a>
            @endforeach
        </nav>
    @endif
    @unless ($function === \App\Enums\StaffFunction::CoSupervisor)
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <a href="{{ route('staff.workspace', ['fonction' => $function->value]) }}"
                class="btn btn-sm {{ ! $actionable && ! $waitlist ? 'gcd-action' : 'btn-outline-primary' }}"
                @if (! $actionable && ! $waitlist) aria-current="page" @endif>Tout voir</a>
            <a href="{{ route('staff.workspace', ['fonction' => $function->value, 'a_traiter' => 1]) }}"
                class="btn btn-sm {{ $actionable ? 'gcd-action' : 'btn-outline-primary' }}"
                @if ($actionable) aria-current="page" @endif>À traiter</a>
            @if ($function === \App\Enums\StaffFunction::Selection)
                <a href="{{ route('staff.workspace', ['fonction' => $function->value, 'liste_attente' => 1]) }}"
                    class="btn btn-sm {{ $waitlist ? 'gcd-action' : 'btn-outline-primary' }}"
                    @if ($waitlist) aria-current="page" @endif>Liste d’attente</a>
            @endif
        </div>
    @endunless
    @if ($actionable)
        <p class="text-muted">
            @if ($actionCount === 0)
                Aucune action attendue dans vos affectations.
            @else
                {{ $actionCount }} {{ $actionCount === 1 ? 'élément à traiter' : 'éléments à traiter' }}
            @endif
        </p>
    @endif
    @if (! $actionable || $subjects->isNotEmpty())
    <section class="card card-body mb-3">
        <h2 class="h5">{{ $actionable ? "Sujets en attente d’avis" : "Sujets affectés" }}</h2>
        @foreach ($subjects as $subject)
            <section class="border-top py-2">
                <h3 class="h6">{{ $subject->title }} · {{ $subject->campaign }}</h3>
                @if (in_array($function, \App\Models\ResearchSubject::APPROVALS, true))
                    @php($revision = $subject->approvalRevision())
                    @php($opinion = $subject->approvals->where('revision', $revision)->firstWhere('function', $function->value))
                    <details>
                        <summary>Consulter le sujet et son avis</summary>
                        <p class="text-break">{{ $subject->description }}</p>
                        <p>{{ $subject->responsibilities }}</p>
                        <p>{{ $subject->candidate_profile }}</p>
                        @if ($opinion)
                            <p>{{ $opinion->approved ? 'Avis favorable' : 'Avis défavorable' }} · {{ $opinion->comment }}</p>
                        @elseif ($subject->is_open)
                            <p>Sujet publié</p>
                        @else
                            <form method="post" action="{{ route('staff.subject.approve', [$subject, $function->value]) }}">
                                @csrf
                                <input type="hidden" name="revision" value="{{ $revision }}">
                                <label class="form-label" for="subject-opinion-{{ $subject->id }}">Motif de l’avis</label>
                                <textarea class="form-control mb-2" id="subject-opinion-{{ $subject->id }}" name="comment" required maxlength="4000"></textarea>
                                <button class="btn gcd-action btn-sm" name="approved" value="1">Approuver</button>
                                <button class="btn btn-outline-secondary btn-sm" name="approved" value="0">Refuser</button>
                            </form>
                        @endif
                    </details>
                @endif
            </section>
        @endforeach
    </section>
    @endif
    @if ($applications && (! $actionable || $applications->total() > 0))
        <section class="card card-body mb-3">
            <h2 class="h5">{{ $actionable ? 'Candidatures à traiter' : 'Candidatures' }}</h2>
            @if ($waitlist)
                <p>Liste d’attente classée par sujet, campagne et rang.</p>
            @endif
            @forelse ($applications as $application)
                <div class="border-top py-2">
                    <a href="{{ route('staff.application', $application) }}">{{ $application->subject->title }}</a> · {{ $application->campaign }} <x-status-badge :status="$application->status" />
                    @if ($application->status === \App\Enums\ApplicationStatus::Waitlisted)
                        <span class="small">{{ $application->waitlist_rank ? 'Rang '.$application->waitlist_rank : 'Rang à attribuer' }}</span>
                    @endif
                </div>
            @empty
                <p class="mb-0">Aucun dossier dans vos affectations.</p>
            @endforelse
            {{ $applications->links() }}
        </section>
    @endif
    @if ($renewals && (! $actionable || $renewals->total() > 0))
        <section class="card card-body">
            <h2 class="h5">Réinscriptions</h2>
            @forelse ($renewals as $renewal)
                <details class="border-top py-2">
                    <summary>{{ $renewal->doctoralEnrolment->application->subject->title }} · {{ $renewal->academic_year }}</summary>
                    <p class="mt-2">{{ \App\Models\Renewal::LABELS[$renewal->status] }}</p>
                    <p class="text-break" style="white-space:pre-wrap">{{ $renewal->report }}</p>
                    @if ($renewal->attachment_path)
                        <p><a href="{{ route('renewals.attachment', $renewal) }}">Télécharger le justificatif du bilan</a></p>
                    @endif
                    @include('renewals.correction-history')
                    @include('renewals.approvals')
                    @include('renewals.extension-status')
                    @if ($function === \App\Enums\StaffFunction::Head && $renewal->extension && $renewal->status === 'favorable'
                        && $renewal->hasRequiredApprovals() && ! $renewal->extensionDecision())
                        <form method="post" action="{{ route('renewals.extension.decide', $renewal) }}" class="my-3">
                            @csrf
                            <input type="hidden" name="revision" value="{{ count($renewal->correction_history ?? []) }}">
                            <label class="form-label" for="extension-{{ $renewal->id }}">Motif de la décision de prolongation</label>
                            <textarea class="form-control mb-2" id="extension-{{ $renewal->id }}" name="comment" required maxlength="4000"></textarea>
                            <button class="btn gcd-action btn-sm" name="approved" value="1">Accorder la prolongation</button>
                            <button class="btn btn-outline-secondary btn-sm" name="approved" value="0">Refuser la prolongation</button>
                        </form>
                    @endif
                    @if ($renewal->status === 'favorable' && in_array($function, \App\Models\Renewal::APPROVALS, true) && ! $renewal->approvals->contains('function', $function->value))
                        <form method="post" action="{{ route('renewals.approve', [$renewal, $function->value]) }}">
                            @csrf
                            <label class="form-label" for="approval-{{ $renewal->id }}">Motif de votre avis</label>
                            <textarea class="form-control mb-2" id="approval-{{ $renewal->id }}" name="comment" required maxlength="4000"></textarea>
                            <button class="btn gcd-action btn-sm" name="approved" value="1">Avis favorable</button>
                            <button class="btn btn-outline-secondary btn-sm" name="approved" value="0">Avis défavorable</button>
                        </form>
                    @elseif ($function === \App\Enums\StaffFunction::Manager && in_array($renewal->status, ['favorable', 'unfavorable'], true))
                        @include('renewals.decision')
                    @endif
                </details>
            @empty
                <p class="mb-0">Aucune réinscription dans vos affectations.</p>
            @endforelse
            {{ $renewals->links() }}
        </section>
    @endif
</x-app-layout>
