<?php

namespace App\Domain\Enums\EnumTraits;

trait EnumToArray
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
