<?php

use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PartNumberController;
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


// Route::get('/', function () {
//     return view('index');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

// Login & Logout Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    // Master Part Number Routes
    Route::resource('part_numbers', PartNumberController::class);
    Route::get('/part-numbers', [PartNumberController::class, 'index'])->name('part_numbers.index');
});
