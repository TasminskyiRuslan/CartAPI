<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResponse',
    title: 'User Response',
    description: 'Data for a user.',
    required: ['id', 'name', 'email'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier of the user.',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'Email address of the user.',
            type: 'string',
            format: 'email',
            example: 'john@example.com'
        )
    ],
    type: 'object'
)]
class UserResponseSchema
{

}
