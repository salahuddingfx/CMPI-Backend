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

Route::post('/login', [AuthController::class, 'login']);

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

Route::get('/feedbacks', [FeedbackController::class, 'index']);
Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::post('/feedbacks/{feedback}/upvote', [FeedbackController::class, 'upvote']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/dashboard/courses', [StudentController::class, 'courses']);
    Route::get('/dashboard/results', [StudentController::class, 'results']);
    Route::get('/dashboard/bills', [StudentController::class, 'bills']);
    Route::get('/dashboard/profile', [StudentController::class, 'profile']);
    Route::get('/dashboard/emails', [StudentController::class, 'emails']);

    // File uploads
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::post('/upload/multiple', [FileUploadController::class, 'uploadMultiple']);
    Route::delete('/upload', [FileUploadController::class, 'destroy']);
});