<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    protected $table = "sections";
    public function talent()
    {
        return $this->belongsTo(Talent::class);
    }
}
