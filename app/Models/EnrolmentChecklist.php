<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrolmentChecklist extends Model
{
    public $timestamps = false;

    protected $fillable = ['campaign', 'kinds'];

    protected function casts(): array
    {
        return ['kinds' => 'array'];
    }

    public static function forCampaign(string $campaign): array
    {
        $kinds = static::where('campaign', $campaign)->first()?->kinds;

        return $kinds === null ? EnrolmentDocument::REQUIRED : array_intersect_key(EnrolmentDocument::AVAILABLE, array_flip($kinds));
    }
}
