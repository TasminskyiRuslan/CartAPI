<?php

namespace App\Data\Auth;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

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
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s\Z')]
        public DateTimeInterface $createdAt,

        #[MapName('updated_at')]
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s\Z')]
        public DateTimeInterface $updatedAt,
    ) {}
}
