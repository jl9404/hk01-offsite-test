<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paypal' => [
        'environment' => 'sandbox',
        'client_id' => 'ASTlBkP8njEizlY3tB8wPwkG-SmJjgUJonNktt2LylsUMtoMetQ20TRVccPDufSnI5RPeZXJwxfbYuV6',
        'client_secret' => 'EAydhog-NNPm-1yivRpageXknbEkqSOCcDmJx8z1r-NacKSlcwbrs8-Wh_nMI2mQc9Sx3kmZWK18CMMS',
    ],

    'braintree' => [
        'environment' => 'sandbox',
        'merchant_id' => 'srg75yzvspzvfv5g',
        'public_key' => 'ksdq2h4ygckzv7jw',
        'private_key' => '3ca4ce94d81f26da67b3333058c42795',
    ],

];
