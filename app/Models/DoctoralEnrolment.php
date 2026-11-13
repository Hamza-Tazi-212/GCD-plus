<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctoralEnrolment extends Model
{
    protected $fillable = ['application_id', 'academic_year', 'confirmed_by'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(Renewal::class);
    }

    public function nextAcademicYear(): string
    {
        $last = ($this->relationLoaded('renewals')
            ? $this->renewals->where('status', 'confirmed')->max('academic_year')
            : $this->renewals()->where('status', 'confirmed')->max('academic_year')) ?? $this->academic_year;
        $start = (int) substr($last, 0, 4) + 1;

        return $start.'–'.($start + 1);
    }
}
