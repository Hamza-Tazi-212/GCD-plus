<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\CampaignPeriod;
use App\Enums\StaffFunction;
use App\Models\Application;
use App\Models\DoctoralEnrolment;
use App\Models\Renewal;
use App\Notifications\DossierUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RenewalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_active, 403);
        abort_unless($user->isAdmin() || $user->isCandidate() || $user->isProfessor(), 403);
        $filter = $request->validate(['inscription' => ['nullable', 'integer', 'min:1']]);
        $enrolments = DoctoralEnrolment::with(['application.subject', 'application.candidate', 'renewals.approvals.user'])
            ->when($user->isCandidate(), fn ($query) => $query->whereHas('application', fn ($item) => $item->where('candidate_id', $user->id)))
            ->when($user->isProfessor(), fn ($query) => $query->whereHas('application.subject', fn ($item) => $item->where('professor_id', $user->id)))
            ->when(isset($filter['inscription']), fn ($query) => $query->whereKey($filter['inscription']))
            ->latest()->paginate(15)->withQueryString();
        abort_if(isset($filter['inscription']) && $enrolments->total() === 0, 404);

        $renewalPeriods = $user->isCandidate()
            ? CampaignPeriod::where('phase', 'renewal')->whereIn('academic_year', $enrolments->getCollection()
                ->map(fn (DoctoralEnrolment $enrolment) => $enrolment->nextAcademicYear())->unique())->get()->keyBy('academic_year')
            : collect();

        return view('renewals.index', compact('enrolments', 'renewalPeriods'));
    }

    public function confirmEnrolment(Request $request, Application $application)
    {
        $user = $request->user();
        abort_unless($user->is_active && ($user->isAdmin()
            || $user->hasStaffAssignment(StaffFunction::Manager, $application->subject_id)), 403);
        $request->validate(['verified' => ['accepted']]);
        DB::transaction(function () use ($request, $application) {
            Application::whereKey($application->id)->toBase()->update(['id' => $application->id]);
            $application->refresh();
            abort_unless($application->status === ApplicationStatus::Accepted, 422);
            if (! $application->doctoralEnrolment()->exists() && ! $application->hasApprovedEnrolmentDocuments()) {
                throw ValidationException::withMessages(['verified' => 'Toutes les pièces administratives doivent être conformes avant la confirmation.']);
            }
            $enrolment = DoctoralEnrolment::firstOrCreate(['application_id' => $application->id], [
                'academic_year' => $application->campaign,
                'confirmed_by' => $request->user()->id,
            ]);
            if ($enrolment->wasRecentlyCreated) {
                DossierUpdate::record($application, 'Inscription administrative confirmée', 'application');
            }
        }, 3);

        return back()->with('success', 'Inscription administrative confirmée.');
    }

    public function store(Request $request, DoctoralEnrolment $enrolment)
    {
        abort_unless($request->user()->is_active && $request->user()->isCandidate()
            && $enrolment->application->candidate_id === $request->user()->id, 403);
        $data = $request->validate([
            'report' => ['required', 'string', 'max:12000'],
            'extension' => ['sometimes', 'boolean'],
            'academic_year' => ['required', 'string', 'max:20'],
        ]);
        $request->validate(['attachment' => ['nullable', 'file', 'mimes:pdf', 'max:5120']]);
        $attachment = $request->file('attachment');
        $path = $attachment?->store('renewal-attachments', 'local');
        if ($attachment && ! $path) {
            throw new RuntimeException('Échec de l’enregistrement du justificatif.');
        }

        try {
            DB::transaction(function () use ($enrolment, $data, $path) {
                DoctoralEnrolment::whereKey($enrolment->id)->toBase()->update(['id' => $enrolment->id]);
                $year = $enrolment->nextAcademicYear();
                CampaignPeriod::ensureOpen($year, 'renewal', 'report');
                if ($year !== $data['academic_year'] || $enrolment->renewals()->where('academic_year', $year)->exists()) {
                    throw ValidationException::withMessages(['report' => 'Cette année n’est plus disponible pour un nouveau dépôt.']);
                }

                $renewal = $enrolment->renewals()->make($data);
                $renewal->attachment_path = $path;
                $renewal->save();
            }, 3);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        return to_route('renewals.index')->with('success', 'Demande de réinscription transmise à l’encadrant.');
    }

    public function attachment(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        $application = $renewal->doctoralEnrolment->application;
        $assigned = $user->staffAssignments()->where('research_subject_id', $application->subject_id)
            ->whereIn('function', array_map(fn ($function) => $function->value, [...Renewal::APPROVALS, StaffFunction::Manager]))
            ->exists();
        abort_unless($user->is_active && ($user->isAdmin() || $assigned
            || ($user->isCandidate() && $application->candidate_id === $user->id)
            || ($user->isProfessor() && $application->subject->professor_id === $user->id)), 403);
        abort_unless($renewal->attachment_path && Storage::disk('local')->exists($renewal->attachment_path), 404);

        return Storage::disk('local')->download($renewal->attachment_path, 'bilan-annuel.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function review(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->isProfessor()
            && $renewal->doctoralEnrolment->application->subject->professor_id === $user->id, 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['favorable', 'unfavorable'])],
            'comment' => ['required', 'string', 'max:4000'],
        ]);
        $updated = Renewal::whereKey($renewal->id)->where('status', 'submitted')->update([
            'status' => $data['status'],
            'supervisor_comment' => $data['comment'],
            'supervisor_id' => $user->id,
            'supervisor_reviewed_at' => now(),
        ]);
        abort_unless($updated, 409);

        return to_route('renewals.index')->with('success', 'Avis enregistré.');
    }

    public function approve(Request $request, Renewal $renewal, StaffFunction $function)
    {
        $user = $request->user();
        $subjectId = $renewal->doctoralEnrolment->application->subject_id;
        abort_unless(in_array($function, Renewal::APPROVALS, true) && $user->hasStaffAssignment($function, $subjectId), 403);
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'comment' => ['required', 'string', 'max:4000'],
        ]);

        DB::transaction(function () use ($renewal, $function, $user, $data) {
            Renewal::whereKey($renewal->id)->toBase()->update(['id' => $renewal->id]);
            $renewal->refresh();
            abort_unless($renewal->status === 'favorable' && ! $renewal->approvals->contains('function', $function->value), 409);
            $renewal->approvals()->create($data + ['function' => $function->value, 'user_id' => $user->id]);
        }, 3);

        return to_route('staff.workspace', ['fonction' => $function->value])->with('success', 'Avis enregistré.');
    }

    public function requestCorrection(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        abort_unless($user->is_active && ($user->isAdmin()
            || $user->hasStaffAssignment(StaffFunction::Manager, $renewal->doctoralEnrolment->application->subject_id)), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'max:4000']]);

        DB::transaction(function () use ($renewal, $user, $data) {
            Renewal::whereKey($renewal->id)->toBase()->update(['id' => $renewal->id]);
            $renewal->refresh();
            abort_unless(in_array($renewal->status, ['favorable', 'unfavorable'], true), 409);
            $history = $renewal->correction_history ?? [];
            $history[] = [
                'reason' => $data['reason'], 'requested_by' => $user->id, 'requested_at' => now()->toIso8601String(),
                'supervisor' => $renewal->only(['status', 'supervisor_id', 'supervisor_comment', 'supervisor_reviewed_at']),
                'approvals' => $renewal->approvals()->get()->toArray(),
            ];
            $renewal->forceFill(['status' => 'correction', 'correction_history' => $history])->save();
            DossierUpdate::record($renewal->doctoralEnrolment->application, 'Complément demandé pour votre réinscription', 'renewal');
        }, 3);

        return back()->with('success', 'Complément demandé au doctorant.');
    }

    public function resubmit(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->isCandidate()
            && $renewal->doctoralEnrolment->application->candidate_id === $user->id, 403);
        $data = $request->validate([
            'report' => ['required', 'string', 'max:12000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'round' => ['required', 'integer', 'min:1'],
        ]);
        $attachment = $request->file('attachment');
        $path = $attachment?->store('renewal-attachments', 'local');
        if ($attachment && ! $path) {
            throw new RuntimeException('Échec de l’enregistrement du justificatif.');
        }

        try {
            $previous = DB::transaction(function () use ($renewal, $data, $path, $user) {
                Renewal::whereKey($renewal->id)->toBase()->update(['id' => $renewal->id]);
                $renewal->refresh();
                $history = $renewal->correction_history ?? [];
                abort_unless($renewal->status === 'correction' && count($history) === (int) $data['round'], 409);
                if ($renewal->report === $data['report'] && ! $path) {
                    throw ValidationException::withMessages(['report' => 'Modifiez le bilan ou ajoutez un justificatif avant de renvoyer le dossier.']);
                }

                $previous = $renewal->attachment_path;
                $last = array_key_last($history);
                $history[$last]['resubmitted_by'] = $user->id;
                $history[$last]['resubmitted_at'] = now()->toIso8601String();
                $history[$last]['previous_report'] = $renewal->report;
                $history[$last]['attachment_replaced'] = $path !== null;
                $renewal->approvals()->delete();
                $renewal->forceFill([
                    'status' => 'submitted', 'report' => $data['report'], 'attachment_path' => $path ?? $previous,
                    'supervisor_comment' => null, 'supervisor_id' => null, 'supervisor_reviewed_at' => null,
                    'correction_history' => $history,
                ])->save();

                return $previous;
            }, 3);
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        if ($path && $previous) {
            Storage::disk('local')->delete($previous);
        }

        return to_route('renewals.index')->with('success', 'Dossier corrigé et transmis à l’encadrant.');
    }

    public function decideExtension(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        abort_unless($user->hasStaffAssignment(StaffFunction::Head, $renewal->doctoralEnrolment->application->subject_id), 403);
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'comment' => ['required', 'string', 'max:4000'],
            'revision' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($renewal, $data, $user) {
            Renewal::whereKey($renewal->id)->toBase()->update(['id' => $renewal->id]);
            $renewal->refresh();
            abort_unless($renewal->extension && $renewal->status === 'favorable'
                && count($renewal->correction_history ?? []) === (int) $data['revision']
                && $renewal->hasRequiredApprovals() && ! $renewal->extensionDecision(), 409);

            $renewal->approvals()->create([
                'function' => Renewal::EXTENSION_DECISION,
                'user_id' => $user->id,
                'approved' => $data['approved'],
                'comment' => $data['comment'],
            ]);
            DossierUpdate::record(
                $renewal->doctoralEnrolment->application,
                $data['approved'] ? 'Prolongation accordée' : 'Prolongation refusée',
                'renewal',
            );
        }, 3);

        return to_route('staff.workspace', ['fonction' => StaffFunction::Head->value])
            ->with('success', 'Décision sur la prolongation enregistrée.');
    }

    public function decide(Request $request, Renewal $renewal)
    {
        $user = $request->user();
        $manager = $user->hasStaffAssignment(StaffFunction::Manager, $renewal->doctoralEnrolment->application->subject_id);
        abort_unless($user->is_active && ($user->isAdmin() || $manager), 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'rejected'])],
            'comment' => ['required', 'string', 'max:4000'],
            'verified' => ['required_if:status,confirmed', 'accepted_if:status,confirmed'],
        ]);

        DB::transaction(function () use ($renewal, $data, $user) {
            Renewal::whereKey($renewal->id)->toBase()->update(['id' => $renewal->id]);
            $renewal->refresh();
            abort_unless(in_array($renewal->status, ['favorable', 'unfavorable'], true), 409);
            if ($data['status'] === 'confirmed' && ($renewal->status !== 'favorable' || ! $renewal->hasRequiredApprovals())) {
                throw ValidationException::withMessages(['verified' => 'Tous les avis favorables et accords requis doivent être enregistrés.']);
            }
            if ($data['status'] === 'confirmed' && $renewal->extension && ! $renewal->extensionDecision()?->approved) {
                throw ValidationException::withMessages(['verified' => 'La prolongation doit être accordée avant de confirmer cette réinscription.']);
            }
            $renewal->forceFill([
                'status' => $data['status'],
                'administrative_comment' => $data['comment'],
                'administrator_id' => $user->id,
                'decided_at' => now(),
            ])->save();
            DossierUpdate::record($renewal->doctoralEnrolment->application, Renewal::LABELS[$data['status']], 'renewal');
        }, 3);

        return ($manager ? to_route('staff.workspace', ['fonction' => StaffFunction::Manager->value]) : to_route('renewals.index'))
            ->with('success', 'Décision enregistrée.');
    }
}
