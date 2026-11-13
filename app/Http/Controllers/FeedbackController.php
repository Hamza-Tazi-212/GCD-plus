<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Cap on submissions per user per calendar day, to stop a single
     * account from flooding the feedback inbox.
     */
    private const DAILY_LIMIT = 5;

    /**
     * Open to every authenticated role (candidate, professor, admin) —
     * each just sees their own submission history alongside the form.
     */
    public function index(Request $request): View
    {
        return view('feedback.index', [
            'feedbackItems' => Feedback::where('user_id', $request->user()->id)
                ->latest()
                ->get(),
            'submittedToday' => $this->submittedToday($request),
            'dailyLimit' => self::DAILY_LIMIT,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if ($this->submittedToday($request) >= self::DAILY_LIMIT) {
            return back()->withErrors([
                'message' => 'Vous avez atteint la limite quotidienne de '.self::DAILY_LIMIT.' remarques. Réessayez demain.',
            ])->withInput();
        }

        Feedback::create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return redirect()->route('feedback.index')->with('success', 'Merci pour votre remarque.');
    }

    private function submittedToday(Request $request): int
    {
        return Feedback::where('user_id', $request->user()->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }
}
