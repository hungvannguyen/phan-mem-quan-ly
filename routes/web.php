<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DiplomaBlankController;
use App\Http\Controllers\DiplomaBlankExportController;
use App\Http\Controllers\DiplomaBlankImportController;
use App\Http\Controllers\DiplomaBlankRecallController;
use App\Http\Controllers\DiplomaManagementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/error', fn() => view('error'))->name('error');
Route::get('/test-error', fn() => abort(404));

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(RedirectIfAuthenticated::class)
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Home / Dashboard
    Route::get('/', [StatisticsController::class, 'index'])
        ->middleware('permission:diplomas.view')
        ->name('home');

    // About Page
    Route::get('/about', fn() => view('about'))->name('about');

    /*
    |--------------------------------------------------------------------------
    | Profile Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::patch('/', [ProfileController::class, 'updateProfile'])->name('update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    /*
    |--------------------------------------------------------------------------
    | Statistics Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('statistics')->name('statistics.')->middleware('permission:diplomas.view')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/diplomas', [StatisticsController::class, 'getDiplomaStatistics'])->name('diplomas');
        Route::get('/certificates', [StatisticsController::class, 'getCertificateStatistics'])->name('certificates');
        Route::get('/export-bachelor-info', [StatisticsController::class, 'exportBachelorInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-bachelor-info');
        Route::get('/export-master-info', [StatisticsController::class, 'exportMasterInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-master-info');
        Route::get('/export-doctorate-info', [StatisticsController::class, 'exportDoctorateInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-doctorate-info');
        Route::get('/export-advanced-political-theory-info', [StatisticsController::class, 'exportAdvancedPoliticalTheoryInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-advanced-political-theory-info');
        Route::get('/export-intermediate-political-theory-info', [StatisticsController::class, 'exportIntermediatePoliticalTheoryInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-intermediate-political-theory-info');
        Route::get('/export-all-certificates-info', [StatisticsController::class, 'exportAllCertificatesInfo'])
            ->middleware('permission:diplomas.export')
            ->name('export-all-certificates-info');
    });

    /*
    |--------------------------------------------------------------------------
    | Diploma Management Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:diplomas.view')->group(function () {
        Route::get('/diploma-management', [DiplomaManagementController::class, 'index'])->name('diploma-management');

        // Students
        Route::prefix('student')->name('student.')->group(function () {
            Route::get('/create', [DiplomaManagementController::class, 'create'])
                ->middleware('permission:diplomas.create')
                ->name('create');
            Route::post('/create', [DiplomaManagementController::class, 'save'])
                ->middleware('permission:diplomas.create')
                ->name('save');
            Route::get('/{student:student_id}', [DiplomaManagementController::class, 'student'])->name('show');
            Route::post('/update/{student:student_id}', [DiplomaManagementController::class, 'update'])
                ->middleware('permission:diplomas.edit')
                ->name('update');
            Route::delete('/{student:student_id}/delete', [DiplomaManagementController::class, 'deleteStudent'])
                ->middleware('permission:diplomas.delete')
                ->name('delete');
            Route::get('/{student:student_id}/export-verification', [DiplomaManagementController::class, 'exportDiplomaVerification'])
                ->name('export-verification');
            Route::get('/{student:student_id}/export-bachelor-confirmation', [DiplomaManagementController::class, 'exportBachelorConfirmation'])
                ->name('export-bachelor-confirmation');
        });

        // Degrees
        Route::prefix('degrees')->name('degrees.')->group(function () {
            Route::post('/store', [DiplomaManagementController::class, 'storeDegree'])
                ->middleware('permission:diplomas.create')
                ->name('store');
            Route::put('/{degree}/update', [DiplomaManagementController::class, 'updateDegree'])
                ->middleware('permission:diplomas.edit')
                ->name('update');
            Route::delete('/{degree}/delete', [DiplomaManagementController::class, 'deleteDegree'])
                ->middleware('permission:diplomas.delete')
                ->name('delete');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Diploma Blanks Management Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:diploma-blanks.view')->group(function () {

        // Diploma Blanks - General
        Route::prefix('diploma-blanks')->name('diploma-blanks.')->group(function () {
            Route::get('/', [DiplomaBlankController::class, 'index'])->name('index');
            Route::get('/import', [DiplomaBlankController::class, 'import'])
                ->middleware('permission:diploma-blanks.create')
                ->name('import');
            Route::post('/import', [DiplomaBlankController::class, 'processImport'])
                ->middleware('permission:diploma-blanks.create')
                ->name('process-import');
            Route::post('/check-duplicates', [DiplomaBlankController::class, 'checkDuplicates'])->name('check-duplicates');
            Route::get('-management/{importId}', [DiplomaBlankController::class, 'indexByImport'])->name('management-by-import');
            Route::post('/{diplomaBlankId}/mark-damaged', [DiplomaBlankController::class, 'markAsDamaged'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('mark-damaged');
        });

        // Diploma Blank Import Management
        Route::prefix('diploma-blank-management')->name('diploma-blank-import.')->group(function () {
            Route::get('/', [DiplomaBlankImportController::class, 'index'])->name('index');
            Route::get('/create', [DiplomaBlankImportController::class, 'create'])
                ->middleware('permission:diploma-blanks.create')
                ->name('create');
            Route::post('/store', [DiplomaBlankImportController::class, 'store'])
                ->middleware('permission:diploma-blanks.create')
                ->name('store');
            Route::get('/import', [DiplomaBlankController::class, 'showImportForm'])
                ->middleware('permission:diploma-blanks.create')
                ->name('show-import-form');
            Route::post('/import', [DiplomaBlankController::class, 'storeImport'])
                ->middleware('permission:diploma-blanks.create')
                ->name('store-import');
            Route::post('/validate-range', [DiplomaBlankController::class, 'validateRange'])->name('validate-range');
            Route::post('/sync', [DiplomaBlankImportController::class, 'sync'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('sync');
            Route::get('/api/statistics', [DiplomaBlankImportController::class, 'statistics'])->name('statistics');

            // Specific Import Operations
            Route::get('/{import}', [DiplomaBlankImportController::class, 'show'])->name('show');
            Route::get('/{import}/status', [DiplomaBlankImportController::class, 'checkUpdateStatus'])
                ->where('import', '[0-9]+')
                ->name('status');
            Route::put('/{import}/update', [DiplomaBlankImportController::class, 'updateImport'])
                ->middleware('permission:diploma-blanks.edit')
                ->where('import', '[0-9]+')
                ->name('update');
            Route::post('/{import}/start', [DiplomaBlankImportController::class, 'start'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('start');
            Route::post('/{import}/pause', [DiplomaBlankImportController::class, 'pause'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('pause');
            Route::post('/{import}/retry', [DiplomaBlankImportController::class, 'retry'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('retry');
            Route::delete('/{import}', [DiplomaBlankImportController::class, 'destroy'])
                ->middleware('permission:diploma-blanks.delete')
                ->name('destroy');
        });

        // Diploma Blank Exports
        Route::prefix('diploma-blank-exports')->name('diploma-blank-exports.')->group(function () {
            Route::get('/', [DiplomaBlankExportController::class, 'index'])->name('index');
            Route::get('/create', [DiplomaBlankExportController::class, 'create'])
                ->middleware('permission:diploma-blanks.export')
                ->name('create');
            Route::post('/suggested-ranges', [DiplomaBlankExportController::class, 'getSuggestedRanges'])->name('suggested-ranges');
            Route::post('/validate-range', [DiplomaBlankExportController::class, 'validateCustomRange'])->name('validate-range');
            Route::post('/store', [DiplomaBlankExportController::class, 'store'])
                ->middleware('permission:diploma-blanks.export')
                ->name('store');
            Route::get('/{export}', [DiplomaBlankExportController::class, 'show'])->name('show');
        });

        // Diploma Blank Recalls
        Route::prefix('diploma-blank-recalls')->name('diploma-blank-recalls.')->group(function () {
            Route::get('/', [DiplomaBlankRecallController::class, 'index'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('index');
            Route::get('/management', [DiplomaBlankRecallController::class, 'recalledList'])->name('management');
            Route::post('/check-serial', [DiplomaBlankRecallController::class, 'checkSerial'])->name('check-serial');
            Route::post('/recall', [DiplomaBlankRecallController::class, 'recall'])
                ->middleware('permission:diploma-blanks.edit')
                ->name('recall');
            Route::get('/statistics', [DiplomaBlankRecallController::class, 'statistics'])->name('statistics');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Certificate Management Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/certificate-management', fn() => view('certificate-management'))
        ->middleware('permission:certificates.view')
        ->name('certificate-management');

    /*
    |--------------------------------------------------------------------------
    | Settings Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // Diploma Blank Types
        Route::middleware('permission:settings.edit')->prefix('types')->name('types.')->group(function () {
            Route::get('/create', [SettingsController::class, 'createType'])->name('create');
            Route::post('/', [SettingsController::class, 'storeType'])->name('store');
            Route::get('/{type:type_id}/edit', [SettingsController::class, 'editType'])->name('edit');
            Route::put('/{type:type_id}', [SettingsController::class, 'updateType'])->name('update');
            Route::delete('/{type:type_id}', [SettingsController::class, 'destroyType'])->name('destroy');
        });

        // Majors
        Route::middleware('permission:settings.edit')->prefix('majors')->name('majors.')->group(function () {
            Route::get('/create', [SettingsController::class, 'createMajor'])->name('create');
            Route::post('/', [SettingsController::class, 'storeMajor'])->name('store');
            Route::get('/{major:major_id}/edit', [SettingsController::class, 'editMajor'])->name('edit');
            Route::put('/{major:major_id}', [SettingsController::class, 'updateMajor'])->name('update');
            Route::delete('/{major:major_id}', [SettingsController::class, 'destroyMajor'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('permission:users.view')->group(function () {
        Route::get('/user-management', [UserController::class, 'index'])->name('user-management');

        Route::prefix('users')->name('user.')->group(function () {
            Route::get('/create', [UserController::class, 'create'])
                ->middleware('permission:users.create')
                ->name('create');
            Route::post('/', [UserController::class, 'store'])
                ->middleware('permission:users.create')
                ->name('store');
            Route::get('/{user:user_id}/edit', [UserController::class, 'edit'])
                ->middleware('permission:users.edit')
                ->name('edit');
            Route::put('/{user:user_id}', [UserController::class, 'update'])
                ->middleware('permission:users.edit')
                ->name('update');
            Route::patch('/{user:user_id}/toggle-status', [UserController::class, 'toggleStatus'])
                ->middleware('permission:users.edit')
                ->name('toggle-status');
            Route::delete('/{user:user_id}', [UserController::class, 'destroy'])
                ->middleware('permission:users.delete')
                ->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Permission Management Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('permissions')->name('permissions.')->middleware('permission:users.edit')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permission:permission_id}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permission:permission_id}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission:permission_id}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | API Routes (Internal)
    |--------------------------------------------------------------------------
    */

    Route::prefix('api')->name('api.')->middleware('permission:diplomas.view')->group(function () {
        Route::get('/diploma-blanks/available/{typeId}', [DiplomaManagementController::class, 'getAvailableDiplomaBlanks'])
            ->name('diploma-blanks.available');
    });
});
