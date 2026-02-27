<?php

namespace App\Swagger\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterUserRequest',
    title: 'Register User Request',
    description: 'Request payload for registering a new user.',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user.',
            type: 'string',
            maxLength: 100,
            example: 'John Doe'
        ),
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
            minLength: 8,
            example: 'password123'
        ),
        new OA\Property(
            property: 'password_confirmation',
            description: 'Password confirmation (must match password).',
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
