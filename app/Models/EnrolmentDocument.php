<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrolmentDocument extends Model
{
    // Initial checklist for the local pilot.
    public const REQUIRED = [
        'identity' => 'Pièce d’identité',
        'diploma' => 'Diplôme donnant accès au doctorat',
        'charter' => 'Charte des thèses signée',
    ];

    public const AVAILABLE = self::REQUIRED + [
        'registration_form' => 'Fiche d’inscription',
        'transcript' => 'Relevé de notes',
        'student_declaration' => 'Engagement sur la situation du doctorant',
    ];

    protected $fillable = ['kind', 'path', 'status', 'comment', 'reviewed_by', 'reviewed_at'];
}
