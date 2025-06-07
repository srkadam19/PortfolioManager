<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    protected $table = "skills";
    public function talent()
    {
        return $this->belongsTo(Talent::class);
    }
}
