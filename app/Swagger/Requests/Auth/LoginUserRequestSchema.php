<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginUserRequest',
    title: 'Login User Request',
    description: 'Request payload for authenticating a user.',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'User email address.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'User account password.',
            type: 'string',
            format: 'password',
            example: 'password123'
        ),
    ],
    type: 'object'
)]
class LoginUserRequestSchema
{

}
