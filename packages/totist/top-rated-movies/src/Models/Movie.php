<?php

namespace Totist\TopRatedMovies\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    /**
     * The genres that belong to the movie.
     *
     * @return BelongsToMany
     */
    public function genres() : BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /**
     * The directors that belong to the movie.
     *
     * @return BelongsToMany
     */
    public function directors() : BelongsToMany
    {
        return $this->belongsToMany(Director::class);
    }
}
