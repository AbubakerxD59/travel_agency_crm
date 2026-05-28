<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID keys for Web Push
    |--------------------------------------------------------------------------
    |
    | Generate keys with: php artisan webpush:generate-vapid-keys
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', env('MAIL_FROM_ADDRESS', 'mailto:hello@example.com')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

];
