<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DiplomaBlankController;
use App\Http\Controllers\DiplomaManagementController;
use App\Http\Controllers\EmbryoManagementController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->middleware('auth');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(RedirectIfAuthenticated::class)->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get(
    '/diploma-management',
    [DiplomaManagementController::class, 'index']
)->middleware('auth')->name('diploma-management');

Route::get(
    '/diploma-blank-management',
    [DiplomaBlankController::class, 'index']
)->middleware('auth')->name('diploma-blank-management');

Route::get(
    '/diploma-blank-management/import',
    [DiplomaBlankController::class, 'showImportForm']
)->middleware('auth')->name('diploma-blank.import');

Route::post(
    '/diploma-blank-management/import',
    [DiplomaBlankController::class, 'storeImport']
)->middleware('auth')->name('diploma-blank.import.store');

Route::post(
    '/diploma-blank-management/validate-range',
    [DiplomaBlankController::class, 'validateRange']
)->middleware('auth')->name('diploma-blank.validate-range');

Route::get(
    '/diploma-blanks/import',
    [DiplomaBlankController::class, 'import']
)->middleware('auth')->name('diploma-blanks.import');

Route::post(
    '/diploma-blanks/import',
    [DiplomaBlankController::class, 'processImport']
)->middleware('auth')->name('diploma-blanks.process-import');

Route::post(
    '/diploma-blanks/check-duplicates',
    [DiplomaBlankController::class, 'checkDuplicates']
)->middleware('auth')->name('diploma-blanks.check-duplicates');

Route::get('/certificate-management', function () {
    return view('certificate-management');
})->middleware('auth')->name('certificate-management');

Route::get('/settings', function () {
    return view('settings');
})->middleware('auth')->name('settings');

Route::get('/about', function () {
    return view('about');
})->middleware('auth')->name('about');

Route::get('/error', function () {
    return view('error');
})->name('error');

Route::get('/test-error', function () {
    abort(404);
});

Route::get(
    "student/create",
    [DiplomaManagementController::class, 'create']
)->middleware('auth')->name('student.create');

Route::post(
    "student/create",
    [DiplomaManagementController::class, 'save']
)->middleware('auth')->name('student.save');

Route::get(
    "student/{student:student_id}",
    [DiplomaManagementController::class, 'student']
)->middleware('auth')->name('student');

Route::post(
    "student/update/{student:student_id}",
    [DiplomaManagementController::class, 'update']
)->middleware('auth')->name('student.update');

// Logout route
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
