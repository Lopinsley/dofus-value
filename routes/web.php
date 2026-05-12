<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ItemPageController;
use App\Http\Controllers\Api\AlertController;
use Illuminate\Support\Facades\Route;

// Dashboard principal
Route::get('/', fn() => view('welcome'));

// Auth
Route::get('/login',    [LoginController::class, 'create'])->name('login');
Route::post('/login',   [LoginController::class, 'store']);
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register',[RegisterController::class, 'store']);
Route::post('/logout',  LogoutController::class)->name('logout');

// Page item dédiée (URL partageable)
Route::get('/items/{slug}', [ItemPageController::class, 'show'])->name('items.show');

// Alertes via form HTML (pour la page item)
Route::post('/alerts', [AlertController::class, 'store'])->middleware('auth');
