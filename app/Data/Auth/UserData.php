<?php

namespace App\Data\Auth;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    /**
     * Create a new instance of UserData.
     *
     * @param int $id The unique identifier of the user.
     * @param string $name The name of the user.
     * @param string $email The email address of the user.
     * @param DateTimeInterface $createdAt The timestamp when the user was created.
     * @param DateTimeInterface $updatedAt The timestamp when the user was last updated.
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,

        #[MapName('created_at')]
        public DateTimeInterface $createdAt,

        #[MapName('updated_at')]
        public DateTimeInterface $updatedAt,
    ) {}
}
