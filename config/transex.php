<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Transex API Key
    |--------------------------------------------------------------------------
    |
    | The API Key used as a Bearer Token for authenticating with the
    | Transexpress API. For production, retrieve from:
    | Client Portal -> My Profile -> Update Account -> API Key
    |
    */
    'api_key' => env('TRANSEX_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Transex Environment
    |--------------------------------------------------------------------------
    |
    | Controls which Transexpress API base URL is used.
    | Supported: "staging", "production"
    |
    */
    'env' => env('TRANSEX_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Transex Base URL
    |--------------------------------------------------------------------------
    |
    | Automatically resolved from the environment setting above.
    |
    */
    'base_url' => env('TRANSEX_ENV', 'production') === 'staging'
        ? 'https://dev-transexpress.parallaxtec.com/api'
        : 'https://portal.transexpress.lk/api',

];
