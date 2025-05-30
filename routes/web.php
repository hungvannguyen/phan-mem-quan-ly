<?php

use App\Http\Controllers\DiplomaManagementController;
use App\Http\Controllers\EmbryoManagementController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return view('home');
})->middleware('auth');

Route::get('/login', function () {
	return view('login');
})->middleware(RedirectIfAuthenticated::class)->name('login');

Route::get('/diploma-management',
		[DiplomaManagementController::class, 'index'])->middleware('auth')->name('diploma-management');

Route::get('/embryo-management',
		[EmbryoManagementController::class, 'index'])->middleware('auth')->name('embryo-management');

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

Route::get("student/create",
		[DiplomaManagementController::class, 'create'])->middleware('auth')->name('student.create');

Route::post("student/create",
		[DiplomaManagementController::class, 'save'])->middleware('auth')->name('student.save');

Route::get("student/{student}",
		[DiplomaManagementController::class, 'student'])->middleware('auth')->name('student');

Route::post("student/update/{student}",
		[DiplomaManagementController::class, 'update'])->middleware('auth')->name('student.update');
