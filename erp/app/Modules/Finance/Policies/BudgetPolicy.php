<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Budget;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->hasPermissionTo('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('finance.create');
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->hasPermissionTo('finance.create');
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->hasPermissionTo('finance.delete') && $budget->status === 'draft';
    }
}
