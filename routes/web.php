<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DiplomaBlankController;
use App\Http\Controllers\DiplomaBlankImportController;
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
    [DiplomaBlankImportController::class, 'index']
)->middleware('auth')->name('diploma-blank-management');

Route::get(
    '/diploma-blanks-list/{importId}',
    [DiplomaBlankController::class, 'indexByImport']
)->middleware('auth')->name('diploma-blanks.list-by-import');

Route::post(
    '/diploma-blanks/{diplomaBlankId}/mark-damaged',
    [DiplomaBlankController::class, 'markAsDamaged']
)->middleware('auth')->name('diploma-blanks.mark-damaged');

Route::get(
    '/diploma-blank-management/create',
    [DiplomaBlankImportController::class, 'create']
)->middleware('auth')->name('diploma-blank-import.create');

Route::post(
    '/diploma-blank-management/store',
    [DiplomaBlankImportController::class, 'store']
)->middleware('auth')->name('diploma-blank-import.store');

Route::get(
    '/diploma-blank-management/{import}',
    [DiplomaBlankImportController::class, 'show']
)->middleware('auth')->name('diploma-blank-import.show');

Route::post(
    '/diploma-blank-management/{import}/start',
    [DiplomaBlankImportController::class, 'start']
)->middleware('auth')->name('diploma-blank-import.start');

Route::post(
    '/diploma-blank-management/{import}/pause',
    [DiplomaBlankImportController::class, 'pause']
)->middleware('auth')->name('diploma-blank-import.pause');

Route::post(
    '/diploma-blank-management/{import}/retry',
    [DiplomaBlankImportController::class, 'retry']
)->middleware('auth')->name('diploma-blank-import.retry');

Route::delete(
    '/diploma-blank-management/{import}',
    [DiplomaBlankImportController::class, 'destroy']
)->middleware('auth')->name('diploma-blank-import.destroy');

Route::get(
    '/diploma-blank-management/api/statistics',
    [DiplomaBlankImportController::class, 'statistics']
)->middleware('auth')->name('diploma-blank-import.statistics');

Route::post(
    '/diploma-blank-management/sync',
    [DiplomaBlankImportController::class, 'sync']
)->middleware('auth')->name('diploma-blank-import.sync');

Route::put(
    '/diploma-blank-management/{import}/update',
    [DiplomaBlankImportController::class, 'updateImport']
)->middleware('auth')->name('diploma-blank-import.update')
    ->where('import', '[0-9]+');

Route::get(
    '/diploma-blank-management/{import}/status',
    [DiplomaBlankImportController::class, 'checkUpdateStatus']
)->middleware('auth')->name('diploma-blank-import.status')
    ->where('import', '[0-9]+');

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

// Degree routes
Route::post(
    "degrees/store",
    [DiplomaManagementController::class, 'storeDegree']
)->middleware('auth')->name('degrees.store');

// Logout route
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
