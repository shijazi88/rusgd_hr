<?php

namespace App\Services;

use App\Models\ApprovalRule;
use App\Repositories\Contracts\IApprovalRuleRepository;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRuleService
{
    public function __construct(
        private readonly IApprovalRuleRepository $ruleRepo,
        private readonly ApprovalRuleEngine      $ruleEngine,
    ) {}

    public function getAll(): Collection
    {
        return $this->ruleRepo->all();
    }

    public function getById(int $id): ApprovalRule
    {
        $rule = $this->ruleRepo->findById($id);

        if (!$rule) {
            abort(404, 'قاعدة الموافقة غير موجودة.');
        }

        return $rule;
    }

    public function create(array $data): ApprovalRule
    {
        $rule = $this->ruleRepo->create($data);
        $this->ruleEngine->clearCache();
        return $rule;
    }

    public function update(ApprovalRule $rule, array $data): ApprovalRule
    {
        $updated = $this->ruleRepo->update($rule, $data);
        $this->ruleEngine->clearCache();
        return $updated;
    }

    public function delete(ApprovalRule $rule): void
    {
        $this->ruleRepo->delete($rule);
        $this->ruleEngine->clearCache();
    }
}
