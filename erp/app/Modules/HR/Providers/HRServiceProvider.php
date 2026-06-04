<?php

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\AttendanceRecord;
use App\Modules\HR\Models\Department;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\EmployeeOnboarding;
use App\Modules\HR\Models\EmployeeTrainingRecord;
use App\Modules\HR\Models\ExpenseClaim;
use App\Modules\HR\Models\JobApplication;
use App\Modules\HR\Models\JobPosition;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\OnboardingTemplate;
use App\Modules\HR\Models\PayrollRun;
use App\Modules\HR\Models\PerformanceReview;
use App\Modules\HR\Models\TrainingCourse;
use App\Modules\HR\Models\WorkSchedule;
use App\Modules\HR\Policies\AttendancePolicy;
use App\Modules\HR\Policies\DepartmentPolicy;
use App\Modules\HR\Policies\EmployeeOnboardingPolicy;
use App\Modules\HR\Policies\EmployeePolicy;
use App\Modules\HR\Policies\ExpenseClaimPolicy;
use App\Modules\HR\Policies\LeaveRequestPolicy;
use App\Modules\HR\Policies\OnboardingTemplatePolicy;
use App\Modules\HR\Policies\PayrollRunPolicy;
use App\Modules\HR\Policies\PerformanceReviewPolicy;
use App\Modules\HR\Policies\RecruitmentPolicy;
use App\Modules\HR\Policies\TrainingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/hr.php');

        Gate::policy(AttendanceRecord::class,       AttendancePolicy::class);
        Gate::policy(WorkSchedule::class,            AttendancePolicy::class);
        Gate::policy(Department::class,              DepartmentPolicy::class);
        Gate::policy(Employee::class,                EmployeePolicy::class);
        Gate::policy(EmployeeOnboarding::class,      EmployeeOnboardingPolicy::class);
        Gate::policy(ExpenseClaim::class,            ExpenseClaimPolicy::class);
        Gate::policy(JobApplication::class,          RecruitmentPolicy::class);
        Gate::policy(JobPosition::class,             RecruitmentPolicy::class);
        Gate::policy(LeaveRequest::class,            LeaveRequestPolicy::class);
        Gate::policy(OnboardingTemplate::class,      OnboardingTemplatePolicy::class);
        Gate::policy(PayrollRun::class,              PayrollRunPolicy::class);
        Gate::policy(PerformanceReview::class,       PerformanceReviewPolicy::class);
        Gate::policy(TrainingCourse::class,          TrainingPolicy::class);
        Gate::policy(EmployeeTrainingRecord::class,  TrainingPolicy::class);
    }
}
