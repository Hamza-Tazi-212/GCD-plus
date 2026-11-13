<?php

namespace App\Models;

use App\Enums\StaffFunction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'research_subject_id', 'function'];

    protected function casts(): array
    {
        return ['function' => StaffFunction::class];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(ResearchSubject::class, 'research_subject_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
