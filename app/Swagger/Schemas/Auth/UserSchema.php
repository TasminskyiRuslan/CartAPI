<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User Schema',
    description: 'User data returned by the API',
    required: ['id', 'name', 'email', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The user ID',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'name',
            description: 'The user name',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'The user email',
            type: 'string',
            format: 'email',
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'created_at',
            description: 'The user registered at',
            type: 'string',
            format: 'date-time',
            example: '2026-01-01T12:00:00Z'
        ),
        new OA\Property(
            property: 'updated_at',
            description: 'The user last updated at',
            type: 'string',
            format: 'date-time',
            example: '2026-01-10T12:00:00Z'
        ),
    ],
    type: 'object'
)]
class UserSchema
{

}
