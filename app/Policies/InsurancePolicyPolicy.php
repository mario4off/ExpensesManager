<?php

namespace App\Policies;

use App\Models\InsurancePolicy;
use App\Models\User;

class InsurancePolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('insurance_policies');
    }

    public function view(User $user, InsurancePolicy $policy): bool
    {
        return $user->can('insurance_policies');
    }

    public function create(User $user): bool
    {
        return $user->can('insurance_policies');
    }

    public function update(User $user, InsurancePolicy $policy): bool
    {
        return $user->can('insurance_policies');
    }

    public function delete(User $user, InsurancePolicy $policy): bool
    {
        return $user->can('insurance_policies');
    }
}
