<?php

namespace App\DTOs;

readonly class UpdateEmployeeDTO
{
    public function __construct(
        public ?string $name               = null,
        public ?string $phone              = null,
        public ?int    $departmentId       = null,
        public ?string $jobTitle           = null,
        public ?int    $managerId          = null,
        public ?string $contractType       = null,
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
            departmentId:       isset($data['department_id']) ? (int) $data['department_id'] : null,
            jobTitle:           $data['job_title'] ?? null,
            managerId:          isset($data['manager_id']) ? (int) $data['manager_id'] : null,
            contractType:       $data['contract_type'] ?? null,
            baseSalary:         isset($data['base_salary']) ? (float) $data['base_salary'] : null,
            housingAllowance:   isset($data['housing_allowance']) ? (float) $data['housing_allowance'] : null,
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
            'job_title'           => $this->jobTitle,
            'manager_id'          => $this->managerId,
            'contract_type'       => $this->contractType,
            'base_salary'         => $this->baseSalary,
            'housing_allowance'   => $this->housingAllowance,
            'transport_allowance' => $this->transportAllowance,
            'hire_date'           => $this->hireDate,
            'status'              => $this->status,
        ], fn ($v) => !is_null($v));
    }
}
