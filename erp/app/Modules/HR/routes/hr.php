<?php

use App\Modules\HR\Http\Controllers\AttendanceController;
use App\Modules\HR\Http\Controllers\DisciplinaryCaseController;
use App\Modules\HR\Http\Controllers\GrievanceController;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\EmployeeLoanController;
use App\Modules\HR\Http\Controllers\EmployeeOnboardingController;
use App\Modules\HR\Http\Controllers\EmployeeOnboardingTrackingController;
use App\Modules\HR\Http\Controllers\EmployeeTrainingRecordController;
use App\Modules\HR\Http\Controllers\ExpenseClaimController;
use App\Modules\HR\Http\Controllers\JobApplicationController;
use App\Modules\HR\Http\Controllers\JobPositionController;
use App\Modules\HR\Http\Controllers\LeaveBalanceController;
use App\Modules\HR\Http\Controllers\LeaveRequestController;
use App\Modules\HR\Http\Controllers\LeaveTypeController;
use App\Modules\HR\Http\Controllers\OnboardingChecklistController;
use App\Modules\HR\Http\Controllers\OnboardingTemplateController;
use App\Modules\HR\Http\Controllers\PayrollController;
use App\Modules\HR\Http\Controllers\PayrollRunController;
use App\Modules\HR\Http\Controllers\PerformanceReviewController;
use App\Modules\HR\Http\Controllers\TrainingCourseController;
use App\Modules\HR\Http\Controllers\TrainingEnrollmentController;
use App\Modules\HR\Http\Controllers\EmployeeCertificationController;
use App\Modules\HR\Http\Controllers\ShiftAssignmentController;
use App\Modules\HR\Http\Controllers\ShiftTemplateController;
use App\Modules\HR\Http\Controllers\WorkScheduleController;
use App\Modules\HR\Models\ExpenseClaimItem;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {

    // Departments — full CRUD
    Route::resource('departments', DepartmentController::class);

    // Employees
    Route::post('employees/{employee}/terminate', [EmployeeController::class, 'terminate'])
        ->name('employees.terminate');
    Route::resource('employees', EmployeeController::class)->names([
        'index'   => 'employees.index',
        'create'  => 'employees.create',
        'store'   => 'employees.store',
        'show'    => 'employees.show',
        'edit'    => 'employees.edit',
        'update'  => 'employees.update',
        'destroy' => 'employees.destroy',
    ]);

    // Leave Types
    Route::resource('leave-types', LeaveTypeController::class)->except(['show', 'create', 'edit']);

    // Leave Requests (new spec-compliant routes)
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');
    Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])
        ->name('leave-requests.cancel');
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update']);

    // Leave Balances
    Route::resource('leave-balances', LeaveBalanceController::class)->only(['index', 'update']);

    // Legacy leave routes (for backward compat with existing tests)
    Route::get('leave', [LeaveRequestController::class, 'legacyIndex'])->name('leave.index');
    Route::post('leave', [LeaveRequestController::class, 'legacyStore'])->name('leave.store');
    Route::patch('leave/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave.approve');
    Route::patch('leave/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
        ->name('leave.reject');

    // Payroll Runs (new spec-compliant routes)
    Route::post('payroll-runs/{payrollRun}/process', [PayrollRunController::class, 'process'])
        ->name('payroll-runs.process');
    Route::resource('payroll-runs', PayrollRunController::class)->except(['edit', 'update']);

    // Payroll — custom actions BEFORE resource
    Route::post('payroll/{payrollRun}/generate',   [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('payroll/{payrollRun}/approve',    [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('payroll/{payrollRun}/mark-paid',  [PayrollController::class, 'markPaid'])->name('payroll.mark-paid');
    Route::patch('payroll/{payrollRun}/process',   [PayrollController::class, 'process'])->name('payroll.process');
    Route::resource('payroll', PayrollController::class)->except(['edit', 'update']);

    // Onboarding Templates
    Route::resource('onboarding-templates', OnboardingTemplateController::class)->except(['edit', 'update']);

    // Employee Onboardings (nested under employees)
    Route::prefix('employees/{employee}')->name('employees.')->group(function () {
        Route::resource('onboardings', EmployeeOnboardingController::class)->except(['edit', 'update']);
        Route::post('onboardings/{onboarding}/complete', [EmployeeOnboardingController::class, 'complete'])->name('onboardings.complete');
        Route::post('onboardings/{onboarding}/tasks/{task}/complete', [EmployeeOnboardingController::class, 'completeTask'])->name('onboardings.tasks.complete');
        Route::post('onboardings/{onboarding}/tasks/{task}/uncomplete', [EmployeeOnboardingController::class, 'uncompleteTask'])->name('onboardings.tasks.uncomplete');
    });

    // Performance Reviews
    Route::post('performance-reviews/{performanceReview}/submit',        [PerformanceReviewController::class, 'submit'])->name('performance-reviews.submit');
    Route::post('performance-reviews/{performanceReview}/acknowledge',   [PerformanceReviewController::class, 'acknowledge'])->name('performance-reviews.acknowledge');
    Route::post('performance-reviews/{performanceReview}/kpis',          [PerformanceReviewController::class, 'addKpi'])->name('performance-reviews.kpis.add');
    Route::patch('performance-reviews/{performanceReview}/kpis/{kpi}',   [PerformanceReviewController::class, 'updateKpi'])->name('performance-reviews.kpis.update');
    Route::delete('performance-reviews/{performanceReview}/kpis/{kpi}',  [PerformanceReviewController::class, 'removeKpi'])->name('performance-reviews.kpis.remove');
    Route::resource('performance-reviews', PerformanceReviewController::class)->except(['edit', 'update']);

    // Expense Claims — custom actions BEFORE resource
    Route::post('expense-claims/{expenseClaim}/submit',     [ExpenseClaimController::class, 'submit'])->name('expense-claims.submit');
    Route::post('expense-claims/{expenseClaim}/approve',    [ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
    Route::post('expense-claims/{expenseClaim}/reject',     [ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');
    Route::post('expense-claims/{expenseClaim}/mark-paid',  [ExpenseClaimController::class, 'markPaid'])->name('expense-claims.mark-paid');
    Route::post('expense-claims/{expenseClaim}/items',      [ExpenseClaimController::class, 'addItem'])->name('expense-claims.items.add');
    Route::delete('expense-claims/{expenseClaim}/items/{item}', [ExpenseClaimController::class, 'removeItem'])->name('expense-claims.items.remove');
    Route::resource('expense-claims', ExpenseClaimController::class)->except(['edit', 'update']);

    // Training Courses — enroll BEFORE resource
    Route::post('training-courses/{trainingCourse}/enroll', [TrainingCourseController::class, 'enroll'])->name('training-courses.enroll');
    Route::resource('training-courses', TrainingCourseController::class)->except(['edit', 'update']);
    Route::resource('training-records', EmployeeTrainingRecordController::class)->except(['edit', 'update']);

    // Training Enrollments — complete/fail BEFORE resource
    Route::post('training-enrollments/{trainingEnrollment}/complete', [TrainingEnrollmentController::class, 'complete'])->name('training-enrollments.complete');
    Route::post('training-enrollments/{trainingEnrollment}/fail',     [TrainingEnrollmentController::class, 'fail'])->name('training-enrollments.fail');
    Route::resource('training-enrollments', TrainingEnrollmentController::class)->only(['index', 'show']);

    // Employee Certifications
    Route::resource('employee-certifications', EmployeeCertificationController::class)->only(['index', 'store', 'destroy']);

    // Job Positions
    Route::post('job-positions/{jobPosition}/publish', [JobPositionController::class, 'publish'])->name('job-positions.publish');
    Route::post('job-positions/{jobPosition}/close',   [JobPositionController::class, 'close'])->name('job-positions.close');
    Route::resource('job-positions', JobPositionController::class)->except(['edit', 'update']);

    // Job Applications
    Route::patch('job-applications/{jobApplication}/advance', [JobApplicationController::class, 'advance'])->name('job-applications.advance');
    Route::post('job-applications/{jobApplication}/advance',  [JobApplicationController::class, 'advance'])->name('job-applications.advance.post');
    Route::post('job-applications/{jobApplication}/hire',     [JobApplicationController::class, 'hire'])->name('job-applications.hire');
    Route::post('job-applications/{jobApplication}/reject',   [JobApplicationController::class, 'reject'])->name('job-applications.reject');
    Route::resource('job-applications', JobApplicationController::class)->except(['edit', 'update']);

    // Attendance
    Route::resource('attendance', AttendanceController::class)->except(['edit']);

    // Work Schedules
    Route::resource('work-schedules', WorkScheduleController::class)->except(['edit', 'update']);

    // Shift Templates
    Route::resource('shift-templates', ShiftTemplateController::class)->except(['edit', 'update']);

    // Shift Assignments — markStatus BEFORE resource
    Route::patch('shift-assignments/{shiftAssignment}/status', [ShiftAssignmentController::class, 'markStatus'])->name('shift-assignments.status');
    Route::resource('shift-assignments', ShiftAssignmentController::class)->except(['edit', 'update', 'show']);

    // Onboarding Checklists
    Route::resource('onboarding-checklists', OnboardingChecklistController::class)->except(['edit', 'update']);

    // Employee Onboardings (checklist-based)
    Route::post('employee-onboardings/{employeeOnboarding}/tasks/{progress}/complete', [EmployeeOnboardingTrackingController::class, 'completeTask'])->name('employee-onboardings.tasks.complete');
    Route::post('employee-onboardings/{employeeOnboarding}/tasks/{progress}/skip',     [EmployeeOnboardingTrackingController::class, 'skipTask'])->name('employee-onboardings.tasks.skip');
    Route::resource('employee-onboardings', EmployeeOnboardingTrackingController::class)->except(['edit', 'update']);

    // Employee Loans
    Route::post('employee-loans/{employeeLoan}/approve',    [EmployeeLoanController::class, 'approve'])->name('employee-loans.approve');
    Route::post('employee-loans/{employeeLoan}/cancel',     [EmployeeLoanController::class, 'cancel'])->name('employee-loans.cancel');
    Route::post('employee-loans/{employeeLoan}/repayments', [EmployeeLoanController::class, 'addRepayment'])->name('employee-loans.repayments.add');
    Route::resource('employee-loans', EmployeeLoanController::class)->except(['edit', 'update']);
    // Disciplinary Cases
    Route::post('disciplinary-cases/{disciplinaryCase}/schedule-hearing', [DisciplinaryCaseController::class, 'scheduleHearing'])->name('disciplinary-cases.schedule-hearing');
    Route::post('disciplinary-cases/{disciplinaryCase}/resolve',          [DisciplinaryCaseController::class, 'resolve'])->name('disciplinary-cases.resolve');
    Route::post('disciplinary-cases/{disciplinaryCase}/close',            [DisciplinaryCaseController::class, 'close'])->name('disciplinary-cases.close');
    Route::resource('disciplinary-cases', DisciplinaryCaseController::class)->except(['edit', 'update']);

    // Grievances
    Route::patch('grievances/{grievance}/assign',  [GrievanceController::class, 'assign'])->name('grievances.assign');
    Route::post('grievances/{grievance}/resolve',  [GrievanceController::class, 'resolve'])->name('grievances.resolve');
    Route::post('grievances/{grievance}/close',    [GrievanceController::class, 'close'])->name('grievances.close');
    Route::resource('grievances', GrievanceController::class)->except(['edit', 'update']);
});


// Timesheet Management — custom actions BEFORE resource
use App\Modules\HR\Http\Controllers\TimesheetController;
Route::middleware(['web', 'auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {
    Route::post('timesheets/{timesheet}/submit',  [TimesheetController::class, 'submit'])->name('timesheets.submit');
    Route::post('timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
    Route::post('timesheets/{timesheet}/reject',  [TimesheetController::class, 'reject'])->name('timesheets.reject');
    Route::post('timesheets/{timesheet}/entries', [TimesheetController::class, 'addEntry'])->name('timesheets.entries.store');
    Route::resource('timesheets', TimesheetController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
});


// Employee Benefits Administration — custom actions BEFORE resource
use App\Modules\HR\Http\Controllers\BenefitPlanController;
use App\Modules\HR\Http\Controllers\EmployeeBenefitController;
Route::middleware(['web', 'auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {
    Route::resource('benefit-plans', BenefitPlanController::class)->only(['index', 'store', 'show', 'destroy']);

    Route::post('employee-benefits/{employeeBenefit}/waive', [EmployeeBenefitController::class, 'waive'])->name('employee-benefits.waive');
    Route::post('employee-benefits/{employeeBenefit}/end',   [EmployeeBenefitController::class, 'end'])->name('employee-benefits.end');
    Route::resource('employee-benefits', EmployeeBenefitController::class)->only(['index', 'store', 'destroy']);
});
