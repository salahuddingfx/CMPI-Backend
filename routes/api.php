<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InstituteController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\BtebResultController;
use App\Http\Controllers\Api\InstituteResultController;
use App\Http\Controllers\Api\SocialLinkController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/institute', [InstituteController::class, 'index']);

Route::get('/notices', [NoticeController::class, 'index']);
Route::get('/notices/{notice}', [NoticeController::class, 'show']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

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
Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::post('/feedbacks/{feedback}/upvote', [FeedbackController::class, 'upvote']);
Route::get('/bteb-results/search', [BtebResultController::class, 'search']);
Route::get('/institute-results/search', [InstituteResultController::class, 'search']);

Route::get('/social-links', [SocialLinkController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/institute', [InstituteController::class, 'update']);
    Route::get('/institute/chart-data', [InstituteController::class, 'chartData']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/dashboard/courses', [StudentController::class, 'courses']);
    Route::get('/dashboard/results', [StudentController::class, 'results']);
    Route::get('/dashboard/bills', [StudentController::class, 'bills']);
    Route::get('/dashboard/profile', [StudentController::class, 'profile']);
    Route::get('/dashboard/emails', [StudentController::class, 'emails']);
    Route::get('/dashboard/emails/{id}/body', [StudentController::class, 'emailBody']);

    // File uploads
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::post('/upload/multiple', [FileUploadController::class, 'uploadMultiple']);
    Route::delete('/upload', [FileUploadController::class, 'destroy']);
    Route::post('/bteb-results/import', [BtebResultController::class, 'import']);
    Route::post('/bteb-results/upload-pdf', [BtebResultController::class, 'uploadPdf']);
    Route::post('/bteb-results/import-drive', [BtebResultController::class, 'importFromDrive']);
    Route::get('/bteb-results/import-status/{jobId}', [BtebResultController::class, 'importStatus']);

    // Institute results
    Route::post('/institute-results/upload-csv', [InstituteResultController::class, 'uploadCsv']);
    Route::post('/institute-results/upload-pdf', [InstituteResultController::class, 'uploadPdf']);
    Route::post('/institute-results/manual', [InstituteResultController::class, 'manual']);
    Route::delete('/institute-results/{id}', [InstituteResultController::class, 'destroy']);
    Route::get('/institute-results/stats', [InstituteResultController::class, 'stats']);

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

    Route::post('/social-links', [SocialLinkController::class, 'store']);
    Route::put('/social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::delete('/social-links/{socialLink}', [SocialLinkController::class, 'destroy']);
});