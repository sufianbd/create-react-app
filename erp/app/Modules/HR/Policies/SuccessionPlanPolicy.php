<?php

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\SuccessionPlan;

class SuccessionPlanPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermissionTo('hr.view'); }
    public function view(User $user, SuccessionPlan $plan): bool { return $user->hasPermissionTo('hr.view'); }
    public function create(User $user): bool { return $user->hasPermissionTo('hr.create'); }
    public function update(User $user, SuccessionPlan $plan): bool { return $user->hasPermissionTo('hr.create'); }
    public function delete(User $user, SuccessionPlan $plan): bool { return $user->hasPermissionTo('hr.delete'); }
    public function complete(User $user, SuccessionPlan $plan): bool { return $user->hasPermissionTo('hr.create'); }
    public function deactivate(User $user, SuccessionPlan $plan): bool { return $user->hasPermissionTo('hr.create'); }
}
