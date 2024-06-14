<?php

namespace App\Domain\Enums;

use App\Domain\Enums\EnumTraits\EnumToArray;

enum Frequency: string
{
    use EnumToArray;

    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
}
