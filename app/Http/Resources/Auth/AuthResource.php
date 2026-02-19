<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $user User associated with the authentication resource.
 * @property-read string $token Access token for the authenticated user.
 * @property-read string|null $tokenType Optional token type, defaults to 'Bearer' if not provided.
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->user),
            'access_token' => $this->token,
            'token_type' => $this->tokenType ?? 'Bearer',
        ];
    }
}
