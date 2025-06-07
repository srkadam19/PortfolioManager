<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Talent extends Model
{
    protected $table = "talents";
    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'location',
        'profile_image_url',
        'bio',
    ];

    public function employers(): HasMany
    {
        return $this->hasMany(Employer::class);
    }

    // Define the relationship between Talent and Videos (through Employers)
    public function videos()
    {
        return $this->hasManyThrough(Video::class, Employer::class);
    }

    public function sections()
    {
        return $this->hasMany(Sections::class);
    }
    public function skills()
    {
        return $this->hasMany(Skills::class);
    }
}
