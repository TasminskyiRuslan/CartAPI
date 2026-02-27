<?php

namespace App\Swagger\Schemas\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResponse',
    title: 'Auth Response',
    description: 'Data returned after user login or registration.',
    required: ['user', 'access_token', 'token_type'],
    properties: [
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/UserResponse',
            description: 'Authenticated user data.'
        ),
        new OA\Property(
            property: 'access_token',
            description: 'Personal access token for API authentication.',
            type: 'string',
            example: '1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            description: 'Token type to be used in the Authorization header.',
            type: 'string',
            example: 'Bearer'
        )
    ],
    type: 'object'
)]
class AuthResponseSchema
{
}
