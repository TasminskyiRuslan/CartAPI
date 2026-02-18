<?php

namespace App\Data\Auth\Requests;

use App\Data\Casts\LowercaseCast;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class LoginUserRequestData extends Data
{
    /**
     * Create a new instance of LoginUserRequestData.
     *
     * @param string $email The email address of the user to log in.
     * @param string $password The password for the user to log in.
     */
    public function __construct(
        #[Required]
        #[Email]
        #[Max(255)]
        #[WithCast(LowercaseCast::class)]
        public string $email,

        #[Required]
        #[StringType]
        public string $password,
    ) {}
}
