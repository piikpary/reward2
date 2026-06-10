<?php

namespace App\Enums;

enum UserType: int
{
    case CUSTOMER = 1;
    case MERCHANT = 2;
    case DISTRIBUTOR = 3;
}