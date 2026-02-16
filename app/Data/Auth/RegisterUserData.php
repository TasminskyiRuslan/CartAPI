<?php

namespace App\Data\Auth;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        #[Max(100)]
        public string $name,

        #[Required]
        #[Email]
        #[Max(255)]
        #[Unique('users', 'email')]
        #[WithCast(LowercaseCast::class)]
        public string $email,

        #[Required]
        #[StringType]
        #[Confirmed]
        #[Password(min:8)]
        public string $password,
    ) {}
}
