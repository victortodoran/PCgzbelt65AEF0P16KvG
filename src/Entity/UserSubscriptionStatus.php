<?php

declare(strict_types=1);

namespace App\Entity;

enum UserSubscriptionStatus: int
{
    case PENDING = 1;
    case ACTIVE = 2;
    case OVERDUE = 3;
    case PAUSED = 4;
    case CANCELED = 5;
}
