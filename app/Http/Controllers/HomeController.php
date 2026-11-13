<?php

namespace App\Http\Controllers;

use App\Models\CampaignPeriod;
use App\Models\ResearchSubject;
use App\Services\SubjectCatalogue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, SubjectCatalogue $catalogue): View
    {
        $listing = $catalogue->listing($request);
        $listing['applicationPeriods'] = CampaignPeriod::where('phase', 'application')
            ->whereIn('academic_year', $listing['subjects']->pluck('campaign')->unique())->get()->keyBy('academic_year');

        return view('welcome', $listing);
    }

    public function show(ResearchSubject $subject): View
    {
        abort_unless($subject->is_open, 404);

        return view('public-subject', [
            'subject' => $subject->load(['professor', 'department']),
            'period' => CampaignPeriod::where(['academic_year' => $subject->campaign, 'phase' => 'application'])->first(),
        ]);
    }

    public function about(): View
    {
        return view('about');
    }
}
