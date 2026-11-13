<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectApproval extends Model
{
    protected $fillable = ['user_id', 'function', 'revision', 'approved', 'comment'];
}
