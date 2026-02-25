<?php

return [
    'guest_header' => env('CART_GUEST_HEADER', 'X-Guest-Token'),
    'expiration_days' => [
        'guest' => (int) env('CART_GUEST_EXPIRATION_DAYS', 7),
        'user' => (int) env('CART_USER_EXPIRATION_DAYS', 30),
    ],
    'max_quantity' => (int) env('CART_MAX_QUANTITY', 99),
];
