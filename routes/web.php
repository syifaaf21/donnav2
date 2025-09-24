<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentMappingController;
use App\Models\DocumentMapping;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', [UserController::class, 'index'])->name('user.index');
Route::resource('users', UserController::class);

Route::get('/documents', [DocumentMappingController::class, 'index'])->name('document.index');
Route::resource('documents', DocumentMappingController::class);

