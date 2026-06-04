<?php

use App\Modules\HR\Http\Controllers\AttendanceController;
use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\EmployeeLoanController;
use App\Modules\HR\Http\Controllers\EmployeeOnboardingController;
use App\Modules\HR\Http\Controllers\EmployeeTrainingRecordController;
use App\Modules\HR\Http\Controllers\ExpenseClaimController;
use App\Modules\HR\Http\Controllers\JobApplicationController;
use App\Modules\HR\Http\Controllers\JobPositionController;
use App\Modules\HR\Http\Controllers\LeaveRequestController;
use App\Modules\HR\Http\Controllers\OnboardingTemplateController;
use App\Modules\HR\Http\Controllers\PayrollController;
use App\Modules\HR\Http\Controllers\PayrollRunController;
use App\Modules\HR\Http\Controllers\PerformanceReviewController;
use App\Modules\HR\Http\Controllers\TrainingCourseController;
use App\Modules\HR\Http\Controllers\ShiftAssignmentController;
use App\Modules\HR\Http\Controllers\ShiftTemplateController;
use App\Modules\HR\Http\Controllers\WorkScheduleController;
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

    // Leave Requests (new spec-compliant routes)
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update']);

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

    // Legacy payroll routes (for backward compat with existing tests)
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('payroll/{payrollRun}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::patch('payroll/{payrollRun}/process', [PayrollController::class, 'process'])->name('payroll.process');

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
    Route::resource('performance-reviews', PerformanceReviewController::class)->except(['edit', 'update']);
    Route::post('performance-reviews/{performanceReview}/start', [PerformanceReviewController::class, 'startReview'])->name('performance-reviews.start');
    Route::post('performance-reviews/{performanceReview}/complete', [PerformanceReviewController::class, 'complete'])->name('performance-reviews.complete');
    Route::patch('performance-reviews/{performanceReview}/goals/{goal}', [PerformanceReviewController::class, 'updateGoal'])->name('performance-reviews.goals.update');

    // Expense Claims
    Route::post('expense-claims/{expenseClaim}/submit',    [ExpenseClaimController::class, 'submit'])->name('expense-claims.submit');
    Route::post('expense-claims/{expenseClaim}/approve',   [ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
    Route::post('expense-claims/{expenseClaim}/reject',    [ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');
    Route::post('expense-claims/{expenseClaim}/reimburse', [ExpenseClaimController::class, 'reimburse'])->name('expense-claims.reimburse');
    Route::resource('expense-claims', ExpenseClaimController::class)->except(['edit', 'update']);

    // Training
    Route::resource('training-courses', TrainingCourseController::class)->except(['edit', 'update']);
    Route::resource('training-records', EmployeeTrainingRecordController::class)->except(['edit', 'update']);

    // Job Positions
    Route::post('job-positions/{jobPosition}/publish', [JobPositionController::class, 'publish'])->name('job-positions.publish');
    Route::post('job-positions/{jobPosition}/close',   [JobPositionController::class, 'close'])->name('job-positions.close');
    Route::resource('job-positions', JobPositionController::class)->except(['edit', 'update']);

    // Job Applications
    Route::patch('job-applications/{jobApplication}/advance', [JobApplicationController::class, 'advance'])->name('job-applications.advance');
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

    // Employee Loans
    Route::post('employee-loans/{employeeLoan}/approve',    [EmployeeLoanController::class, 'approve'])->name('employee-loans.approve');
    Route::post('employee-loans/{employeeLoan}/cancel',     [EmployeeLoanController::class, 'cancel'])->name('employee-loans.cancel');
    Route::post('employee-loans/{employeeLoan}/repayments', [EmployeeLoanController::class, 'addRepayment'])->name('employee-loans.repayments.add');
    Route::resource('employee-loans', EmployeeLoanController::class)->except(['edit', 'update']);
});
