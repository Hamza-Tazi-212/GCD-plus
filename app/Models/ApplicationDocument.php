<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'type',
        'disk_path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
        ];
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(ApplicationDocumentCorrection::class)->latest('id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
