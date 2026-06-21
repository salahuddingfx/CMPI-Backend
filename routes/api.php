<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\InstituteController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\BtebResultController;
use App\Http\Controllers\Api\InstituteResultController;
use App\Http\Controllers\Api\ClassRoutineController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\HeroSlideController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SystemStatusController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

Route::get('/institute', [InstituteController::class, 'index']);

Route::get('/notices', [NoticeController::class, 'index']);
Route::get('/notices/{notice}', [NoticeController::class, 'show']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::post('/events/{event}/register', [EventController::class, 'register']);

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'bySlug']);

Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{slug}', [DepartmentController::class, 'bySlug']);

Route::get('/faculty', [FacultyController::class, 'index']);
Route::get('/faculty/{faculty}', [FacultyController::class, 'show']);

Route::get('/search', SearchController::class);

Route::post('/admissions', [AdmissionController::class, 'store']);
Route::post('/admissions/track', [AdmissionController::class, 'track']);
Route::get('/admissions/download-form', [AdmissionController::class, 'downloadForm']);

Route::get('/feedbacks', [FeedbackController::class, 'index']);
Route::post('/feedbacks', [FeedbackController::class, 'store'])->middleware('throttle:10,1');
Route::post('/feedbacks/{feedback}/upvote', [FeedbackController::class, 'upvote'])->middleware('throttle:20,1');
Route::get('/bteb-results/search', [BtebResultController::class, 'search']);
Route::get('/institute-results/search', [InstituteResultController::class, 'search']);

Route::get('/social-links', [SocialLinkController::class, 'index']);

Route::get('/hero-slides', [HeroSlideController::class, 'index']);

Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects/lookup', [SubjectController::class, 'lookup']);
Route::get('/subjects/detect-department', [SubjectController::class, 'detectDepartment']);
Route::get('/subjects/dictionary', [SubjectController::class, 'dictionary']);

Route::get('/class-routines', [ClassRoutineController::class, 'index']);
Route::get('/class-routines/{routine}/download', [ClassRoutineController::class, 'download']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Student dashboard routes
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/dashboard/courses', [StudentController::class, 'courses']);
    Route::get('/dashboard/results', [StudentController::class, 'results']);
    Route::get('/dashboard/bills', [StudentController::class, 'bills']);
    Route::get('/dashboard/profile', [StudentController::class, 'profile']);
    Route::put('/dashboard/profile', [StudentController::class, 'updateProfile']);
    Route::get('/dashboard/emails', [StudentController::class, 'emails']);
    Route::get('/dashboard/emails/{id}/body', [StudentController::class, 'emailBody']);

    // Bills (admin)
    Route::middleware('admin')->group(function () {
        Route::put('/institute', [InstituteController::class, 'update']);
        Route::get('/institute/chart-data', [InstituteController::class, 'chartData']);
        Route::get('/bills', [BillController::class, 'index']);
        Route::post('/bills', [BillController::class, 'store']);
        Route::put('/bills/{bill}', [BillController::class, 'update']);
        Route::delete('/bills/{bill}', [BillController::class, 'destroy']);
        Route::post('/bills/{bill}/mark-paid', [BillController::class, 'markPaid']);
        Route::get('/bills/stats', [BillController::class, 'stats']);
        Route::post('/bills/bulk', [BillController::class, 'bulkCreate']);

        // Reports
        Route::get('/reports/department-result', [ReportController::class, 'departmentResultData']);
        Route::get('/reports/department-result/download', [ReportController::class, 'departmentResult']);
        Route::get('/reports/student-transcript/{roll}', [ReportController::class, 'studentTranscriptData']);
        Route::get('/reports/student-transcript/{roll}/download', [ReportController::class, 'studentTranscript']);

        // File uploads
        Route::post('/upload', [FileUploadController::class, 'upload']);
        Route::post('/upload/multiple', [FileUploadController::class, 'uploadMultiple']);
        Route::delete('/upload', [FileUploadController::class, 'destroy']);
        Route::post('/bteb-results/import', [BtebResultController::class, 'import']);
        Route::post('/bteb-results/upload-pdf', [BtebResultController::class, 'uploadPdf']);
        Route::post('/bteb-results/import-drive', [BtebResultController::class, 'importFromDrive']);
        Route::get('/bteb-results/import-status/{jobId}', [BtebResultController::class, 'importStatus']);
        Route::get('/bteb-results/stats', [BtebResultController::class, 'stats']);

        // Institute results
        Route::post('/institute-results/upload-csv', [InstituteResultController::class, 'uploadCsv']);
        Route::post('/institute-results/upload-pdf', [InstituteResultController::class, 'uploadPdf']);
        Route::post('/institute-results/manual', [InstituteResultController::class, 'manual']);
        Route::put('/institute-results/{id}', [InstituteResultController::class, 'update']);
        Route::delete('/institute-results/{id}', [InstituteResultController::class, 'destroy']);
        Route::get('/institute-results/stats', [InstituteResultController::class, 'stats']);

        // Class routines
        Route::post('/class-routines/upload', [ClassRoutineController::class, 'upload']);
        Route::put('/class-routines/{routine}', [ClassRoutineController::class, 'update']);
        Route::delete('/class-routines/{routine}', [ClassRoutineController::class, 'destroy']);

        // Subjects
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

        // Admin CRUD operations
        Route::post('/notices', [NoticeController::class, 'store']);
        Route::put('/notices/{notice}', [NoticeController::class, 'update']);
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy']);

        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);

        Route::post('/blogs', [BlogController::class, 'store']);
        Route::put('/blogs/{blog}', [BlogController::class, 'update']);
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

        Route::post('/faculty', [FacultyController::class, 'store']);
        Route::put('/faculty/{faculty}', [FacultyController::class, 'update']);
        Route::delete('/faculty/{faculty}', [FacultyController::class, 'destroy']);

        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        Route::get('/admissions', [AdmissionController::class, 'index']);
        Route::put('/admissions/{admission}/status', [AdmissionController::class, 'updateStatus']);

        Route::get('/users', [StudentController::class, 'allUsers']);
        Route::post('/users', [StudentController::class, 'storeUser']);
        Route::put('/users/{user}', [StudentController::class, 'updateUser']);
        Route::delete('/users/{user}', [StudentController::class, 'destroyUser']);
        Route::post('/users/bulk-import', [StudentController::class, 'bulkImport']);

        Route::post('/social-links', [SocialLinkController::class, 'store']);
        Route::put('/social-links/{socialLink}', [SocialLinkController::class, 'update']);
        Route::delete('/social-links/{socialLink}', [SocialLinkController::class, 'destroy']);

        // Hero slides
        Route::get('/hero-slides/all', [HeroSlideController::class, 'index']);
        Route::post('/hero-slides', [HeroSlideController::class, 'store']);
        Route::put('/hero-slides/{heroSlide}', [HeroSlideController::class, 'update']);
        Route::delete('/hero-slides/{heroSlide}', [HeroSlideController::class, 'destroy']);
 
        // System status
        Route::get('/system/status', [SystemStatusController::class, 'getStatus']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead']);
    });
});
