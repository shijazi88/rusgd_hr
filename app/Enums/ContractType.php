<?php

namespace App\Enums;

enum ContractType: string
{
    case Permanent  = 'permanent';
    case Temporary  = 'temporary';
    case Consultant = 'consultant';
    case Trainee    = 'trainee';
}
