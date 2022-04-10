<?php

return [
    'api_key' => env('TMDB_API_KEY'),
    'api_url' => env('TMDB_API_URL', 'https://api.themoviedb.org/3'),
    'list_size' => env('TMDB_LIST_SIZE', 210),
];
