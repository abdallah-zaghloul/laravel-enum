<?php

namespace App\Enums;

use App\Traits\IterableEnum;

enum WorkUnitEnum
{
    use IterableEnum;
    case Onsite;
    case Remote;
    case Hybrid;
}
