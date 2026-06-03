<?php

use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\EmployeeOnboardingController;
use App\Modules\HR\Http\Controllers\ExpenseClaimController;
use App\Modules\HR\Http\Controllers\LeaveRequestController;
use App\Modules\HR\Http\Controllers\OnboardingTemplateController;
use App\Modules\HR\Http\Controllers\PayrollController;
use App\Modules\HR\Http\Controllers\PayrollRunController;
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

    // Expense Claims
    Route::post('expense-claims/{expenseClaim}/submit',    [ExpenseClaimController::class, 'submit'])->name('expense-claims.submit');
    Route::post('expense-claims/{expenseClaim}/approve',   [ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
    Route::post('expense-claims/{expenseClaim}/reject',    [ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');
    Route::post('expense-claims/{expenseClaim}/reimburse', [ExpenseClaimController::class, 'reimburse'])->name('expense-claims.reimburse');
    Route::resource('expense-claims', ExpenseClaimController::class)->except(['edit', 'update']);
});
