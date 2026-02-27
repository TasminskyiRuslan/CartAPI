<?php

namespace App\Swagger\Schemas\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartResponse',
    title: 'Cart Response',
    description: 'Data for a user cart.',
    required: ['id', 'items', 'total_price', 'total_count'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the cart.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'items',
            description: 'List of items in the cart.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CartItemResponse')
        ),
        new OA\Property(
            property: 'total_price',
            description: 'Total price of all items in the cart.',
            type: 'string',
            format: 'float',
            example: 1999.98
        ),
        new OA\Property(
            property: 'total_count',
            description: 'Total number of items in the cart.',
            type: 'integer',
            example: 2
        ),
    ],
    type: 'object'
)]
class CartResponseSchema
{

}
