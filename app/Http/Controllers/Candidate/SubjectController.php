<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ResearchSubject;
use App\Services\SubjectCatalogue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request, SubjectCatalogue $catalogue): View
    {
        return view('candidate.subjects.index', $catalogue->listing($request));
    }

    public function show(ResearchSubject $subject): View
    {
        $subject->load(['professor', 'department']);

        $alreadyApplied = $subject->applications()
            ->where('candidate_id', auth()->id())
            ->exists();

        return view('candidate.subjects.show', [
            'subject' => $subject,
            'alreadyApplied' => $alreadyApplied,
            'profileComplete' => auth()->user()->profile?->isComplete() ?? false,
        ]);
    }
}
