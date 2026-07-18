<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API.Bible
    |--------------------------------------------------------------------------
    |
    | Scripture text for sermon passages and reading plans.
    | Keys are issued at https://scripture.api.bible.
    |
    */

    'url' => env('BIBLE_API_URL', 'https://rest.api.bible'),

    'key' => env('BIBLE_API_KEY'),

    /*
    | Default translation. This key has access to NLT and NKJV as well as the
    | public-domain versions; NLT is the default as it reads most naturally.
    */
    'default_bible' => env('BIBLE_API_DEFAULT_BIBLE', 'd6e14a625393b4da-01'),

    /*
    | Translations offered to members. The copyright notice returned by the API
    | must be displayed alongside the text for licensed translations.
    */
    'translations' => [
        'NLT' => ['id' => 'd6e14a625393b4da-01', 'name' => 'New Living Translation'],
        'NKJV' => ['id' => '63097d2a0a2f7db3-01', 'name' => 'New King James Version'],
        'KJV' => ['id' => 'de4e12af7f28f599-01', 'name' => 'King James Version'],
        'WEB' => ['id' => '9879dbb7cfe39e4d-01', 'name' => 'World English Bible'],
    ],

    /*
    | Scripture text never changes, so responses are cached for a long time.
    | This keeps the app fast and well inside the API's rate limits.
    */
    'cache_ttl' => env('BIBLE_API_CACHE_TTL', 60 * 60 * 24 * 30),

    'timeout' => env('BIBLE_API_TIMEOUT', 8),

];
