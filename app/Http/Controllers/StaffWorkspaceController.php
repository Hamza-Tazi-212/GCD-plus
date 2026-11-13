<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\StaffFunction;
use App\Models\Application;
use App\Models\EnrolmentChecklist;
use App\Models\EnrolmentDocument;
use App\Models\ResearchSubject;
use App\Models\Renewal;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffWorkspaceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->is_active, 403);
        $assignments = $request->user()->staffAssignments()->with('subject.approvals')->get()->groupBy(fn ($assignment) => $assignment->function->value);
        abort_if($assignments->isEmpty(), 403);
        $function = StaffFunction::tryFrom((string) $request->query('fonction', $assignments->keys()->sort()->first()));
        abort_unless($function && $assignments->has($function->value), 403);

        $subjects = $assignments[$function->value]->pluck('subject');
        $subjectIds = $subjects->pluck('id')->all();
        $reviewSubjects = in_array($function, ResearchSubject::APPROVALS, true);
        $waitlist = $function === StaffFunction::Selection && $request->boolean('liste_attente');
        $actionable = $function !== StaffFunction::CoSupervisor && ! $waitlist && $request->boolean('a_traiter');
        if ($actionable) {
            $subjects = $reviewSubjects
                ? $subjects->filter(fn (ResearchSubject $subject) => ! $subject->is_open
                    && ! $subject->approvals->where('revision', $subject->approvalRevision())->contains('function', $function->value))
                : collect();
        }

        $applications = null;
        if (in_array($function, [StaffFunction::CoSupervisor, StaffFunction::Selection, StaffFunction::Manager], true)) {
            $applications = Application::with('subject')->whereIn('subject_id', $subjectIds)
                ->when($function === StaffFunction::Manager, fn ($query) => $query->where('status', ApplicationStatus::Accepted))
                ->when($actionable, fn ($query) => $this->filterApplicationsToProcess($query, $function))
                ->when($waitlist, fn ($query) => $query->where('status', ApplicationStatus::Waitlisted)
                    ->orderBy('subject_id')->orderByDesc('campaign')
                    ->orderByRaw('CASE WHEN waitlist_rank IS NULL THEN 1 ELSE 0 END')->orderBy('waitlist_rank'))
                ->latest()->orderByDesc('id')->paginate(15, pageName: 'dossiers')->withQueryString();
        }
        $renewals = null;
        if (in_array($function, [StaffFunction::Lab, StaffFunction::Ced, StaffFunction::Thesis, StaffFunction::Head, StaffFunction::Manager], true)) {
            $renewals = Renewal::with(['doctoralEnrolment.application.subject', 'approvals.user'])
                ->whereHas('doctoralEnrolment.application', fn ($query) => $query->whereIn('subject_id', $subjectIds))
                ->when($actionable, fn ($query) => $this->filterRenewalsToProcess($query, $function))
                ->latest()->orderByDesc('id')->paginate(15, pageName: 'reinscriptions')->withQueryString();
        }

        $actionCount = $actionable ? $subjects->count() + ($applications?->total() ?? 0) + ($renewals?->total() ?? 0) : null;

        return view('staff.workspace', compact('assignments', 'function', 'subjects', 'applications', 'renewals', 'actionable', 'waitlist', 'actionCount'));
    }

    private function filterApplicationsToProcess(Builder $query, StaffFunction $function): Builder
    {
        if ($function === StaffFunction::Manager) {
            $checklists = EnrolmentChecklist::all();
            $ready = fn ($dossier, array $kinds) => $dossier
                ->where(fn ($state) => $state
                    ->whereHas('enrolmentDocuments', fn ($docs) => $docs->where('status', 'pending')->whereIn('kind', $kinds))
                    ->orWhereHas('enrolmentDocuments', fn ($docs) => $docs->where('status', 'approved')->whereIn('kind', $kinds), '=', count($kinds)));

            return $query->whereDoesntHave('doctoralEnrolment')->where(function ($dossier) use ($checklists, $ready) {
                $dossier->where(fn ($default) => $ready($default->whereNotIn('campaign', $checklists->pluck('campaign')), array_keys(EnrolmentDocument::REQUIRED)));
                foreach ($checklists as $checklist) {
                    $dossier->orWhere(fn ($configured) => $ready($configured->where('campaign', $checklist->campaign), $checklist->kinds));
                }
            });
        }

        return $query->where(fn ($dossier) => $dossier->where('status', ApplicationStatus::Pending)
            ->orWhere(fn ($oral) => $oral->where('status', ApplicationStatus::UnderReview)->where('oral_exam_at', '<', now()))
            ->orWhere(fn ($wait) => $wait->where('status', ApplicationStatus::Waitlisted)->whereNull('waitlist_rank')));
    }

    private function filterRenewalsToProcess(Builder $query, StaffFunction $function): Builder
    {
        $required = array_map(fn (StaffFunction $step) => $step->value, Renewal::APPROVALS);
        $favorable = fn ($approval) => $approval->whereIn('function', $required)->where('approved', true);
        $extension = fn ($approval) => $approval->where('function', Renewal::EXTENSION_DECISION);

        if ($function === StaffFunction::Manager) {
            return $query->where(fn ($renewal) => $renewal->where('status', 'unfavorable')
                ->orWhere(fn ($ready) => $ready->where('status', 'favorable')->where(fn ($decision) => $decision
                    ->whereHas('approvals', fn ($approval) => $approval->whereIn('function', $required)->where('approved', false))
                    ->orWhere(fn ($complete) => $complete->whereHas('approvals', $favorable, '=', count($required))
                        ->where(fn ($extra) => $extra->where('extension', false)->orWhereHas('approvals', $extension))))));
        }

        return $query->where('status', 'favorable')->where(function ($renewal) use ($function, $favorable, $required, $extension) {
            $renewal->whereDoesntHave('approvals', fn ($approval) => $approval->where('function', $function->value));
            if ($function === StaffFunction::Head) {
                $renewal->orWhere(fn ($extra) => $extra->where('extension', true)
                    ->whereHas('approvals', $favorable, '=', count($required))->whereDoesntHave('approvals', $extension));
            }
        });
    }

    public function application(Request $request, Application $application)
    {
        abort_unless($request->user()->canConsultAssignedApplication($application), 403);
        $application->load(['subject', 'candidate.profile', 'documents.corrections', 'doctoralEnrolment']);

        return view('staff.application', compact('application'));
    }

    public function assignments()
    {
        return view('staff.assignments', [
            'assignments' => StaffAssignment::with(['user', 'subject'])->latest('id')->paginate(25),
            'users' => User::where('is_active', true)->where('role', '!=', \App\Enums\UserRole::Candidate->value)->orderBy('name')->get(),
            'subjects' => ResearchSubject::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('is_active', true)->whereNot('role', \App\Enums\UserRole::Candidate->value)],
            'research_subject_id' => ['required', 'exists:research_subjects,id'],
            'function' => ['required', Rule::enum(StaffFunction::class)],
        ]);
        StaffAssignment::firstOrCreate($data);

        return back()->with('success', 'Fonction attribuée.');
    }

    public function destroy(StaffAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Affectation retirée.');
    }
}
