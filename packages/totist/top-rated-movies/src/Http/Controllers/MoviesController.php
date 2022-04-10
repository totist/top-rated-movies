<?php

namespace Totist\TopRatedMovies\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Totist\TopRatedMovies\Models\Director;
use Totist\TopRatedMovies\Models\Genre;
use Totist\TopRatedMovies\Models\Movie;

class MoviesController extends Controller
{
    /**
     * Retrieve movies from TheMovieDB
     *
     * @return View
     */
    public function index() : View
    {
        $this->update();
        return view('top-rated-movies::index');
    }

    /**
     * Update database (use this method with CRON)
     */
    public function update(): void
    {
        $savedGenres = $this->updateGenres();
        $savedTopRatedMovies = $this->updateTopRatedMovies($savedGenres);
        $this->updateDirectors($savedTopRatedMovies);
    }

    /**
     * Update genres in the database
     *
     * @return array
     */
    protected function updateGenres() : array
    {
        $genres = $this->getGenresFromTMDB();
        $savedGenres = Genre::all()->keyBy('id')->all();

        foreach ($genres as $genreId => $genre) {
            if (!array_key_exists($genreId, $savedGenres)) {
                $genreObj = new Genre();
                $genreObj->id = $genreId;
                $savedGenres[$genreId] = $genreObj;
            } else {
                $genreObj = $savedGenres[$genreId];
            }

            $genreObj->name = $genre['name'];
            $genreObj->save();

            // this is necessary because after saving, the value of the ID will be 0
            $genreObj->id = $genreId;
        }

        $delete = array_diff(array_keys($savedGenres), array_keys($genres));
        foreach ($delete as $genreId) {
            unset($savedGenres[$genreId]);

            $genre = Genre::find($genreId);
            $genre->movies()->detach();
            $genre->delete();
        }

        return $savedGenres;
    }

    /**
     * Update movies and related genres in the database
     *
     * @param $savedGenres
     * @return array
     */
    protected function updateTopRatedMovies($savedGenres) : array
    {
        $maxItem = config('tmdb.list_size');
        $topRatedMovies = $this->getTopRatedMoviesFromTMDB($maxItem);
        $details = $this->getMoviesDetailsFromTMDB(array_keys($topRatedMovies));
        $savedTopRatedMovies = Movie::all()->keyBy('tmdb_id')->all();

        foreach ($topRatedMovies as $movieId => $topRatedMovie) {
            if (!array_key_exists($topRatedMovie->id, $savedTopRatedMovies)) {
                $movie = new Movie();
                $savedTopRatedMovies[$movieId] = $movie;
            } else {
                $movie = $savedTopRatedMovies[$movieId];
            }

            $movie->title = $topRatedMovie->title;
            $movie->release_date = $topRatedMovie->release_date;
            $movie->overview = $topRatedMovie->overview;
            $movie->poster_url = sprintf('https://image.tmdb.org/t/p/original%s', $topRatedMovie->poster_path);
            $movie->tmdb_id = $topRatedMovie->id;
            $movie->tmdb_vote_average = $topRatedMovie->vote_average;
            $movie->tmdb_vote_count = $topRatedMovie->vote_count;
            $movie->tmdb_url = sprintf('https://www.themoviedb.org/movie/%d', $topRatedMovie->id);
            $movie->length = $details[$topRatedMovie->id]->runtime;
            $movie->save();

            $relatedGenres = [];
            foreach ($topRatedMovie->genre_ids as $genreId) {
                if (array_key_exists($genreId, $savedGenres)) {
                    $relatedGenres[] = $savedGenres[$genreId];
                }
            }
            $movie->genres()->detach();
            $movie->genres()->saveMany($relatedGenres);
        }

        $delete = array_diff(array_keys($savedTopRatedMovies), array_keys($topRatedMovies));
        foreach ($delete as $movieId) {
            unset($savedTopRatedMovies[$movieId]);

            $movieObj = Movie::where('tmdb_id', $movieId)->get()->first();
            $movieObj->genres()->detach();
            $movieObj->directors()->detach();
            $movieObj->delete();
        }

        return $savedTopRatedMovies;
    }

    /**
     * Update directors and related movies in the database
     *
     * @param $savedTopRatedMovies
     * @return array
     */
    protected function updateDirectors($savedTopRatedMovies) : array
    {
        $directorList = $this->getDirectorsFromTMDB(array_keys($savedTopRatedMovies));
        $savedDirectors = Director::all()->keyBy('id')->all();
        $directorIds = [];
        $duplications = [];

        foreach ($directorList as $movieId => $directors) {
            $relatedDirectors = [];
            foreach ($directors as $director) {
                if (in_array($director['id'], $duplications)) {
                    continue;
                }
                $duplications[] = $director['id'];

                if (!array_key_exists($director['id'], $savedDirectors)) {
                    $directorObj = new Director();
                    $directorObj->id = $director['id'];

                    $savedDirectors[$director['id']] = $directorObj;
                } else {
                    $directorObj = $savedDirectors[$director['id']];
                }

                $directorObj->name = $director['name'];
                $directorObj->biography = $director['biography'];
                $directorObj->birthday = $director['birthday'];
                $directorObj->save();

                // this is necessary because after saving, the value of the ID will be 0
                $directorObj->id = $director['id'];

                $directorIds[] = $director['id'];
                $relatedDirectors[] = $directorObj;
            }

            $movie = Movie::where('tmdb_id', $movieId)->get()->first();
            $movie->directors()->detach();
            $movie->directors()->saveMany($relatedDirectors);
        }

        $delete = array_diff(array_keys($savedDirectors), $directorIds);
        foreach ($delete as $directorId) {
            unset($savedDirectors[$directorId]);

            $director = Director::find($directorId);
            $director->movies()->detach();
            $director->delete();
        }

        return $savedDirectors;
    }

    /**
     * Retrieve top movies from TheMovieDB
     *
     * @param int $maxItem
     * @return array
     */
    protected function getTopRatedMoviesFromTMDB(int $maxItem) : array
    {
        $itemPerPage = 20;
        $pages = $maxItem / $itemPerPage;
        $rest = $maxItem % $itemPerPage;
        $pages += ($rest > 0 ? 1 : 0);

        $topRatedMovies = Http::pool(function (Pool $pool) use ($pages) {
            $queries = [];
            for ($index = 1; $index <= $pages; $index++) {
                $queries[] = $pool->withToken(config('tmdb.api_key'))
                    ->get(config('tmdb.api_url') . '/movie/top_rated', [
                        'page' => $index,
                    ]);
            }
            return $queries;
        });

        $movies = [];
        foreach ($topRatedMovies as $page) {
            if ($page->ok()) {
                foreach ($page->object()->results as $movie) {
                    if (count($movies) >= $maxItem) {
                        break;
                    }
                    $movies[$movie->id] = $movie;
                }
            }
        }

        return $movies;
    }

    /**
     * Retrieve genres from TheMovieDB
     *
     * @return array|mixed
     */
    protected function getGenresFromTMDB()
    {
        $genres = Http::withToken(config('tmdb.api_key'))
            ->get(config('tmdb.api_url') . '/genre/movie/list')
            ->json()['genres'];

        $indexedGenres = [];
        foreach ($genres as $genre) {
            $indexedGenres[$genre['id']] = $genre;
        }

        return $indexedGenres;
    }

    /**
     * Retrieve movie details from TheMovieDB
     *
     * @param $movieIds
     * @return array
     */
    protected function getMoviesDetailsFromTMDB($movieIds) : array
    {
        $details = Http::pool(function (Pool $pool) use ($movieIds)
        {
            $queries = [];
            foreach ($movieIds as $movieId) {
                $queries[] = $pool->withToken(config('tmdb.api_key'))
                    ->get(config('tmdb.api_url') . sprintf('/movie/%d', $movieId));
            }
            return $queries;
        });

        $result = [];
        foreach ($details as $detail) {
            if ($detail->ok()) {
                $result[$detail->object()->id] = $detail->object();
            }
        }

        return $result;
    }

    /**
     * Retrieve directors from TheMovieDB
     *
     * @param $movieIds
     * @return array
     */
    protected function getDirectorsFromTMDB($movieIds) : array
    {
        $credits = Http::pool(function (Pool $pool) use ($movieIds)
        {
            $queries = [];
            foreach ($movieIds as $movieId) {
                $queries[] = $pool->withToken(config('tmdb.api_key'))
                    ->get(config('tmdb.api_url') . sprintf('/movie/%d/credits', $movieId));
            }
            return $queries;
        });

        $directors = [];
        foreach ($credits as $credit) {
            if ($credit->ok()) {
                $movieId = $credit->object()->id;
                $crew = $credit->object()->crew;
                $directors[$movieId] = [];

                foreach ($crew as $member) {
                    if ($member->job == 'Director') {
                        $person = Http::withToken(config('tmdb.api_key'))
                            ->get(config('tmdb.api_url') . sprintf('/person/%d', $member->id))
                            ->json();

                        $directors[$movieId][] = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'biography' => $person['biography'],
                            'birthday' => $person['birthday'],
                        ];
                    }
                }
            }
        }

        return $directors;
    }
}
