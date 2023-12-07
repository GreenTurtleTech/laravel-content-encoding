<?php

declare(strict_types=1);

use GreenTurtle\Middleware\Encoder;

return [
    /*
    |--------------------------------------------------------------------------
    | Encode Unknown Content Type
    |--------------------------------------------------------------------------
    |
    | A boolean value
    | false ~ Do not encode if the 'Content-Type' header is missing
    | true ~ Try to encode when the 'Content-Type' header is missing
    */
    'encode_unknown_type' => false,

    /*
    |--------------------------------------------------------------------------
    | Allowed Content Types
    |--------------------------------------------------------------------------
    |
    | An array of string regex patterns
    | Specifies the content types allowed for encoding
    | Any content type that matches one of the regex patterns is allowed
    |
    */
    'allowed_types' => [
        '#^(text\/.*)(;.*)?$#',
        '#^(image\/svg\\+xml)(;.*)?$#',
        '#^(application\/json)(;.*)?$#',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Encoders
    |--------------------------------------------------------------------------
    |
    | An array of ContentEncoder implementations
    | Specifies which encodings are available to the middleware
    | The order determines which available encodings take priority
    |
    */
    'encoders' => [
        Encoder\Gzip::class,
        Encoder\Deflate::class,
    ],
];
