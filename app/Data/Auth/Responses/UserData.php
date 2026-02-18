<?php

namespace App\Data\Auth\Responses;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    /**
     * Create a new instance of UserData.
     *
     * @param int $id The unique identifier of the user.
     * @param string $name The name of the user.
     * @param string $email The email address of the user.
     */
    public function __construct(
        public int $id,

        public string $name,

        public string $email,
    ) {}
}
