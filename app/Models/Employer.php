<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employer extends Model
{
    protected $table = "employers";
    protected $fillable = [
        'name',
        'talent_id'
    ];
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

}
