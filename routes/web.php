<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return view('home');
})->middleware('auth');

Route::get('/login', function () {
	return view('login');
})->middleware(RedirectIfAuthenticated::class)->name('login');

Route::get('/diploma-management', function () {
	return view('diploma-management');
})->middleware('auth')->name('diploma-management');

Route::get('/embryo-management', function () {
	return view('embryo-management');
})->middleware('auth')->name('embryo-management');

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
