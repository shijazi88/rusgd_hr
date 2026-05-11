<?php

namespace App\Http\Requests\Period;

class UpdatePeriodRequest extends StorePeriodRequest
{
    public function rules(): array
    {
        return array_merge($this->fieldRules(false), $this->lateTierRules());
    }
}
