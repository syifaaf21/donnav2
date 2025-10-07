<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentControlController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\PartNumberController;
use App\Http\Controllers\UserController;
use App\Models\Document;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Facades\Auth;
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

// Master Data Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/index', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('master')->name('master.')->middleware('auth')->group(function() {
        // Part Number Management Routes
        Route::resource('part_numbers', PartNumberController::class);
        Route::get('/part-numbers', [PartNumberController::class, 'index'])->name('part_numbers.index');

        // User Management Routes
        Route::resource('users', UserController::class);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Document Management Routes
        Route::resource('hierarchy', DocumentController::class);
        Route::get('/hierarchy', [DocumentController::class, 'index'])->name('hierarchy.index');

        Route::prefix('document-review')->name('document-review.')->middleware('auth')->group(function () {
            Route::get('/', [DocumentMappingController::class, 'reviewIndex'])->name('index');

            // Admin routes
            Route::post('/store', [DocumentMappingController::class, 'storeReview'])->name('store');
            Route::put('/update/{mapping}', [DocumentMappingController::class, 'updateReview'])->name('update');
            Route::delete('/destroy/{mapping}', [DocumentMappingController::class, 'destroy'])->name('destroy');
            Route::post('/reject/{mapping}', [DocumentMappingController::class, 'reject'])->name('reject');
            Route::post('{mapping}/approve', [DocumentMappingController::class, 'approveWithDates'])->name('approveWithDates');

            // User route
            Route::post('/revise/{mapping}', [DocumentMappingController::class, 'revise'])->name('revise');
        });

        Route::prefix('document-control')->name('document-control.')->middleware('auth')->group(function () {
            Route::get('/', [DocumentMappingController::class, 'controlIndex'])->name('index');
            Route::post('/store', [DocumentMappingController::class, 'storeControl'])->name('store');
            Route::post('/reject/{mapping}', [DocumentMappingController::class, 'reject'])->name('reject');
            Route::post('{mapping}/approve', [DocumentMappingController::class, 'approveControl'])->name('approve');
            Route::put('/update/{mapping}', [DocumentMappingController::class, 'updateControl'])->name('update');
            Route::delete('/destroy/{mapping}', [DocumentMappingController::class, 'destroy'])->name('destroy');

            Route::post('/revise/{mapping}', [DocumentMappingController::class, 'revise'])->name('revise');

        });

        Route::post('/bulk-destroy', [DocumentMappingController::class, 'bulkDestroy'])
            ->name('bulkDestroy');
    });

    Route::prefix('document-review')->name('document-review.')->middleware('auth')->group(function () {
        Route::get('/', [DocumentReviewController::class, 'Index'])->name('index');

        // Admin routes
        // Route::post('/store', [DocumentReviewController::class, 'storeReview'])->name('store');
        // Route::put('/update/{mapping}', [DocumentReviewController::class, 'updateReview'])->name('update');
        // Route::delete('/destroy/{mapping}', [DocumentReviewController::class, 'destroy'])->name('destroy');
        // Route::post('/reject/{mapping}', [DocumentReviewController::class, 'reject'])->name('reject');
        // Route::post('{mapping}/approve', [DocumentReviewController::class, 'approveWithDates'])->name('approveWithDates');

        // User route
        // Route::post('/revise/{mapping}', [DocumentReviewController::class, 'revise'])->name('revise');
    });

    Route::prefix('document-control')->name('document-control.')->middleware('auth')->group(function () {
        Route::get('/', [DocumentControlController::class, 'index'])->name('index');
        Route::post('{mapping}/reject', [DocumentControlController::class, 'reject'])->name('reject');
        Route::post('{mapping}/approve', [DocumentControlController::class, 'approve'])->name('approve');
        Route::post('{mapping}/revise', [DocumentControlController::class, 'revise'])->name('revise');
    });


    // Route::post('/bulk-destroy', [DocumentControlController::class, 'bulkDestroy'])
    //     ->name('bulkDestroy');

});



