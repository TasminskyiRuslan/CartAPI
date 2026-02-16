<?php

namespace App\Data\Auth;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class UserData extends Data
{
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
