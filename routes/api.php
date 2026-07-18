<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Api\Company\CompanyController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\Geofence\GeofenceController;
use App\Http\Controllers\Attendance\AttendanceRuleController;
use App\Http\Controllers\Api\Employee\EmployeeLocationController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);
Route::post('/verify-otp-login', [AuthController::class, 'verifyOtpLogin']);
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendForgetOtp']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify-otp', [VerificationController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/email/resend', [VerificationController::class, 'resend']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('employee-locations', EmployeeLocationController::class);
    Route::apiResource('geofences', GeofenceController::class);    
    Route::apiResource('employees', EmployeeController::class);
    Route::post('employees/sync', [EmployeeController::class, 'syncEmployees']);   
    
});

 //Route::apiResource('companies', CompanyController::class);
 //Route::apiResource('attendance-rules', AttendanceRuleController::class);
