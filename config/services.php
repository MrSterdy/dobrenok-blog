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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tbank' => [
        'terminal_key' => env('TBANK_TERMINAL_KEY'),
        'password' => env('TBANK_PASSWORD'),
        'api_url' => env('TBANK_API_URL', 'https://securepay.tinkoff.ru/v2'),
        'receipt' => [
            'taxation' => env('TBANK_TAXATION', 'usn_income'), // osn, usn_income, usn_income_outcome, envd, esn, patent
            'tax' => env('TBANK_TAX', 'none'), // none, vat0, vat10, vat20, vat110, vat120
            'payment_method' => env('TBANK_PAYMENT_METHOD', 'full_payment'),
            'payment_object' => env('TBANK_PAYMENT_OBJECT', 'payment'),
        ],
    ],

];
