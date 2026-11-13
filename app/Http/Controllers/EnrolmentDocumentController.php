<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\CampaignPeriod;
use App\Enums\StaffFunction;
use App\Models\Application;
use App\Models\EnrolmentChecklist;
use App\Models\EnrolmentDocument;
use App\Notifications\DossierUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class EnrolmentDocumentController extends Controller
{
    public function show(Request $request, Application $application)
    {
        $manager = $this->authorizeDossier($request, $application);

        return view('renewals.documents', [
            'application' => $application,
            'manager' => $manager,
            'period' => CampaignPeriod::where(['academic_year' => $application->campaign, 'phase' => 'enrolment'])->first(),
            'documents' => $application->enrolmentDocuments()->get()->keyBy('kind'),
            'checklist' => EnrolmentChecklist::forCampaign($application->campaign),
        ]);
    }

    public function upload(Request $request, Application $application, string $kind)
    {
        abort_if($this->authorizeDossier($request, $application), 403);
        abort_unless(isset(EnrolmentChecklist::forCampaign($application->campaign)[$kind]), 404);
        $request->validate(['document' => ['required', 'file', 'mimes:pdf', 'max:5120']]);
        $path = $request->file('document')->store('enrolment-documents', 'local');
        if (! $path) {
            throw new RuntimeException('Échec de l’enregistrement du document.');
        }

        try {
            $previous = DB::transaction(function () use ($application, $kind, $path) {
                $this->lockOpenDossier($application);
                abort_unless(isset(EnrolmentChecklist::forCampaign($application->campaign)[$kind]), 404);
                $isCorrection = $application->enrolmentDocuments()->where('kind', $kind)->where('status', 'correction')->exists();
                if (! $isCorrection) {
                    CampaignPeriod::ensureOpen($application->campaign, 'enrolment', 'document');
                }
                $document = $application->enrolmentDocuments()->where('kind', $kind)->first();
                abort_if($document && $document->status !== 'correction', 409);
                $previous = $document?->path;
                $application->enrolmentDocuments()->updateOrCreate(['kind' => $kind], [
                    'path' => $path, 'status' => 'pending', 'comment' => null,
                    'reviewed_by' => null, 'reviewed_at' => null,
                ]);

                return $previous;
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        if ($previous) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('success', 'Pièce déposée.');
    }

    public function preview(Request $request, Application $application, EnrolmentDocument $document)
    {
        $this->authorizeDossier($request, $application);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->response($document->path, $document->kind.'.pdf', [
            'Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function review(Request $request, Application $application, EnrolmentDocument $document)
    {
        abort_unless($this->authorizeDossier($request, $application), 403);
        abort_unless($document->application_id === $application->id, 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'correction'])],
            'comment' => ['nullable', 'required_if:status,correction', 'string', 'max:2000'],
            'version' => ['required', 'string', 'size:64'],
        ]);

        DB::transaction(function () use ($request, $application, $document, $data) {
            $this->lockOpenDossier($application);
            $document->refresh();
            abort_unless(isset(EnrolmentChecklist::forCampaign($application->campaign)[$document->kind]), 404);
            abort_unless($document->status === 'pending' && hash('sha256', $document->path) === $data['version'], 409);
            $document->update([
                'status' => $data['status'], 'comment' => $data['comment'] ?? null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
            ]);

            if ($document->status === 'correction') {
                DossierUpdate::record($application, 'Pièce administrative à remplacer', 'enrolment');
            }
        }, 3);

        return back()->with('success', 'Contrôle enregistré.');
    }

    private function authorizeDossier(Request $request, Application $application): bool
    {
        $user = $request->user();
        $manager = $user->isAdmin() || $user->hasStaffAssignment(StaffFunction::Manager, $application->subject_id);
        abort_unless($user->is_active && ($manager || ($user->isCandidate() && $application->candidate_id === $user->id)), 403);
        abort_unless($application->status === ApplicationStatus::Accepted, 404);

        return $manager;
    }

    private function lockOpenDossier(Application $application): void
    {
        Application::whereKey($application->id)->toBase()->update(['id' => $application->id]);
        $application->refresh();
        abort_unless($application->status === ApplicationStatus::Accepted && ! $application->doctoralEnrolment()->exists(), 409);
    }
}
