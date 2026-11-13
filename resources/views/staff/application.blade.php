<x-app-layout title="Dossier affecté">
    <a href="{{ route('staff.workspace') }}">Retour à mes fonctions</a>
    <section class="card card-body mt-3">
        <h2 class="h5">{{ $application->subject->title }}</h2>
        <p>{{ $application->fullName() }} · {{ $application->campaign }}</p>
        <p><x-status-badge :status="$application->status" /></p>
        @if ($application->motivation_summary)
            <h3 class="h6">Motivation</h3>
            <p class="text-break" style="white-space:pre-wrap">{{ $application->motivation_summary }}</p>
        @endif
        <h3 class="h6">Pièces déposées</h3>
        @include('partials.application-documents')
    </section>
    @if (auth()->user()->hasStaffAssignment(\App\Enums\StaffFunction::Manager, $application->subject_id))
        <div class="mt-3">@include('renewals.enrolment-confirm')</div>
    @endif
    @if (auth()->user()->hasStaffAssignment(\App\Enums\StaffFunction::Selection, $application->subject_id))
        <section class="card card-body mt-3">
            <h2 class="h5">Décision de sélection</h2>
            @foreach ($errors->all() as $error)<p class="text-danger" role="alert">{{ $error }}</p>@endforeach
            <form method="post" action="{{ route('staff.application.review', $application) }}">
                @csrf
                @method('PATCH')
                <label class="form-label" for="selection-status">Décision</label>
                <select class="form-select mb-2" id="selection-status" name="status" required>
                    @foreach ([\App\Enums\ApplicationStatus::UnderReview, \App\Enums\ApplicationStatus::Waitlisted, \App\Enums\ApplicationStatus::Accepted, \App\Enums\ApplicationStatus::Rejected] as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $application->status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <label class="form-label" for="waitlist-rank">Rang (obligatoire pour la liste d’attente)</label>
                <input class="form-control mb-2" id="waitlist-rank" type="number" name="waitlist_rank" min="1" max="999999" value="{{ old('waitlist_rank', $application->waitlist_rank) }}">
                <label class="form-label" for="oral-date">Date de l’oral (pour une convocation)</label>
                <input class="form-control mb-2" id="oral-date" type="datetime-local" name="oral_exam_date" value="{{ old('oral_exam_date') }}">
                <label class="form-label" for="program-date">Début de la formation (pour une admission)</label>
                <input class="form-control mb-2" id="program-date" type="date" name="program_start_date" value="{{ old('program_start_date') }}">
                <label class="form-label" for="selection-comment">Commentaire (motif obligatoire pour un refus)</label>
                <textarea class="form-control mb-2" id="selection-comment" name="comment" maxlength="2000">{{ old('comment') }}</textarea>
                <button class="btn gcd-action">Enregistrer la décision</button>
            </form>
        </section>
    @endif
</x-app-layout>
