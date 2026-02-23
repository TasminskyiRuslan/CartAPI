<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginUserRequest',
    title: 'Login User Request Schema',
    description: 'Schema for user login via API request.',
    required: ['email', 'password'],
    properties: [
        new OA\Property(
            property: 'email',
            description: 'Email of the user.',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'john@example.com'
        ),
        new OA\Property(
            property: 'password',
            description: 'Password of the user.',
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
