<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\StaffFunction;
use App\Http\Requests\Admin\ReviewApplicationRequest;
use App\Models\Application;
use App\Models\ResearchSubject;
use App\Services\ApplicationDecisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffDecisionController extends Controller
{
    public function approveSubject(Request $request, ResearchSubject $subject, StaffFunction $function)
    {
        abort_unless(in_array($function, ResearchSubject::APPROVALS, true)
            && $request->user()->hasStaffAssignment($function, $subject->id), 403);
        $data = $request->validate([
            'approved' => ['required', 'boolean'],
            'revision' => ['required', 'size:64', 'string'],
            'comment' => ['required', 'string', 'max:4000'],
        ]);

        DB::transaction(function () use ($request, $subject, $function, $data) {
            ResearchSubject::whereKey($subject->id)->toBase()->update(['id' => $subject->id]);
            $subject->refresh();
            $revision = $subject->approvalRevision();
            abort_if($revision !== $data['revision'] || $subject->is_open || $subject->approvals()->where('revision', $revision)->where('function', $function->value)->exists(), 409);
            $subject->approvals()->create($data + [
                'revision' => $revision,
                'function' => $function->value,
                'user_id' => $request->user()->id,
            ]);
        }, 3);

        return back()->with('success', 'Avis sur le sujet enregistré.');
    }

    public function review(ReviewApplicationRequest $request, Application $application, ApplicationDecisionService $decisions)
    {
        abort_unless($request->user()->hasStaffAssignment(StaffFunction::Selection, $application->subject_id), 403);
        $decisions->decide(
            application: $application,
            status: ApplicationStatus::from($request->validated('status')),
            reviewer: $request->user(),
            comment: $request->validated('comment'),
            oralExamDate: $request->validated('oral_exam_date'),
            programStartDate: $request->validated('program_start_date'),
            waitlistRank: (int) $request->validated('waitlist_rank') ?: null,
        );

        return to_route('staff.application', $application)->with('success', 'Décision de sélection enregistrée.');
    }
}
