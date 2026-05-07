<?php

namespace App\Enums;

enum ApprovalRuleType: string
{
    case Financial = 'financial';
    case Leave     = 'leave';
}
