<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Auth',
    title: 'Auth Schema',
    description: 'Authentication data returned by the API',
    required: ['user', 'access_token', 'token_type'],
    properties: [
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/User',
            description: 'Authenticated user data'
        ),
        new OA\Property(
            property: 'access_token',
            description: 'Access token',
            type: 'string',
            example: '1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            description: 'Type of the token for Authorization header',
            type: 'string',
            example: 'Bearer'
        ),
    ],
    type: 'object'
)]
class AuthSchema
{
}
