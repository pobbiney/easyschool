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
use App\Http\Controllers\Student\AcademicTermController;
use App\Http\Controllers\Student\SchoolClassController;
use App\Http\Controllers\Student\ClassCategoryController;
use App\Http\Controllers\Student\ClassTeacherController;
use App\Http\Controllers\Student\StudentClassAssignmentController;
use App\Http\Controllers\Settings\SchoolSettingController;
use App\Http\Controllers\Dormitory\DormitoryController;
use App\Http\Controllers\Billing\BillingItemController;
use App\Http\Controllers\Billing\CategoryBillSetupController;
use App\Http\Controllers\Billing\StudentBillController;
use App\Http\Controllers\Billing\BillPaymentController;
use App\Http\Controllers\TeacherManagement\TeacherDirectoryController;
use App\Http\Controllers\TeacherManagement\GradingSchemeController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ClassWorkspaceController;
use App\Http\Controllers\Teacher\CourseWorkspaceController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\GradebookController;

Route::get('forgot-password',[AuthenticationController::class,'getForgetPassword'])->name('forgot-password');
Route::post('forgot-password-process',[AuthenticationController::class,'forgotPass'])->name('forgot-password-process');
Route::get('verify-otp-page/{id}',[AuthenticationController::class,'getOtp'])->name('verify-otp-page');
Route::post('update-user-photo-process',[AuthenticationController::class,'updatePhoto'])->name('update-user-photo-process');
Route::post('update-user-password-process',[AuthenticationController::class,'updatePassword'])->name('update-user-password-process');
Route::post('reset-otp-process/{id}',[AuthenticationController::class,'resetOtp'])->name('reset-otp-process');

/** End of Frontend */

/* Backend*/
Route::get('/', [AuthenticationController::class, 'getAdminLoginPage'])->name('admin-login');
Route::get('login', fn () => redirect('/'))->name('login');
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

   Route::controller(AcademicTermController::class)->group(function () {
        Route::get('academic-terms', 'index')->name('academic-terms');
        Route::post('add-academic-term-process', 'store')->name('add-academic-term-process');
        Route::get('get-academic-term-id/{id}', 'show')->name('get-academic-term-id');
        Route::post('update-academic-term-process', 'update')->name('update-academic-term-process');
    });

   Route::controller(SchoolClassController::class)->group(function () {
        Route::get('school-classes', 'index')->name('school-classes');
        Route::post('add-school-class-process', 'store')->name('add-school-class-process');
        Route::get('get-school-class-id/{id}', 'show')->name('get-school-class-id');
        Route::post('update-school-class-process', 'update')->name('update-school-class-process');
    });

   Route::controller(ClassCategoryController::class)->group(function () {
        Route::get('class-categories', 'index')->name('class-categories');
        Route::post('add-class-category-process', 'store')->name('add-class-category-process');
        Route::get('get-class-category-id/{id}', 'show')->name('get-class-category-id');
        Route::post('update-class-category-process', 'update')->name('update-class-category-process');
    });

   Route::controller(ClassTeacherController::class)->group(function () {
        Route::get('class-teacher-assignment', 'index')->name('class-teacher-assignment');
        Route::get('get-class-teacher-assignment/{id}', 'show')->name('get-class-teacher-assignment');
        Route::post('assign-class-teacher-process', 'assign')->name('assign-class-teacher-process');
        Route::post('unassign-class-teacher-process', 'unassign')->name('unassign-class-teacher-process');
    });

   Route::controller(StudentClassAssignmentController::class)->group(function () {
        Route::get('student-class-assignment', 'index')->name('student-class-assignment');
        Route::get('student-class-assignment-search', 'search')->name('student-class-assignment-search');
        Route::get('get-student-class-assignment/{id}', 'show')->name('get-student-class-assignment');
        Route::get('student-class-assignment-preview', 'preview')->name('student-class-assignment-preview');
        Route::get('student-class-assignment-bulk-candidates', 'bulkCandidates')->name('student-class-assignment-bulk-candidates');
        Route::post('assign-student-class-process', 'assign')->name('assign-student-class-process');
        Route::post('assign-student-class-bulk-process', 'bulkAssign')->name('assign-student-class-bulk-process');
    });
/* End StudentController*/

/* SchoolSettingController*/
   Route::controller(SchoolSettingController::class)->group(function () {
        Route::get('school-settings', 'index')->name('school-settings');
        Route::get('academic-session', 'index')->name('academic-session');
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

/* Billing */
   Route::controller(BillingItemController::class)->group(function () {
        Route::get('billing-items', 'index')->name('billing-items');
        Route::post('add-billing-item-process', 'store')->name('add-billing-item-process');
        Route::get('get-billing-item-id/{id}', 'show')->name('get-billing-item-id');
        Route::post('update-billing-item-process', 'update')->name('update-billing-item-process');
    });

   Route::controller(CategoryBillSetupController::class)->group(function () {
        Route::get('category-bill-setup', 'index')->name('category-bill-setup');
        Route::get('category-bill-setup-load', 'load')->name('category-bill-setup-load');
        Route::post('category-bill-setup-save', 'store')->name('category-bill-setup-save');
    });

   Route::controller(StudentBillController::class)->group(function () {
        Route::get('student-bills', 'index')->name('student-bills');
        Route::get('student-bills/print', 'printLedger')->name('student-bills-print');
        Route::get('student-bills/print/{id}', 'printStatement')->name('student-bill-print');
        Route::get('print-bills', 'printBillsIndex')->name('print-bills');
        Route::get('print-class-bills/output', 'printClassStatements')->name('print-class-bills-output');
        Route::get('edit-student-bills', 'editIndex')->name('edit-student-bills');
        Route::get('edit-student-bills-search', 'search')->name('edit-student-bills-search');
        Route::get('get-student-bills/{id}', 'show')->name('get-student-bills');
        Route::get('get-student-outstanding-bills/{id}', 'outstanding')->name('get-student-outstanding-bills');
        Route::post('update-student-bill-process', 'updateBill')->name('update-student-bill-process');
    });

   Route::controller(BillPaymentController::class)->group(function () {
        Route::get('record-bill-payment/{id}', 'cashier')->name('record-bill-payment');
        Route::post('record-bill-payment-process', 'store')->name('record-bill-payment-process');
        Route::post('paystack/bill-payment/initialize', 'initializePaystack')->name('paystack-bill-payment-initialize');
        Route::post('paystack/bill-payment/verify', 'verifyPaystack')->name('paystack-bill-payment-verify');
        Route::post('paystack/webhook', 'webhook')->name('paystack-webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        Route::get('bill-payment-receipt/{id}', 'receipt')->name('bill-payment-receipt');
    });
/* End Billing */

/* Teacher Management */
   Route::controller(TeacherDirectoryController::class)->group(function () {
        Route::get('teacher-directory', 'index')->name('teacher-directory');
    });

   Route::controller(GradingSchemeController::class)->group(function () {
        Route::get('grading-scheme', 'index')->name('grading-scheme');
        Route::post('add-grading-scheme-process', 'store')->name('add-grading-scheme-process');
        Route::post('update-grading-scheme-process', 'update')->name('update-grading-scheme-process');
        Route::post('delete-grading-scheme-process', 'destroy')->name('delete-grading-scheme-process');
    });
/* End Teacher Management */

/* Teacher Portal */
   Route::controller(TeacherDashboardController::class)->group(function () {
        Route::get('teacher-dashboard', 'index')->name('teacher-dashboard');
    });

   Route::controller(ClassWorkspaceController::class)->group(function () {
        Route::get('teacher/classes/{class}', 'show')->name('teacher-class-workspace');
    });

   Route::controller(CourseWorkspaceController::class)->group(function () {
        Route::get('teacher/courses/{course}/classes/{class}', 'show')->name('teacher-course-workspace');
    });

   Route::controller(AssessmentController::class)->group(function () {
        Route::get('teacher-assessments', 'hub')->name('teacher-assessments');
        Route::get('teacher/classes/{class}/assessments', 'classIndex')->name('teacher-class-assessments');
        Route::get('teacher/courses/{course}/classes/{class}/assessments', 'courseIndex')->name('teacher-course-assessments');
        Route::post('teacher-assessments-process', 'store')->name('teacher-assessments-process');
        Route::get('teacher/assessments/{assessment}/scores', 'scores')->name('teacher-assessment-scores');
        Route::post('teacher/assessments/{assessment}/scores', 'saveScores')->name('teacher-assessment-scores-process');
    });

   Route::controller(AttendanceController::class)->group(function () {
        Route::get('teacher-attendance', 'hub')->name('teacher-attendance');
        Route::get('teacher/classes/{class}/attendance', 'index')->name('teacher-class-attendance');
        Route::post('teacher/classes/{class}/attendance', 'store')->name('teacher-class-attendance-process');
    });

   Route::controller(GradebookController::class)->group(function () {
        Route::get('teacher-gradebook', 'hub')->name('teacher-gradebook');
        Route::get('teacher/classes/{class}/gradebook', 'index')->name('teacher-class-gradebook');
        Route::get('teacher/students/{student}/report-card/print', 'printReportCard')->name('teacher-report-card-print');
        Route::get('teacher/classes/{class}/report-cards/print', 'printClassReportCards')->name('teacher-class-report-cards-print');
    });
/* End Teacher Portal */
