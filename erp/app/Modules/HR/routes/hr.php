<?php

use App\Modules\HR\Http\Controllers\DepartmentController;
use App\Modules\HR\Http\Controllers\EmployeeController;
use App\Modules\HR\Http\Controllers\LeaveRequestController;
use App\Modules\HR\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {

    // Departments (inline CRUD, no separate pages)
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Employees
    Route::resource('employees', EmployeeController::class)->names('employees');

    // Leave Requests
    Route::get('leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('leave/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
    Route::patch('leave/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');

    // Payroll
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('payroll/{payrollRun}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::patch('payroll/{payrollRun}/process', [PayrollController::class, 'process'])->name('payroll.process');
});
