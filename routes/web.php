<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\CourseTeacherController;
use App\Http\Controllers\Course\CourseRegistrationController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\UserManagement\UserManagementController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\AcademicYearController;
use App\Http\Controllers\Student\SchoolClassController;
use App\Http\Controllers\Student\ClassTeacherController;
use App\Http\Controllers\Settings\SchoolSettingController;
use App\Http\Controllers\Dormitory\DormitoryController;

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
        Route::get('list-staff', 'getListstaffView')->name('list-staff');
        Route::get('edit-staff/{id}', 'geteditStaffID')->name('edit-staff');
        Route::get('view-staff-details/{id}', 'getstaffDetailsView')->name('view-staff-details');
        Route::get('staff-id/{id}', 'getStaffID')->name('staff-id');
        Route::post('delete-staff-process', 'deleteStaff')->name('delete-staff-process');
        Route::post('add-staff-document-process', 'addDocStaff')->name('add-staff-document-process');
        Route::get('profile', 'getstaffprofileView')->name('profile');
        Route::post('update-staff-process/{id}', 'updateStaff')->name('update-staff-process');
        Route::post('update-photo-process', 'updatePhoto')->name('update-photo-process');
        Route::post('update-password-process', 'updatePassword')->name('update-password-process');
        
    });
/* End StaffController*/

/* UserManagementController*/
   Route::controller(UserManagementController::class)->group(function () {
        Route::get('user-categories', 'getUserCategoriesView')->name('user-categories');
        Route::post('add-user-category-process', 'addUserCategory')->name('add-user-category-process');
        Route::get('get-user-category-id/{id}', 'getUserCategoryId')->name('get-user-category-id');
        Route::post('update-user-category-process', 'updateUserCategory')->name('update-user-category-process');
        Route::post('delete-user-category-process', 'deleteUserCategory')->name('delete-user-category-process');
    });
/* End UserManagementController*/

/* StudentController*/
   Route::controller(StudentController::class)->group(function () {
        Route::get('add-student', 'getAddStudentView')->name('add-student');
        Route::post('add-student-process', 'addStudent')->name('add-student-process');
        Route::post('save-student-draft-process', 'saveStudentDraft')->name('save-student-draft-process');
        Route::get('list-students', 'getListStudentsView')->name('list-students');
        Route::get('edit-student/{id}', 'getEditStudentView')->name('edit-student');
        Route::post('update-student-process/{id}', 'updateStudent')->name('update-student-process');
        Route::get('view-student-details/{id}', 'getStudentDetailsView')->name('view-student-details');
        Route::get('print-student-details/{id}', 'printStudentDetailsView')->name('print-student-details');
        Route::get('student-profile/{id}', 'publicStudentProfileView')->name('public-student-profile')->middleware('signed');
    });

   Route::controller(AcademicYearController::class)->group(function () {
        Route::get('academic-years', 'index')->name('academic-years');
        Route::post('add-academic-year-process', 'store')->name('add-academic-year-process');
        Route::get('get-academic-year-id/{id}', 'show')->name('get-academic-year-id');
        Route::post('update-academic-year-process', 'update')->name('update-academic-year-process');
    });

   Route::controller(SchoolClassController::class)->group(function () {
        Route::get('school-classes', 'index')->name('school-classes');
        Route::post('add-school-class-process', 'store')->name('add-school-class-process');
        Route::get('get-school-class-id/{id}', 'show')->name('get-school-class-id');
        Route::post('update-school-class-process', 'update')->name('update-school-class-process');
    });

   Route::controller(ClassTeacherController::class)->group(function () {
        Route::get('class-teacher-assignment', 'index')->name('class-teacher-assignment');
        Route::get('get-class-teacher-assignment/{id}', 'show')->name('get-class-teacher-assignment');
        Route::post('assign-class-teacher-process', 'assign')->name('assign-class-teacher-process');
        Route::post('unassign-class-teacher-process', 'unassign')->name('unassign-class-teacher-process');
    });
/* End StudentController*/

/* SchoolSettingController*/
   Route::controller(SchoolSettingController::class)->group(function () {
        Route::get('school-settings', 'index')->name('school-settings');
        Route::post('update-school-settings-process', 'update')->name('update-school-settings-process');
    });
/* End SchoolSettingController*/

/* DormitoryController*/
   Route::controller(DormitoryController::class)->group(function () {
        Route::get('dormitory-setup', 'index')->name('dormitory-setup');
        Route::post('add-house-process', 'storeHouse')->name('add-house-process');
        Route::get('get-house-id/{id}', 'showHouse')->name('get-house-id');
        Route::post('update-house-process', 'updateHouse')->name('update-house-process');
        Route::post('add-dormitory-process', 'storeDormitory')->name('add-dormitory-process');
        Route::get('get-dormitory-id/{id}', 'showDormitory')->name('get-dormitory-id');
        Route::post('update-dormitory-process', 'updateDormitory')->name('update-dormitory-process');
        Route::get('get-dormitories-by-house/{houseId}', 'dormitoriesByHouse')->name('get-dormitories-by-house');
        Route::get('get-available-beds/{dormitoryId}', 'availableBeds')->name('get-available-beds');
        Route::get('get-student-dormitory/{id}', 'getStudentAssignment')->name('get-student-dormitory');
        Route::post('assign-student-dormitory-process', 'assignStudent')->name('assign-student-dormitory-process');
        Route::post('unassign-student-dormitory-process', 'unassignStudent')->name('unassign-student-dormitory-process');
    });
/* End DormitoryController*/

/* CourseController*/
   Route::controller(CourseController::class)->group(function () {
        Route::get('add-course', 'getAddCourseView')->name('add-course');
        Route::post('add-course-process', 'addCourse')->name('add-course-process');
        Route::post('add-sub-course-process', 'storeSubCourse')->name('add-sub-course-process');
        Route::get('get-course-id/{id}', 'getCourseID')->name('get-course-id');
        Route::post('update-course-process', 'updateCourse')->name('update-course-process');
    });

   Route::controller(CourseTeacherController::class)->group(function () {
        Route::get('course-teacher-assignment', 'index')->name('course-teacher-assignment');
        Route::get('get-course-teacher-assignment/{id}', 'show')->name('get-course-teacher-assignment');
        Route::post('assign-course-teacher-process', 'assign')->name('assign-course-teacher-process');
        Route::post('unassign-course-teacher-process', 'unassign')->name('unassign-course-teacher-process');
    });

   Route::controller(CourseRegistrationController::class)->group(function () {
        Route::get('course-registration', 'index')->name('course-registration');
        Route::get('course-registration-courses', 'courses')->name('course-registration-courses');
        Route::get('course-registration-registered', 'registered')->name('course-registration-registered');
        Route::post('course-registration-register', 'register')->name('course-registration-register');
        Route::post('course-registration-unregister', 'unregister')->name('course-registration-unregister');
    });
/* End CourseController*/
