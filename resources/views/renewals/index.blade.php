<x-app-layout title="Réinscriptions doctorales">
    <p>
        <a href="{{ route('calendar') }}">Consulter le calendrier des campagnes</a>
        @if (request()->filled('inscription'))
            · <a href="{{ route('renewals.index') }}">Voir toutes les réinscriptions</a>
        @endif
    </p>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    @forelse ($enrolments as $enrolment)
        <section class="card card-body mb-3">
            <h2 class="h5">{{ $enrolment->application->subject->title }}</h2>
            <p class="text-muted">{{ $enrolment->application->candidate->name }} · Première inscription {{ $enrolment->academic_year }}</p>
            @foreach ($enrolment->renewals->sortByDesc('academic_year') as $renewal)
                <div class="border-top py-3" id="reinscription-{{ $renewal->id }}">
                    <h3 class="h6">{{ $renewal->academic_year }} · {{ \App\Models\Renewal::LABELS[$renewal->status] }}</h3>
                    @include('renewals.extension-status')
                    <details class="mb-2">
                        <summary>Lire le bilan annuel</summary>
                        <p class="mt-2 text-break" style="white-space:pre-wrap">{{ $renewal->report }}</p>
                    @if ($renewal->attachment_path)
                        <p><a href="{{ route('renewals.attachment', $renewal) }}">Télécharger le justificatif du bilan</a></p>
                    @endif
                    </details>
                    @if ($renewal->supervisor_comment)<p><strong>Avis de l’encadrant</strong><br>{{ $renewal->supervisor_comment }}</p>@endif
                    @if ($renewal->administrative_comment)<p><strong>Décision administrative</strong><br>{{ $renewal->administrative_comment }}</p>@endif
                    @include('renewals.correction-history')
                    @include('renewals.approvals')
                    @if (auth()->user()->isCandidate() && $renewal->status === 'correction')
                        @include('renewals.resubmit')
                    @elseif (auth()->user()->isProfessor() && $renewal->status === 'submitted')
                        <form method="post" action="{{ route('renewals.review', $renewal) }}">
                            @csrf
                            <label class="form-label" for="opinion-{{ $renewal->id }}">Avis motivé</label>
                            <textarea class="form-control mb-2" id="opinion-{{ $renewal->id }}" name="comment" required maxlength="4000"></textarea>
                            <button class="btn gcd-action btn-sm" name="status" value="favorable">Avis favorable</button>
                            <button class="btn btn-outline-secondary btn-sm" name="status" value="unfavorable">Avis défavorable</button>
                        </form>
                    @elseif (auth()->user()->isAdmin() && in_array($renewal->status, ['favorable', 'unfavorable'], true))
                        @include('renewals.decision')
                    @endif
                </div>
            @endforeach
            @if (auth()->user()->isCandidate())
                @php($year = $enrolment->nextAcademicYear())
                @php($period = $renewalPeriods->get($year))
                @unless ($enrolment->renewals->contains('academic_year', $year))
                    @include('partials.campaign-deadline')
                    @if (! $period || $period->isOpen())
                    <form class="border-top pt-3" id="bilan-{{ $enrolment->id }}" method="post" enctype="multipart/form-data" action="{{ route('renewals.store', $enrolment) }}">
                        @csrf
                        <input type="hidden" name="academic_year" value="{{ $year }}">
                        <label class="form-label" for="report-{{ $enrolment->id }}">Bilan annuel pour la réinscription {{ $year }}</label>
                        <textarea class="form-control mb-2" id="report-{{ $enrolment->id }}" name="report" rows="4" maxlength="12000" required></textarea>
                        <label class="form-label" for="attachment-{{ $enrolment->id }}">Justificatif du bilan (facultatif, PDF de 5 Mo maximum)</label>
                        <input class="form-control" type="file" id="attachment-{{ $enrolment->id }}" name="attachment" accept=".pdf,application/pdf">
                        <x-document-capture :target="'attachment-'.$enrolment->id" />
                        <label class="d-block my-2"><input type="checkbox" name="extension" value="1"> Je demande une prolongation</label>
                        <button class="btn gcd-action">Envoyer la demande</button>
                    </form>
                    @endif
                @endunless
            @endif
        </section>
    @empty
        <p>Aucune inscription doctorale confirmée à afficher.</p>
    @endforelse
    {{ $enrolments->links() }}
</x-app-layout>
