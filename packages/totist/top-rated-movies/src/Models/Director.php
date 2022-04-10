<?php

namespace Totist\TopRatedMovies\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Director extends Model
{
    use HasFactory;

    /**
     * The movies that belong to the director.
     *
     * @return BelongsToMany
     */
    public function movies() : BelongsToMany
    {
        return $this->belongsToMany(\Movie::class);
    }
}
