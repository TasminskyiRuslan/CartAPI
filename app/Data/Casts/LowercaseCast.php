<?php

namespace App\Data\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class LowercaseCast implements Cast
{
    /**
     * Cast the given value to lowercase.
     *
     * @param DataProperty $property The data property being cast.
     * @param mixed $value The value to be cast.
     * @param array $properties All properties of the data object.
     * @param CreationContext $context The context of the creation process.
     * @return string The casted value in lowercase.
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return strtolower($value);
    }
}
