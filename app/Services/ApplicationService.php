<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\CampaignPeriod;
use App\Enums\DocumentType;
use App\Models\Application;
use App\Models\ResearchSubject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        protected DocumentUploadService $documentUploadService,
        protected ProfileService $profileService,
    ) {
    }

    public function submit(
        User $candidate,
        ResearchSubject $subject,
        ?string $motivationSummary,
        UploadedFile $motivationLetter,
    ): Application {
        return DB::transaction(function () use ($candidate, $subject, $motivationSummary, $motivationLetter) {
            // Acquire the candidate's write lock before reading the quota, also on SQLite.
            User::whereKey($candidate->id)->toBase()->update(['id' => $candidate->id]);
            $subject = ResearchSubject::findOrFail($subject->id);
            CampaignPeriod::ensureOpen($subject->campaign, 'application', 'application');
            $applications = $candidate->applications();

            if (! $subject->is_open || (clone $applications)->where('subject_id', $subject->id)->exists()) {
                throw ValidationException::withMessages(['application' => 'Ce sujet est fermé ou fait déjà l’objet d’une candidature.']);
            }

            if ((clone $applications)->where('campaign', $subject->campaign)->count() >= 3) {
                throw ValidationException::withMessages(['application' => 'Vous avez déjà envoyé trois candidatures pour la campagne '.$subject->campaign.'.']);
            }

            $application = Application::create([
                'candidate_id' => $candidate->id,
                'subject_id' => $subject->id,
                'campaign' => $subject->campaign,
                'status' => ApplicationStatus::Pending,
                'motivation_summary' => $motivationSummary,
                'submitted_at' => now(),
            ]);

            if ($candidate->profile) {
                $this->profileService->snapshotDocumentsFor($application, $candidate->profile);
            }

            $this->documentUploadService->store($application, DocumentType::MotivationLetter, $motivationLetter, 'applications');

            $application->statusLogs()->create([
                'from_status' => null,
                'to_status' => ApplicationStatus::Pending,
                'changed_by' => $candidate->id,
                'comment' => 'Candidature envoyée.',
            ]);

            return $application;
        });
    }
}
