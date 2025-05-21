<?php

return [

    /*
   |--------------------------------------------------------------------------
   | Client Id
   |--------------------------------------------------------------------------
   |
   | This value is the consumer key provided for your developer application.
   | The package needs this to make requests to the TANDA APIs.
   |
   */

    'client_id' => env('TANDA_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    |
    | This value is the consumer secret provided for your developer application.
    | The package needs this to make requests to the TANDA APIs.
    |
    */

    'client_secret' => env('TANDA_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Organisation ID
    |--------------------------------------------------------------------------
    |
    | This value is the organisation id provided for your developer application.
    | The package needs this to make requests to the TANDA APIs.
    |
    */

    'organisation_id' => env('TANDA_ORG_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Package Mode
    |--------------------------------------------------------------------------
    |
    | This value sets the mode at which you are using the package. Acceptable
    | values are sandbox or live
    |
    */

    'mode' => 'live',

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Here you can set the Tanda Base URLs
    |
    */

    'auth_base_url' => env('TANDA_AUTH_BASE_URL', 'https://identity-uat.tanda.africa'),
    'api_base_url' => env('TANDA_API_BASE_URL', 'https://api-v3.tanda.africa'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Here you can set the Tanda Base URL
    |
    */

    'tanda_base_result_url' => env('TANDA_BASE_RESULT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | API C2B Result URL
    |--------------------------------------------------------------------------
    |
    | Here you can set the URLs that will handle the results from each of the
    | APIs from TANDA
    |
    */

    'c2b_result_url' => env('TANDA_C2B_RESULT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | API Payout Result URL
    |--------------------------------------------------------------------------
    |
    | Here you can set the URLs that will handle the results from each of the
    | APIs from TANDA
    |
    */

    'result_url' => env('TANDA_RESULT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | LOGS
    |--------------------------------------------------------------------------
    |
    | Here you can set your logging requirements. If enabled a new file will
    | will be created in the logs folder and will record all requests
    | and responses to the TANDA APIs. You can use the
    | the Monolog debug levels.
    |
    */

    'logs' => [
        'enabled' => true,
        'level'   => 'DEBUG',
    ],

];