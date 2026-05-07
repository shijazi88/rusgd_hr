<?php

namespace App\Repositories\Eloquent;

use App\Models\ApprovalRule;
use App\Repositories\Contracts\IApprovalRuleRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentApprovalRuleRepository implements IApprovalRuleRepository
{
    public function findById(int $id): ?ApprovalRule
    {
        return ApprovalRule::find($id);
    }

    public function all(): Collection
    {
        return ApprovalRule::orderBy('type')->orderBy('min_value')->get();
    }

    public function create(array $data): ApprovalRule
    {
        return ApprovalRule::create($data);
    }

    public function update(ApprovalRule $rule, array $data): ApprovalRule
    {
        $rule->update($data);
        return $rule->fresh();
    }

    public function delete(ApprovalRule $rule): void
    {
        $rule->delete();
    }
}
