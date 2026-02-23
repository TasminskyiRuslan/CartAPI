<?php

namespace App\Swagger\Requests\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateItemRequest',
    title: 'Create Item Request Schema',
    description: 'Schema for cart item creation via API request.',
    required: ['product_id'],
    properties: [
        new OA\Property(
            property: 'product_id',
            description: 'The product ID.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'quantity',
            description: 'The product quantity.',
            type: 'integer',
            minimum: 1,
            example: 2
        ),
    ],
    type: 'object'
)]
class CreateCartRequestSchema
{
}
