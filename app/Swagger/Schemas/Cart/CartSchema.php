<?php

namespace App\Swagger\Schemas\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Cart',
    title: 'Cart schema',
    description: 'The cart data returned by the API.',
    required: ['id', 'items', 'total_price', 'total_count'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The cart ID.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'items',
            description: 'The cart items.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CartItem')
        ),
        new OA\Property(
            property: 'total_price',
            description: 'The total price of the cart.',
            type: 'string',
            format: 'float',
            example: 1999.98
        ),
        new OA\Property(
            property: 'total_count',
            description: 'The total count of the cart.',
            type: 'integer',
            example: 2
        ),
    ],
    type: 'object'
)]
class CartSchema
{

}
