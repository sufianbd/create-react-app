<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\ExpenseClaim;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Policies\DepartmentPolicy;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\HR\Policies\ExpenseClaimPolicy;
use App\Modules\HR\Policies\LeaveRequestPolicy;
use App\Modules\HR\Policies\PayrollRunPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/hr.php');

        Gate::policy(Department::class,   DepartmentPolicy::class);
        Gate::policy(Employee::class,     EmployeePolicy::class);
        Gate::policy(ExpenseClaim::class, ExpenseClaimPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(PayrollRun::class,   PayrollRunPolicy::class);
    }
}
