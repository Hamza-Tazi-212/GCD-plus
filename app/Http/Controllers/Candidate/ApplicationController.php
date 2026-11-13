<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreApplicationRequest;
use App\Models\CampaignPeriod;
use App\Models\Application;
use App\Models\ResearchSubject;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applicationService)
    {
    }

    public function index(Request $request): View
    {
        $applications = $request->user()->applications()
            ->with(['subject.department', 'subject.professor'])
            ->latest()
            ->paginate(10);

        return view('candidate.applications.index', [
            'applications' => $applications,
        ]);
    }

    public function create(Request $request, ResearchSubject $subject): View|RedirectResponse
    {
        if (! $subject->is_open) {
            return redirect()->route('candidate.subjects.show', $subject)
                ->with('error', 'Les candidatures sont fermées pour ce sujet.');
        }

        $alreadyApplied = $subject->applications()->where('candidate_id', $request->user()->id)->exists();

        if ($alreadyApplied) {
            return redirect()->route('candidate.subjects.show', $subject)
                ->with('error', 'Vous avez déjà postulé à ce sujet.');
        }

        if ($request->session()->get('intended_subject') === $subject->id) {
            $request->session()->forget('intended_subject');
        }

        return view('candidate.applications.create', [
            'subject' => $subject->load(['professor', 'department']),
            'profile' => $request->user()->profile()->with('documents')->first(),
            'period' => CampaignPeriod::where(['academic_year' => $subject->campaign, 'phase' => 'application'])->first(),
        ]);
    }

    public function store(StoreApplicationRequest $request, ResearchSubject $subject): RedirectResponse
    {
        if (! $subject->is_open) {
            return redirect()->route('candidate.subjects.show', $subject)
                ->with('error', 'Les candidatures sont fermées pour ce sujet.');
        }

        $alreadyApplied = $subject->applications()->where('candidate_id', $request->user()->id)->exists();

        if ($alreadyApplied) {
            return redirect()->route('candidate.subjects.show', $subject)
                ->with('error', 'Vous avez déjà postulé à ce sujet.');
        }

        $application = $this->applicationService->submit(
            $request->user(),
            $subject,
            $request->validated('motivation_summary'),
            $request->documentFiles()['motivation_letter'],
        );

        $basket = $request->session()->get('basket', []);
        $request->session()->put('basket', array_values(array_diff($basket, [$subject->id])));

        return redirect()->route('candidate.applications.show', $application)
            ->with('success', 'Votre candidature a été envoyée.');
    }

    public function show(Request $request, Application $application): View
    {
        $this->authorize('view', $application);

        return view('candidate.applications.show', [
            'application' => $application->load(['subject.department', 'subject.professor', 'documents.corrections', 'statusLogs.changedBy', 'candidate.profile', 'doctoralEnrolment']),
        ]);
    }
}
