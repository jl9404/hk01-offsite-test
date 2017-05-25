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
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paypal' => [
        'client_id' => 'Accz-iQK0BO67LZQDbr_aoUTEFLJynflxF6j03a2xT1MbEPwh7t4hQZXOHM8B67BNpKAsGc4YVo27zy3',
        'client_secret' => 'EDDDiU81ujiD4rzpW9TsumnkWV99gb04p4y96jlovBm5neCxv8lUXf08XoQ7YbKtKf-Ev0Nqa9Vz9_Ed',
    ],

    'braintree' => [
        'environment' => 'sandbox',
        'merchant_id' => 'srg75yzvspzvfv5g',
        'public_key' => 'ksdq2h4ygckzv7jw',
        'private_key' => '3ca4ce94d81f26da67b3333058c42795',
    ],

];
