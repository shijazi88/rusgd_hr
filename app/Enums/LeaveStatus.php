<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Pending        = 'pending';
    case InitialApproval = 'initial_approval';
    case Approved       = 'approved';
    case Rejected       = 'rejected';
}
