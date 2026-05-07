<?php

namespace App\DTOs;

readonly class CreateEmployeeDTO
{
    public function __construct(
        public string  $name,
        public string  $email,
        public int     $departmentId,
        public string  $jobTitle,
        public string  $contractType,
        public float   $baseSalary,
        public string  $hireDate,
        public ?string $phone           = null,
        public ?int    $managerId       = null,
        public float   $housingAllowance   = 0,
        public float   $transportAllowance = 0,
        public string  $status          = 'active',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:               $data['name'],
            email:              $data['email'],
            departmentId:       $data['department_id'],
            jobTitle:           $data['job_title'],
            contractType:       $data['contract_type'],
            baseSalary:         (float) $data['base_salary'],
            hireDate:           $data['hire_date'],
            phone:              $data['phone'] ?? null,
            managerId:          isset($data['manager_id']) ? (int) $data['manager_id'] : null,
            housingAllowance:   (float) ($data['housing_allowance'] ?? 0),
            transportAllowance: (float) ($data['transport_allowance'] ?? 0),
            status:             $data['status'] ?? 'active',
        );
    }

    public function toArray(): array
    {
        return [
            'name'               => $this->name,
            'email'              => $this->email,
            'phone'              => $this->phone,
            'department_id'      => $this->departmentId,
            'job_title'          => $this->jobTitle,
            'manager_id'         => $this->managerId,
            'contract_type'      => $this->contractType,
            'base_salary'        => $this->baseSalary,
            'housing_allowance'  => $this->housingAllowance,
            'transport_allowance'=> $this->transportAllowance,
            'hire_date'          => $this->hireDate,
            'status'             => $this->status,
        ];
    }
}
