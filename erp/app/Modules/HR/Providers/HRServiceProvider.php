<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Policies\EmployeePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/hr.php');

        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
