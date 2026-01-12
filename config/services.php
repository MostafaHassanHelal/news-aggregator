<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | News API Services
    |--------------------------------------------------------------------------
    |
    | Configuration for external news API providers.
    |
    */

    'newsapi' => [
        'key' => env('NEWSAPI_KEY'),
        'base_url' => env('NEWSAPI_BASE_URL', 'https://newsapi.org/v2'),
    ],

    'guardian' => [
        'key' => env('GUARDIAN_API_KEY'),
        'base_url' => env('GUARDIAN_BASE_URL', 'https://content.guardianapis.com'),
    ],

    'nytimes' => [
        'key' => env('NYTIMES_API_KEY'),
        'base_url' => env('NYTIMES_BASE_URL', 'https://api.nytimes.com/svc/search/v2'),
    ],

];
