<?php

namespace App\Swagger\Schemas\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItem',
    title: 'Cart Item Schema',
    description: 'Details of a cart item returned by the API.',
    required: ['id', 'price_snapshot', 'quantity', 'total_price', 'product'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The product ID.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'price_snapshot',
            description: 'The product price at the time it was added to the cart.',
            type: 'string',
            format: 'float',
            example: 999.99
        ),
        new OA\Property(
            property: 'quantity',
            description: 'The quantity of the product in the cart.',
            type: 'integer',
            example: 2
        ),
        new OA\Property(
            property: 'total_price',
            description: 'The total price for this cart item (price_snapshot * quantity).',
            type: 'string',
            format: 'float',
            example: 1999.98
        ),
        new OA\Property(
            property: 'product',
            ref: '#/components/schemas/Product',
            description: 'The product details.'
        )
    ],
    type: 'object'
)]
class CartItemSchema
{

}
