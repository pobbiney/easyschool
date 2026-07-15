<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Asset\AssetController;
 
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\UserManagement\UserManagementController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Stock\StockController;

 

Route::get('forgot-password',[AuthenticationController::class,'getForgetPassword'])->name('forgot-password');
Route::post('forgot-password-process',[AuthenticationController::class,'forgotPass'])->name('forgot-password-process');
Route::get('verify-otp-page/{id}',[AuthenticationController::class,'getOtp'])->name('verify-otp-page');
Route::post('update-user-photo-process',[AuthenticationController::class,'updatePhoto'])->name('update-user-photo-process');
Route::post('update-user-password-process',[AuthenticationController::class,'updatePassword'])->name('update-user-password-process');
Route::post('reset-otp-process/{id}',[AuthenticationController::class,'resetOtp'])->name('reset-otp-process');

/** End of Frontend */

/* Backend*/
Route::get('/',[AuthenticationController::class,'getAdminLoginPage'])->name('admin-login');
Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
 
Route::get('user-profile',[AuthenticationController::class,'getUserProfile'])->name('user-profile');
Route::post('authentication-process',[AuthenticationController::class,'authenticationProcess'])->name('authentication-process');
Route::post('logout-authentication-process',[DashboardController::class,'logoutAuthenticationProcess'])->name('logout-authentication-process');
/* Authentication */ 

/* StaffController*/
   Route::controller(StaffController::class)->group(function () {
        Route::get('add-staff', 'getstaffView')->name('add-staff');
        Route::post('add-staff-process', 'addStaff')->name('add-staff-process');
        
    });
/* End StaffController*/
