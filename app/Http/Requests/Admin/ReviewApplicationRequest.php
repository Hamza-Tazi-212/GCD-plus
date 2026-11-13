<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        if ($this->routeIs('staff.application.review')) {
            return $this->user()->hasStaffAssignment(\App\Enums\StaffFunction::Selection, $application->subject_id);
        }

        return $this->user()?->can('review', $application) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['under_review', 'waitlisted', 'accepted', 'rejected'])],
            'waitlist_rank' => ['exclude_unless:status,waitlisted', 'required', 'integer', 'min:1', 'max:999999'],
            'comment' => ['nullable', 'required_if:status,rejected', 'string', 'max:2000'],
            // Only meaningful (and required) when inviting the candidate to
            // the oral exam — that's the only status the candidate needs a
            // date for.
            'oral_exam_date' => ['nullable', 'required_if:status,under_review', 'date', 'after_or_equal:today'],

            // Same idea for the final acceptance — the program start date
            // the candidate is told to show up for.
            'program_start_date' => ['nullable', 'required_if:status,accepted', 'date', 'after_or_equal:today'],
        ];
    }
}
