<?php

return [
    'guest_header' => env('CART_GUEST_HEADER', 'X-Guest-Token'),
    'expiration_days' => [
        'guest' => (int) env('CART_GUEST_EXPIRATION_DAYS', 7),
        'user' => (int) env('CART_USER_EXPIRATION_DAYS', 30),
    ],
];
