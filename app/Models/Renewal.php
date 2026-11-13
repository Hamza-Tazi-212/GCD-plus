<?php

namespace App\Models;

use App\Enums\StaffFunction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Renewal extends Model
{
    public const LABELS = [
        'correction' => 'Complément demandé',
        'submitted' => 'Avis de l’encadrant attendu',
        'favorable' => 'Avis favorable de l’encadrant',
        'unfavorable' => 'Avis défavorable · décision administrative attendue',
        'confirmed' => 'Réinscription confirmée',
        'rejected' => 'Réinscription refusée',
    ];

    protected $fillable = ['academic_year', 'report', 'extension'];

    protected function casts(): array
    {
        return ['correction_history' => 'array'];
    }

    public const APPROVALS = [StaffFunction::Thesis, StaffFunction::Lab, StaffFunction::Ced, StaffFunction::Head];

    public function approvals(): HasMany
    {
        return $this->hasMany(RenewalApproval::class);
    }

    public function hasRequiredApprovals(): bool
    {
        foreach (self::APPROVALS as $function) {
            if (! $this->approvals->contains(fn ($approval) => $approval->function === $function->value && $approval->approved)) {
                return false;
            }
        }

        return true;
    }

    public const EXTENSION_DECISION = 'extension_head';

    public function extensionDecision(): ?RenewalApproval
    {
        return $this->approvals->firstWhere('function', self::EXTENSION_DECISION);
    }

    public function doctoralEnrolment(): BelongsTo
    {
        return $this->belongsTo(DoctoralEnrolment::class);
    }
}
