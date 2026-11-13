<?php

namespace App\Http\Controllers;

use App\Models\CampaignPeriod;
use App\Models\EnrolmentChecklist;
use App\Models\EnrolmentDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignCalendarController extends Controller
{
    public function index()
    {
        return view('calendar', ['periods' => $this->periods()]);
    }

    public function edit()
    {
        return view('admin.calendar', [
            'periods' => $this->periods(),
            'checklists' => EnrolmentChecklist::orderByDesc('campaign')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'regex:/^20\d{2}[–-]20\d{2}$/u', function ($attribute, $value, $fail) {
                if ((int) substr($value, 0, 4) + 1 !== (int) substr($value, -4)) {
                    $fail('Indiquez deux années consécutives.');
                }
            }],
            'phase' => ['required', Rule::in(array_keys(CampaignPeriod::PHASES))],
            'opens_on' => ['required', 'date_format:Y-m-d'],
            'closes_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:opens_on'],
        ]);

        CampaignPeriod::upsert([[
            'academic_year' => str_replace('-', '–', $data['academic_year']),
            'phase' => $data['phase'],
            'opens_on' => $data['opens_on'],
            'closes_on' => $data['closes_on'],
        ]], ['academic_year', 'phase'], ['opens_on', 'closes_on']);

        return back()->with('success', 'Période enregistrée.');
    }

    public function storeChecklist(Request $request)
    {
        $data = $request->validate([
            'campaign' => ['bail', 'required', 'string', 'regex:/^20\d{2}[–-]20\d{2}$/u', function ($attribute, $value, $fail) {
                if ((int) substr($value, 0, 4) + 1 !== (int) substr($value, -4)) {
                    $fail('Indiquez deux années consécutives.');
                }
            }],
            'kinds' => ['required', 'array', 'min:1'],
            'kinds.*' => ['required', 'distinct', Rule::in(array_keys(EnrolmentDocument::AVAILABLE))],
        ]);

        EnrolmentChecklist::upsert([[
            'campaign' => str_replace('-', '–', $data['campaign']),
            'kinds' => json_encode(array_values($data['kinds']), JSON_THROW_ON_ERROR),
        ]], ['campaign'], ['kinds']);

        return back()->with('success', 'Liste de pièces enregistrée pour cette campagne.');
    }

    private function periods()
    {
        return CampaignPeriod::orderByDesc('academic_year')->orderBy('opens_on')->paginate(20);
    }
}
