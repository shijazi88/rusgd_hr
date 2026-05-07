<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case Pending         = 'pending';
    case InitialApproval = 'initial_approval';
    case Approved        = 'approved';
    case Rejected        = 'rejected';
}
