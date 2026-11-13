<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenewalApproval extends Model
{
    protected $fillable = ['function', 'user_id', 'approved', 'comment'];

    protected function casts(): array
    {
        return ['approved' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
