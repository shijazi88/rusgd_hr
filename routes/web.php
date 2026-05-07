<?php

use Illuminate\Support\Facades\Route;

// Login
Route::get('/login', fn () => view('auth.login'))->name('login');

// Authenticated views — auth guard is handled client-side (Alpine checks localStorage token)
Route::get('/',                  fn () => view('dashboard'))->name('dashboard');
Route::get('/employees',         fn () => view('employees.index'))->name('employees');
Route::get('/org-chart',         fn () => view('org-chart'))->name('org-chart');
Route::get('/leave-requests',    fn () => view('leave-requests.index'))->name('leave-requests');
Route::get('/attendance',        fn () => view('attendance.index'))->name('attendance');
Route::get('/shifts',            fn () => view('shifts.index'))->name('shifts');
Route::get('/purchase-requests', fn () => view('purchase-requests.index'))->name('purchase-requests');
Route::get('/payroll',           fn () => view('payroll.index'))->name('payroll');
Route::get('/roles',             fn () => view('roles.index'))->name('roles');
Route::get('/approval-rules',    fn () => view('approval-rules.index'))->name('approval-rules');
Route::get('/departments',       fn () => view('departments.index'))->name('departments');
Route::get('/company',           fn () => view('company.index'))->name('company');
Route::get('/approvals',         fn () => view('approvals.index'))->name('approvals');
