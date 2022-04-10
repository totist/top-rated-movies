<?php

namespace Totist\TopRatedMovies\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name'];

    /**
     * The movies that belong to the genre.
     *
     * @return BelongsToMany
     */
    public function movies() : BelongsToMany
    {
        return $this->belongsToMany(\Movie::class);
    }
}
