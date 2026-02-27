<?php

namespace App\Swagger\Schemas\Cart;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductResponse',
    title: 'Product Response',
    description: 'Data for a product.',
    required: ['id', 'name', 'price'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The product ID.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'The product name.',
            type: 'string',
            example: 'iPhone 15 Pro'
        ),
        new OA\Property(
            property: 'description',
            description: 'The product description.',
            type: 'string',
            example: 'New iPhone with powerful processor and Pro camera',
            nullable: true
        ),
        new OA\Property(
            property: 'price',
            description: 'The product price.',
            type: 'string',
            format: 'float',
            example: 999.99
        ),
        new OA\Property(
            property: 'image_path',
            description: 'The image of the product.',
            type: 'string',
            format: 'path',
            example: 'products/product1.png',
            nullable: true
        )
    ],
    type: 'object'
)]
class ProductResponseSchema
{

}
