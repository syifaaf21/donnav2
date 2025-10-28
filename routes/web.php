<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentControlController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FtppMasterController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartNumberController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\UserController;
use App\Models\Department;
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

    Route::get('/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::put('/profile/update-password', [UserController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    Route::prefix('master')->name('master.')->middleware('auth')->group(function () {
        // Part Number Management Routes
        Route::resource('part_numbers', PartNumberController::class);
        Route::get('/part-numbers', [PartNumberController::class, 'index'])->name('part_numbers.index');

        // User Management Routes
        Route::resource('users', UserController::class);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Document Management Routes
        Route::resource('hierarchy', DocumentController::class);
        Route::get('/hierarchy', [DocumentController::class, 'index'])->name('hierarchy.index');

        Route::resource('processes', ProcessController::class);
        Route::get('/processes', [ProcessController::class, 'index'])->name('processes.index');

        Route::resource('departments', DepartmentController::class);
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');

        Route::resource('products', ProductController::class);
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        Route::resource('models', ModelController::class);
        Route::get('/models', [ModelController::class, 'index'])->name('models.index');

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

        // Master FTPP
        Route::prefix('ftpp')->name('ftpp.')->middleware('auth')->group(function () {
            Route::get('/', [FtppMasterController::class, 'index'])->name('index');
            Route::get('/load/{section}', [FtppMasterController::class, 'loadSection'])->name('load');
        });
    });

    Route::prefix('document-review')->name('document-review.')->middleware('auth')->group(function () {
        Route::get('/', [DocumentReviewController::class, 'index'])->name('index');
        Route::get('/get-data-by-plant', [DocumentReviewController::class, 'getDataByPlant'])
            ->name('getDataByPlant');
        Route::get('/show/{id}', [DocumentReviewController::class, 'show'])->name('show');
        Route::post('/{id}/revise', [DocumentReviewController::class, 'revise'])->name('revise');
        Route::post('/{id}/approve-with-dates', [DocumentReviewController::class, 'approveWithDates'])
            ->name('approveWithDates');
        Route::post('/{id}/reject', [DocumentReviewController::class, 'reject'])->name('reject');


        Route::get('/live-search', [DocumentReviewController::class, 'liveSearch'])->name('liveSearch');
    });

    Route::prefix('document-control')->name('document-control.')->middleware('auth')->group(function () {
        Route::get('/', [DocumentControlController::class, 'index'])->name('index');
        Route::post('{mapping}/reject', [DocumentControlController::class, 'reject'])->name('reject');
        Route::post('{mapping}/approve', [DocumentControlController::class, 'approve'])->name('approve');
        Route::post('{mapping}/revise', [DocumentControlController::class, 'revise'])->name('revise');
    });

    // AJAX for Select2 (Product & Model inside Part Number)
    Route::get('/ajax/products', [PartNumberController::class, 'ajaxProductIndex']);
    Route::post('/ajax/products', [PartNumberController::class, 'ajaxProductStore']);

    Route::get('/ajax/models', [PartNumberController::class, 'ajaxModelIndex']);
    Route::post('/ajax/models', [PartNumberController::class, 'ajaxModelStore']);

});
