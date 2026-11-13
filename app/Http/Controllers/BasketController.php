<?php

namespace App\Http\Controllers;

use App\Models\CampaignPeriod;
use App\Models\ResearchSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BasketController extends Controller
{
    public function index(Request $request): View
    {
        $ids = $request->session()->get('basket', []);
        $subjects = ResearchSubject::open()->with('department')->whereKey($ids)->get();
        $unavailable = array_diff($ids, $subjects->modelKeys());

        $applicationPeriods = CampaignPeriod::where('phase', 'application')
            ->whereIn('academic_year', $subjects->pluck('campaign')->unique())->get()->keyBy('academic_year');

        return view('basket', compact('subjects', 'unavailable', 'applicationPeriods'));
    }

    public function add(Request $request, ResearchSubject $subject): RedirectResponse
    {
        abort_unless($subject->is_open, 404);
        $basket = $request->session()->get('basket', []);
        if (! in_array($subject->id, $basket, true)) {
            $request->session()->push('basket', $subject->id);
        }

        return back()->with('basket_status', 'Sujet ajouté au panier.');
    }

    public function remove(Request $request, int $subject): RedirectResponse
    {
        $request->session()->put('basket', array_values(array_diff($request->session()->get('basket', []), [$subject])));

        return to_route('basket');
    }
}
