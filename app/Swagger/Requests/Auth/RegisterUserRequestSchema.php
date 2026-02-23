<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterUserRequest',
    title: 'Register User Request Schema',
    description: 'Schema for user registration via API request.',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Name of the user.',
            type: 'string',
            maxLength: 100,
            example: 'John Doe'
        ),
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
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password Confirmation of the user.',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'password123'
        ),
    ],
    type: 'object'
)]
class RegisterUserRequestSchema
{
}
