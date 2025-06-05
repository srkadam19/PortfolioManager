<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $table = "employers";
    protected $fillable = [
        'name',
        'talent_id'
    ];
}
