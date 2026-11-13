<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CampaignPeriod extends Model
{
    public const PHASES = [
        'application' => 'Candidature',
        'enrolment' => 'Inscription administrative',
        'renewal' => 'Réinscription',
    ];

    public $timestamps = false;

    protected $fillable = ['academic_year', 'phase', 'opens_on', 'closes_on'];

    public function statusLabel(): string
    {
        return $this->isOpen() ? 'Ouverte'
            : (now('Africa/Casablanca')->toDateString() < $this->opens_on ? 'À venir' : 'Clôturée');
    }

    public function isOpen(): bool
    {
        $today = now('Africa/Casablanca')->toDateString();

        return $today >= $this->opens_on && $today <= $this->closes_on;
    }

    public static function ensureOpen(string $year, string $phase, string $field): void
    {
        $period = self::where(['academic_year' => $year, 'phase' => $phase])->first();
        if ($period && ! $period->isOpen()) {
            throw ValidationException::withMessages([
                $field => 'La période de '.mb_strtolower(self::PHASES[$phase]).' pour '.$year.' est fermée. Consultez le calendrier.',
            ]);
        }
    }
}
