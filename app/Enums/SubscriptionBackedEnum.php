<?php

namespace App\Enums;

use App\Traits\IterableEnum;

enum SubscriptionBackedEnum: string
{
    use IterableEnum;
    case Free = 'free';
    case Basic = 'basic';
    case Pro = 'pro';
    case Enterprise = 'enterprise';
}
