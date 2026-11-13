<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\CampaignPeriod;
use App\Models\EnrolmentChecklist;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardStatsService $stats)
    {
    }

    public function index(Request $request): View
    {
        $overview = $this->stats->candidateOverview($request->user());
        $now = now();
        $interviews = $overview['applications']
            ->filter(fn ($application) => $application->status === ApplicationStatus::UnderReview
                && $application->oral_exam_at && $application->oral_exam_at->gte($now))
            ->sortBy('oral_exam_at');

        $unreadCount = $request->user()->unreadNotifications()->count();

        $actions = $this->dossierActions($overview['applications']);

        return view('candidate.dashboard', compact('overview', 'interviews', 'unreadCount', 'actions'));
    }

    private function dossierActions(\Illuminate\Database\Eloquent\Collection $applications): array
    {
        $accepted = $applications->where('status', ApplicationStatus::Accepted);
        $accepted->load(['enrolmentDocuments', 'doctoralEnrolment.renewals']);
        $periods = CampaignPeriod::whereIn('phase', ['enrolment', 'renewal'])->get()->groupBy('phase');
        $enrolmentPeriods = $periods->get('enrolment', collect())->keyBy('academic_year');
        $renewalPeriods = $periods->get('renewal', collect())->keyBy('academic_year');
        $checklists = EnrolmentChecklist::whereIn('campaign', $accepted->pluck('campaign')->unique())->get()->keyBy('campaign');
        $actions = [];

        foreach ($accepted as $application) {
            if ($enrolment = $application->doctoralEnrolment) {
                $renewal = $enrolment->renewals->firstWhere('status', 'correction');
                if ($renewal) {
                    $actions[] = [
                        'title' => 'Corriger ma réinscription',
                        'subject' => $application->subject->title,
                        'detail' => 'Année universitaire '.$renewal->academic_year,
                        'url' => route('renewals.index', ['inscription' => $enrolment->id]).'#reinscription-'.$renewal->id,
                    ];
                    continue;
                }

                $year = $enrolment->nextAcademicYear();
                $period = $renewalPeriods->get($year);
                if ($period?->isOpen() && ! $enrolment->renewals->contains('academic_year', $year)) {
                    $actions[] = [
                        'title' => 'Déposer mon bilan annuel',
                        'subject' => $application->subject->title,
                        'detail' => 'Année universitaire '.$year.' · jusqu’au '.date('d/m/Y', strtotime($period->closes_on)),
                        'url' => route('renewals.index', ['inscription' => $enrolment->id]).'#bilan-'.$enrolment->id,
                    ];
                }
                continue;
            }

            $documents = $application->enrolmentDocuments->keyBy('kind');
            $period = $enrolmentPeriods->get($application->campaign);
            $corrections = [];
            $missing = [];
            $kinds = $checklists->get($application->campaign)?->kinds;
            $checklist = $kinds === null ? \App\Models\EnrolmentDocument::REQUIRED
                : array_intersect_key(\App\Models\EnrolmentDocument::AVAILABLE, array_flip($kinds));
            foreach ($checklist as $kind => $label) {
                $document = $documents->get($kind);
                if ($document?->status === 'correction') {
                    $corrections[] = $label;
                } elseif (! $document && (! $period || $period->isOpen())) {
                    $missing[] = $label;
                }
            }
            if ($corrections || $missing) {
                $details = [];
                if ($corrections) {
                    $details[] = 'À remplacer : '.implode(', ', $corrections);
                }
                if ($missing) {
                    $details[] = 'À déposer : '.implode(', ', $missing);
                }
                $actions[] = [
                    'title' => 'Compléter mes pièces administratives',
                    'subject' => $application->subject->title,
                    'detail' => implode(' · ', $details),
                    'url' => route('enrolment-documents.show', $application),
                ];
            }
        }

        return $actions;
    }
}
