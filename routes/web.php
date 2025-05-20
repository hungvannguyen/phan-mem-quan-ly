<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return view('home');
});

Route::get('/login', function () {
	return view('login');
})->middleware(RedirectIfAuthenticated::class);

Route::get('/diploma-management', function () {
	return view('diploma-management');
});
