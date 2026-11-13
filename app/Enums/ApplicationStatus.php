<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Waitlisted = 'waitlisted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::UnderReview => 'Admissible à l’oral',
            self::Waitlisted => 'Sur liste d’attente',
            self::Accepted => 'Retenue',
            self::Rejected => 'Refusée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-secondary',
            self::UnderReview => 'bg-primary',
            self::Waitlisted => 'bg-warning text-dark',
            self::Accepted => 'bg-success',
            self::Rejected => 'bg-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-hourglass-split',
            self::UnderReview => 'bi-search',
            self::Waitlisted => 'bi-list-ol',
            self::Accepted => 'bi-check-circle-fill',
            self::Rejected => 'bi-x-circle-fill',
        };
    }
}
