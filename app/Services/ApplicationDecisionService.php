<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicationAccepted;
use App\Mail\ApplicationRejected;
use App\Mail\ApplicationWaitlisted;
use App\Mail\OralExamInvitation;
use App\Models\Application;
use App\Models\ResearchSubject;
use App\Models\User;
use App\Notifications\DossierUpdate;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApplicationDecisionService
{
    public function decide(
        Application $application,
        ApplicationStatus $status,
        User $reviewer,
        ?string $comment = null,
        ?string $oralExamDate = null,
        ?string $programStartDate = null,
        ?int $waitlistRank = null,
    ): Application {
        [$updated, $previousStatus] = DB::transaction(function () use ($application, $status, $reviewer, $comment, $oralExamDate, $programStartDate, $waitlistRank) {
            // Serialize rank changes on the subject before checking available ranks.
            ResearchSubject::whereKey($application->subject_id)->toBase()->update(['id' => $application->subject_id]);
            Application::whereKey($application->id)->toBase()->update(['id' => $application->id]);
            $application->refresh();
            $previousStatus = $application->status;
            if ($application->documents()->whereHas('corrections', fn ($query) => $query->whereNull('resolved_at'))->exists()) {
                throw ValidationException::withMessages(['status' => 'Attendez le remplacement des pièces demandées avant de décider.']);
            }
            if ($status === ApplicationStatus::Waitlisted
                && $previousStatus !== ApplicationStatus::Waitlisted
                && ($previousStatus !== ApplicationStatus::UnderReview || ! $application->oral_exam_at?->isPast())) {
                throw ValidationException::withMessages(['status' => 'La mise en liste d’attente est possible après la date de l’oral.']);
            }
            if ($previousStatus === ApplicationStatus::Waitlisted && $status === ApplicationStatus::UnderReview) {
                throw ValidationException::withMessages(['status' => 'Un dossier sur liste d’attente peut être retenu ou refusé.']);
            }
            if ($status !== ApplicationStatus::Accepted && $application->doctoralEnrolment()->exists()) {
                throw ValidationException::withMessages(['status' => 'L’inscription administrative est déjà confirmée pour ce dossier.']);
            }

            if ($status === ApplicationStatus::Waitlisted) {
                if ($waitlistRank === null || $waitlistRank < 1 || $waitlistRank > 999999) {
                    throw ValidationException::withMessages(['waitlist_rank' => 'Indiquez un rang entier positif.']);
                }
                $rankTaken = Application::where('subject_id', $application->subject_id)
                    ->where('campaign', $application->campaign)
                    ->where('waitlist_rank', $waitlistRank)
                    ->whereKeyNot($application->id)->exists();
                if ($rankTaken) {
                    throw ValidationException::withMessages(['waitlist_rank' => 'Ce rang est déjà attribué pour ce sujet et cette campagne.']);
                }
            }

            $attributes = [
                'status' => $status,
                'waitlist_rank' => $status === ApplicationStatus::Waitlisted ? $waitlistRank : null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_comment' => $comment,
            ];

            // Only touched when (re-)setting the matching status, so an
            // already-recorded date isn't wiped out by a later, unrelated
            // status change.
            if ($status === ApplicationStatus::UnderReview) {
                $attributes['oral_exam_at'] = $oralExamDate;
            }

            if ($status === ApplicationStatus::Accepted) {
                $attributes['program_start_at'] = $programStartDate;
            }

            // A fresh decision means a fresh notification attempt — clear
            // any leftover sent/failed state from a previous status so the
            // admin isn't looking at stale delivery info for this one.
            if ($previousStatus !== $status) {
                $attributes['notification_sent_at'] = null;
                $attributes['notification_error'] = null;
            }

            $application->update($attributes);

            $application->statusLogs()->create([
                'from_status' => $previousStatus,
                'to_status' => $status,
                'waitlist_rank' => $attributes['waitlist_rank'],
                'changed_by' => $reviewer->id,
                'comment' => $comment,
            ]);

            $title = $previousStatus !== $status ? 'Candidature · '.$status->label() : null;
            if ($previousStatus === $status && $status === ApplicationStatus::UnderReview && $application->wasChanged('oral_exam_at')) {
                $title = 'Date de l’entretien modifiée';
            }
            if ($previousStatus === $status && $status === ApplicationStatus::Waitlisted && $application->wasChanged('waitlist_rank')) {
                $title = 'Rang sur liste d’attente mis à jour';
            }
            if ($title !== null) {
                DossierUpdate::record($application, $title, 'application');
            }

            return [$application->fresh(), $previousStatus];
        }, 3);

        // Only notify on an actual transition into this status, so
        // re-saving a comment on an already-decided application doesn't
        // spam the candidate with a repeat email. Sent synchronously (not
        // queued) — this is an occasional admin action, not bulk mail, so
        // the admin sees whether it actually went out immediately instead
        // of needing a queue worker running in the background.
        if ($previousStatus !== $status) {
            $this->notifyCandidate($updated);
        }

        return $updated;
    }

    /**
     * Retry the decision email for the application's current status —
     * used by the admin's "Resend" button after a failed send.
     */
    public function resendNotification(Application $application): void
    {
        $this->notifyCandidate($application);
    }

    private function notifyCandidate(Application $application): void
    {
        $application->loadMissing(['candidate.profile', 'subject']);

        $mailable = $this->mailableFor($application);

        if (! $mailable) {
            return;
        }

        try {
            Mail::to($application->candidate->email)->send($mailable);

            $application->update([
                'notification_sent_at' => now(),
                'notification_error' => null,
            ]);
        } catch (Throwable $e) {
            $application->update([
                'notification_sent_at' => null,
                'notification_error' => $e->getMessage(),
            ]);
        }
    }

    private function mailableFor(Application $application): ?Mailable
    {
        return match ($application->status) {
            ApplicationStatus::UnderReview => new OralExamInvitation($application),
            ApplicationStatus::Waitlisted => new ApplicationWaitlisted($application),
            ApplicationStatus::Accepted => new ApplicationAccepted($application),
            ApplicationStatus::Rejected => new ApplicationRejected($application),
            default => null,
        };
    }
}
