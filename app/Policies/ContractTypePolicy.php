<?php

namespace App\Policies;

use App\Models\ContractType;
use App\Models\User;

class ContractTypePolicy
{
    /**
     * Reading contract types is open to all authenticated users —
     * needed by the employee create/edit form dropdown.
     */
    public function viewAny(User $_user): bool
    {
        return true;
    }

    public function view(User $_user, ContractType $_type): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_contract_types');
    }

    public function update(User $user, ContractType $_type): bool
    {
        return $user->hasPermissionTo('manage_contract_types');
    }

    public function delete(User $user, ContractType $_type): bool
    {
        return $user->hasPermissionTo('manage_contract_types');
    }
}
