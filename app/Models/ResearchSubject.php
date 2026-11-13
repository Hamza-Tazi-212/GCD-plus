<?php

namespace App\Models;

use App\Enums\StaffFunction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'professor_id',
        'department_id',
        'campaign',
        'laboratory',
        'field',
        'title',
        'description',
        'responsibilities',
        'candidate_profile',
        'keywords',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
        ];
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'subject_id');
    }

    public const APPROVALS = [StaffFunction::Lab, StaffFunction::Ced];

    private const REVIEW_FIELDS = ['title', 'description', 'responsibilities', 'candidate_profile', 'keywords', 'department_id', 'professor_id', 'campaign'];

    protected static function booted(): void
    {
        static::updating(function (self $subject) {
            if ($subject->isDirty(self::REVIEW_FIELDS)) {
                $subject->is_open = false;
            }
        });
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SubjectApproval::class);
    }

    public function approvalRevision(): string
    {
        return hash('sha256', json_encode($this->only(self::REVIEW_FIELDS), JSON_THROW_ON_ERROR));
    }

    public function togglePublication(): void
    {
        DB::transaction(function () {
            self::whereKey($this->id)->toBase()->update(['id' => $this->id]);
            $this->refresh();
            if (! $this->is_open) {
                $approvals = $this->approvals()->where('revision', $this->approvalRevision())->where('approved', true)->pluck('function');
                foreach (self::APPROVALS as $function) {
                    if (! $approvals->contains($function->value)) {
                        throw ValidationException::withMessages(['publication' => 'La structure de recherche et le CED doivent approuver cette version du sujet.']);
                    }
                }
            }
            $this->update(['is_open' => ! $this->is_open]);
        }, 3);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_open', true);
    }
}
