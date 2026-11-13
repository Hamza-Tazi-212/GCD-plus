<?php

namespace App\Enums;

enum UserRole: int
{
    case Admin = 1;
    case Professor = 2;
    case Candidate = 3;
    case Staff = 4;

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Professor => 'Encadrant',
            self::Candidate => 'Candidat',
            self::Staff => 'Personnel',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-dark',
            self::Professor => 'bg-primary',
            self::Candidate => 'bg-success',
            self::Staff => 'bg-primary',
        };
    }

    public static function fromName(string $name): ?self
    {
        return match (strtolower($name)) {
            'admin', 'administrator' => self::Admin,
            'professor' => self::Professor,
            'candidate' => self::Candidate,
            'staff' => self::Staff,
            default => null,
        };
    }
}
