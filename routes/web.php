<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
	return view('home');
});

Route::get('/login', function () {
	return view('login');
})->middleware(RedirectIfAuthenticated::class);

Route::get('/diploma-management', function () {
	return view('diploma-management');
});

Route::get('/embryo-management', function () {
	return view('embryo-management');
});
