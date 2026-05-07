<?php

namespace App\Exceptions;

use Exception;

class ApprovalNotAuthorizedException extends Exception
{
    public function __construct(string $message = 'You are not authorized to approve this request.')
    {
        parent::__construct($message);
    }
}
