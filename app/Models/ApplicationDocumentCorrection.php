<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocumentCorrection extends Model
{
    protected $fillable = ['requested_by', 'reason', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }
}
