<?php

namespace App\Exceptions;

use Exception;

class PayrollAlreadyProcessedException extends Exception
{
    public function __construct(string $message = 'Payroll has already been processed for this period.')
    {
        parent::__construct($message);
    }
}
