<?php

namespace App\Swagger\Requests\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateCartItemRequest',
    title: 'Create Cart Item Request',
    description: 'Request payload for adding a product to the cart.',
    required: ['product_id'],
    properties: [
        new OA\Property(
            property: 'product_id',
            description: 'ID of the product to add.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'quantity',
            description: 'Quantity of the product.',
            type: 'integer',
            maximum: 99,
            minimum: 1,
            example: 2
        ),
    ],
    type: 'object'
)]
class CreateCartItemRequestSchema
{
}
