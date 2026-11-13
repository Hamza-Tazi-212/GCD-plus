<?php

namespace App\Http\Controllers;

use App\Enums\StaffFunction;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Notifications\DossierUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ApplicationDocumentCorrectionController extends Controller
{
    public function requestCorrection(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $user = $request->user();
        $application = $document->application;
        abort_unless($user->is_active && ($user->isAdmin()
            || $user->hasStaffAssignment(StaffFunction::Selection, $application->subject_id)), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($application, $document, $user, $data) {
            $this->lockApplication($application);
            abort_unless($application->allowsDocumentCorrections(), 409);
            abort_if($document->corrections()->whereNull('resolved_at')->exists(), 409);
            $document->corrections()->create($data + ['requested_by' => $user->id]);
            DossierUpdate::record($application, 'Pièce de candidature à remplacer', 'application');
        }, 3);

        return back()->with('success', 'Demande de remplacement envoyée au candidat.');
    }

    public function replace(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $user = $request->user();
        $application = $document->application;
        abort_unless($user->is_active && $user->isCandidate() && $application->candidate_id === $user->id, 403);
        $data = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'correction' => ['required', 'integer', 'min:1'],
        ]);
        $file = $request->file('document');
        $disk = Storage::disk('local');
        $path = $file->storeAs('applications/'.$application->id, Str::uuid().'.pdf', 'local');
        if (! $path) {
            throw new RuntimeException('Échec de l’enregistrement du PDF.');
        }

        try {
            $oldPath = DB::transaction(function () use ($application, $document, $file, $path, $data) {
                $this->lockApplication($application);
                abort_unless($application->allowsDocumentCorrections(), 409);
                $correction = $document->corrections()->whereNull('resolved_at')->find($data['correction']);
                abort_unless($correction, 409);
                $document->refresh();
                $oldPath = $document->disk_path;
                $document->update([
                    'disk_path' => $path, 'original_name' => $file->getClientOriginalName(),
                    'mime_type' => 'application/pdf', 'size' => $file->getSize(),
                ]);
                $correction->update(['resolved_at' => now()]);

                return $oldPath;
            }, 3);
        } catch (Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }

        // Initial documents may point to the candidate's reusable profile files.
        if (str_starts_with($oldPath, 'applications/'.$application->id.'/')
            && ! ApplicationDocument::where('disk_path', $oldPath)->exists()) {
            $disk->delete($oldPath);
        }

        return back()->with('success', 'Pièce remplacée. La commission peut consulter le nouveau PDF.');
    }

    private function lockApplication(Application $application): void
    {
        Application::whereKey($application->id)->toBase()->update(['id' => $application->id]);
        $application->refresh();
    }
}
