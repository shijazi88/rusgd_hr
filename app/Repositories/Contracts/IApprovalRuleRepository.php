<?php

namespace App\Repositories\Contracts;

use App\Models\ApprovalRule;
use Illuminate\Database\Eloquent\Collection;

interface IApprovalRuleRepository
{
    public function findById(int $id): ?ApprovalRule;

    public function all(): Collection;

    public function create(array $data): ApprovalRule;

    public function update(ApprovalRule $rule, array $data): ApprovalRule;

    public function delete(ApprovalRule $rule): void;
}
