<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DiplomaBlankController;
use App\Http\Controllers\DiplomaBlankImportController;
use App\Http\Controllers\DiplomaManagementController;
use App\Http\Controllers\EmbryoManagementController;
use App\Http\Controllers\StatisticsController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [StatisticsController::class, 'index'])->middleware('auth')->name('home');

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
    '/diploma-blanks',
    [DiplomaBlankController::class, 'index']
)->middleware('auth')->name('diploma-blanks.index');

Route::get(
    '/diploma-blanks-management/{importId}',
    [DiplomaBlankController::class, 'indexByImport']
)->middleware('auth')->name('diploma-blanks.management-by-import');

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

// Diploma Blank Export routes
Route::get(
    '/diploma-blank-exports',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'index']
)->middleware('auth')->name('diploma-blank-exports.index');

Route::get(
    '/diploma-blank-exports/create',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'create']
)->middleware('auth')->name('diploma-blank-exports.create');

Route::post(
    '/diploma-blank-exports/suggested-ranges',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'getSuggestedRanges']
)->middleware('auth')->name('diploma-blank-exports.suggested-ranges');

Route::post(
    '/diploma-blank-exports/validate-range',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'validateCustomRange']
)->middleware('auth')->name('diploma-blank-exports.validate-range');

Route::post(
    '/diploma-blank-exports/store',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'store']
)->middleware('auth')->name('diploma-blank-exports.store');

Route::get(
    '/diploma-blank-exports/{export}',
    [App\Http\Controllers\DiplomaBlankExportController::class, 'show']
)->middleware('auth')->name('diploma-blank-exports.show');

Route::get('/certificate-management', function () {
    return view('certificate-management');
})->middleware('auth')->name('certificate-management');

// Settings Routes
Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])
    ->middleware('auth')
    ->name('settings.index');

// Diploma Blank Type Routes
Route::get('/settings/types/create', [App\Http\Controllers\SettingsController::class, 'createType'])
    ->middleware('auth')
    ->name('settings.types.create');

Route::post('/settings/types', [App\Http\Controllers\SettingsController::class, 'storeType'])
    ->middleware('auth')
    ->name('settings.types.store');

Route::get('/settings/types/{type:type_id}/edit', [App\Http\Controllers\SettingsController::class, 'editType'])
    ->middleware('auth')
    ->name('settings.types.edit');

Route::put('/settings/types/{type:type_id}', [App\Http\Controllers\SettingsController::class, 'updateType'])
    ->middleware('auth')
    ->name('settings.types.update');

Route::delete('/settings/types/{type:type_id}', [App\Http\Controllers\SettingsController::class, 'destroyType'])
    ->middleware('auth')
    ->name('settings.types.destroy');

// Major Routes
Route::get('/settings/majors/create', [App\Http\Controllers\SettingsController::class, 'createMajor'])
    ->middleware('auth')
    ->name('settings.majors.create');

Route::post('/settings/majors', [App\Http\Controllers\SettingsController::class, 'storeMajor'])
    ->middleware('auth')
    ->name('settings.majors.store');

Route::get('/settings/majors/{major:major_id}/edit', [App\Http\Controllers\SettingsController::class, 'editMajor'])
    ->middleware('auth')
    ->name('settings.majors.edit');

Route::put('/settings/majors/{major:major_id}', [App\Http\Controllers\SettingsController::class, 'updateMajor'])
    ->middleware('auth')
    ->name('settings.majors.update');

Route::delete('/settings/majors/{major:major_id}', [App\Http\Controllers\SettingsController::class, 'destroyMajor'])
    ->middleware('auth')
    ->name('settings.majors.destroy');

// User Management Routes
Route::get('/user-management', [App\Http\Controllers\UserController::class, 'index'])
    ->middleware('auth')
    ->name('user-management');

Route::get('/users/create', [App\Http\Controllers\UserController::class, 'create'])
    ->middleware('auth')
    ->name('user.create');

Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])
    ->middleware('auth')
    ->name('user.store');

Route::get('/users/{user:user_id}/edit', [App\Http\Controllers\UserController::class, 'edit'])
    ->middleware('auth')
    ->name('user.edit');

Route::put('/users/{user:user_id}', [App\Http\Controllers\UserController::class, 'update'])
    ->middleware('auth')
    ->name('user.update');

Route::delete('/users/{user:user_id}', [App\Http\Controllers\UserController::class, 'destroy'])
    ->middleware('auth')
    ->name('user.destroy');

Route::patch('/users/{user:user_id}/toggle-status', [App\Http\Controllers\UserController::class, 'toggleStatus'])
    ->middleware('auth')
    ->name('user.toggle-status');

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

Route::delete(
    "student/{student:student_id}/delete",
    [DiplomaManagementController::class, 'deleteStudent']
)->middleware('auth')->name('student.delete');

// Degree routes
Route::post(
    "degrees/store",
    [DiplomaManagementController::class, 'storeDegree']
)->middleware('auth')->name('degrees.store');

Route::put(
    "degrees/{degree}/update",
    [DiplomaManagementController::class, 'updateDegree']
)->middleware('auth')->name('degrees.update');

Route::delete(
    "degrees/{degree}/delete",
    [DiplomaManagementController::class, 'deleteDegree']
)->middleware('auth')->name('degrees.delete');

// API route for getting available diploma blanks
Route::get(
    "api/diploma-blanks/available/{typeId}",
    [DiplomaManagementController::class, 'getAvailableDiplomaBlanks']
)->middleware('auth')->name('api.diploma-blanks.available');

// Statistics routes
Route::get(
    '/statistics',
    [StatisticsController::class, 'index']
)->middleware('auth')->name('statistics.index');

Route::post(
    '/statistics/chart-data',
    [StatisticsController::class, 'getChartData']
)->middleware('auth')->name('statistics.chart-data');

Route::post(
    '/statistics/export',
    [StatisticsController::class, 'export']
)->middleware('auth')->name('statistics.export');

// Diploma Blank Recall routes
Route::get(
    '/diploma-blank-recalls',
    [App\Http\Controllers\DiplomaBlankRecallController::class, 'index']
)->middleware('auth')->name('diploma-blank-recalls.index');

Route::get(
    '/diploma-blank-recalls/management',
    [App\Http\Controllers\DiplomaBlankRecallController::class, 'recalledList']
)->middleware('auth')->name('diploma-blank-recalls.management');

Route::post(
    '/diploma-blank-recalls/check-serial',
    [App\Http\Controllers\DiplomaBlankRecallController::class, 'checkSerial']
)->middleware('auth')->name('diploma-blank-recalls.check-serial');

Route::post(
    '/diploma-blank-recalls/recall',
    [App\Http\Controllers\DiplomaBlankRecallController::class, 'recall']
)->middleware('auth')->name('diploma-blank-recalls.recall');

Route::get(
    '/diploma-blank-recalls/statistics',
    [App\Http\Controllers\DiplomaBlankRecallController::class, 'statistics']
)->middleware('auth')->name('diploma-blank-recalls.statistics');

// Logout route
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');