<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User Schema',
    description: 'User data returned by the API.',
    required: ['id', 'name', 'email'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The user ID.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'The user name.',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'The user email.',
            type: 'string',
            format: 'email',
            example: 'john@example.com'
        )
    ],
    type: 'object'
)]
class UserSchema
{

}
