<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Totist\TopRatedMovies\Models\Genre;
use Totist\TopRatedMovies\Models\Movie;

class TMDBApiTest extends TestCase
{
    public function test_genres_endpoint()
    {
        $response = Http::withToken(config('tmdb.api_key'))
            ->get(config('tmdb.api_url') . '/genre/movie/list');
        $this->assertEquals($response->status(), 200);

        $this->assertJson($response->body());
        $this->assertArrayHasKey('genres', $response->json());
    }

    public function test_movie_details_endpoint()
    {
        $movieIds = [278, 19404];
        $response = Http::pool(function (Pool $pool) use ($movieIds)
        {
            $queries = [];
            foreach ($movieIds as $movieId) {
                $queries[] = $pool->withToken(config('tmdb.api_key'))
                    ->get(config('tmdb.api_url') . sprintf('/movie/%d', $movieId));
            }
            return $queries;
        });

        $this->assertIsArray($response);
        $this->assertCount(count($movieIds), $response);

        foreach ($response as $item) {
            $this->assertEquals($item->status(), 200);
            $this->assertJson($item->body());

            $jsonBody = $item->json();
            $this->assertArrayHasKey('id', $jsonBody);
            $this->assertArrayHasKey('runtime', $jsonBody);
        }
    }

    public function test_genre_objects()
    {
        $this->assertModelExists(Genre::find(18)); // Drama

        $genres = Genre::all()->keyBy('id')->all();
        if (count($genres) > 0) {
            $firstKey = array_key_first($genres);
            $this->assertEquals($firstKey, $genres[$firstKey]->id);
        }

        $genreByFactory = Genre::factory()->make();
        $result = DB::table('genre_movie')
            ->where('genre_id', $genreByFactory->id)
            ->get();

        $this->assertEquals(count($result), count($genreByFactory->movies));
    }

    public function test_movie_objects()
    {
        $movies = Movie::all();
        $this->assertEquals(count($movies), config('tmdb.list_size'));
    }
}
