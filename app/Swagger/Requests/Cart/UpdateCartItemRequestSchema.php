<?php

namespace App\Swagger\Requests\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateCartItemRequest',
    title: 'Update Cart Item Request',
    description: 'Request payload for updating a product quantity in the cart.',
    required: ['quantity'],
    properties: [
        new OA\Property(
            property: 'quantity',
            description: 'New quantity of the product.',
            type: 'integer',
            maximum: 99,
            minimum: 1,
            example: 2
        ),
    ],
    type: 'object'
)]
class UpdateCartItemRequestSchema
{

}
