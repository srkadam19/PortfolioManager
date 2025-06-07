<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = "videos";
    protected $fillable = [
        'url',
        'employer_id',
        'project_id'
    ];
}
