<?php

namespace App\DTOs;

readonly class UpdateEmployeeDTO
{
    public function __construct(
        public ?string $name               = null,
        public ?string $phone              = null,
        public ?int    $departmentId       = null,
        public ?int    $jobTitleId         = null,
        public ?int    $managerId          = null,
        public ?int    $contractTypeId     = null,
        public ?float  $baseSalary         = null,
        public ?float  $housingAllowance   = null,
        public ?float  $transportAllowance = null,
        public ?string $hireDate           = null,
        public ?string $status             = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:               $data['name'] ?? null,
            phone:              $data['phone'] ?? null,
            departmentId:       isset($data['department_id'])    ? (int) $data['department_id']    : null,
            jobTitleId:         isset($data['job_title_id'])     ? (int) $data['job_title_id']     : null,
            managerId:          isset($data['manager_id'])       ? (int) $data['manager_id']       : null,
            contractTypeId:     isset($data['contract_type_id']) ? (int) $data['contract_type_id'] : null,
            baseSalary:         isset($data['base_salary'])         ? (float) $data['base_salary']         : null,
            housingAllowance:   isset($data['housing_allowance'])   ? (float) $data['housing_allowance']   : null,
            transportAllowance: isset($data['transport_allowance']) ? (float) $data['transport_allowance'] : null,
            hireDate:           $data['hire_date'] ?? null,
            status:             $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'                => $this->name,
            'phone'               => $this->phone,
            'department_id'       => $this->departmentId,
            'job_title_id'        => $this->jobTitleId,
            'manager_id'          => $this->managerId,
            'contract_type_id'    => $this->contractTypeId,
            'base_salary'         => $this->baseSalary,
            'housing_allowance'   => $this->housingAllowance,
            'transport_allowance' => $this->transportAllowance,
            'hire_date'           => $this->hireDate,
            'status'              => $this->status,
        ], fn ($v) => !is_null($v));
    }
}
