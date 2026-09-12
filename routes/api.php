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
use App\Http\Controllers\Api\Employee\EmployeeLocationEmployeeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\Hierarchy\CountryController;
use App\Http\Controllers\Api\Hierarchy\RegionController;
use App\Http\Controllers\Api\Hierarchy\ZoneController;
use App\Http\Controllers\Api\Hierarchy\DivisionController;
use App\Http\Controllers\Api\Hierarchy\DistrictController;
use App\Http\Controllers\Api\Hierarchy\SubDistrictController;
use App\Http\Controllers\Api\Hierarchy\TerritoryController;
use App\Http\Controllers\Api\Hierarchy\AreaController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin-login', [AuthController::class, 'adminLogin']);
Route::post('/verify-otp-login', [AuthController::class, 'verifyOtpLogin']);
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendForgetOtp']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify-otp', [VerificationController::class, 'verifyOtp']);

Route::middleware('auth:sanctum', 'throttle:api-auth')->group(function () {
    Route::post('/email/resend', [VerificationController::class, 'resend']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('employee-locations', EmployeeLocationController::class);
    Route::post('/locations/history', [EmployeeLocationController::class, 'history']);
    Route::apiResource('geofences', GeofenceController::class);    
    Route::apiResource('employees', EmployeeController::class);
    Route::post('employees/sync', [EmployeeController::class, 'syncEmployees']);   
    Route::apiResource('permissions', PermissionController::class);
    Route::get('permission-groups', [PermissionController::class, 'grouped']);  
    Route::apiResource('roles', RoleController::class);    
    Route::post('/users/{user}/assign-role', [RoleController::class, 'assignRole']);
    Route::get('/assign-role/users', [UserController::class, 'assignRoleUsers']);
    Route::post('roles/{role}/assign-permission', [PermissionController::class, 'assignPermission']);
    Route::get('/locations/employees', [EmployeeLocationEmployeeController::class, 'employeesLocationEmployee']);
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('attendance-rules', AttendanceRuleController::class);

    // Hierarchy //

    Route::apiResource('countries', CountryController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('zones', ZoneController::class);
    Route::apiResource('divisions', DivisionController::class);
    Route::apiResource('districts', DistrictController::class);
    Route::apiResource('sub-districts', SubDistrictController::class);
    Route::apiResource('territories', TerritoryController::class);
    Route::apiResource('areas', AreaController::class);    
});