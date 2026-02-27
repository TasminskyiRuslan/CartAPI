<?php

namespace App\Swagger\Schemas\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItemResponse',
    title: 'Cart Item Response',
    description: 'Data for a single item in the cart.',
    required: ['id', 'price_snapshot', 'quantity', 'total_price', 'product'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the cart item.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'price_snapshot',
            description: 'Product price at the time it was added to the cart.',
            type: 'string',
            format: 'float',
            example: 999.99
        ),
        new OA\Property(
            property: 'quantity',
            description: 'Quantity of the product in the cart.',
            type: 'integer',
            example: 2
        ),
        new OA\Property(
            property: 'total_price',
            description: 'Total price for this item (price_snapshot * quantity).',
            type: 'string',
            format: 'float',
            example: 1999.98
        ),
        new OA\Property(
            property: 'product',
            ref: '#/components/schemas/ProductResponse',
            description: 'Product details.'
        )
    ],
    type: 'object'
)]
class CartItemResponseSchema
{

}
