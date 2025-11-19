<?php

use App\Http\Controllers\AuditTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentControlController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FindingCategoryController;
use App\Http\Controllers\FtppController;
use App\Http\Controllers\FtppMasterController;
use App\Http\Controllers\KlausulController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartNumberController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Registration
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Login & Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Master Data Routes
|--------------------------------------------------------------------------
*/

// Master data: Admin & Super Admin
Route::prefix('master')->name('master.')->middleware(['auth', 'role:Admin,Super Admin'])->group(function () {
    Route::resource('part_numbers', PartNumberController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('products', ProductController::class);
    Route::resource('models', ModelController::class);
    Route::resource('processes', ProcessController::class);
    Route::resource('hierarchy', DocumentController::class);
    Route::resource('users', UserController::class);
    Route::prefix('ftpp')->name('ftpp.')->group(function () {
        Route::get('/', [FtppMasterController::class, 'index'])->name('index');
        Route::get('/load/{section}/{id?}', [FtppMasterController::class, 'loadSection'])->name('load');

        // Audit
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/{id}', [AuditTypeController::class, 'show']);
            Route::post('/store', [AuditTypeController::class, 'store'])->name('store');
            Route::put('/update/{id}', [AuditTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [AuditTypeController::class, 'destroy'])->name('destroy');
        });

        // Finding Category
        Route::prefix('finding-category')->name('finding-category.')->group(function () {
            Route::get('/{id}', [FindingCategoryController::class, 'show']);
            Route::post('/', [FindingCategoryController::class, 'store'])->name('store');
            Route::put('/update/{id}', [FindingCategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [FindingCategoryController::class, 'destroy'])->name('destroy');
        });

        // Klausul
        Route::prefix('klausul')->name('klausul.')->group(function () {
            Route::get('/{id}', [KlausulController::class, 'show']);
            Route::post('/', [KlausulController::class, 'store'])->name('store');
            Route::put('/update/{id}', [KlausulController::class, 'update'])->name('update');
            Route::delete('/{id}', [KlausulController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard & Profile
    Route::get('/index', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::put('/profile/update-password', [UserController::class, 'updatePassword'])->name('profile.updatePassword');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notification/{id}/read', [NotificationController::class, 'redirectAndMarkRead'])
        ->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])
        ->name('notifications.markRead');
    /*
    |--------------------------------------------------------------------------
    | Document Review Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-review')->name('document-review.')->group(function () {
        Route::get('/', [DocumentReviewController::class, 'index'])->name('index');
        Route::get('/get-data-by-plant', [DocumentReviewController::class, 'getDataByPlant'])->name('getDataByPlant');
        Route::get('/folder/{plant}/{docCode}', [DocumentReviewController::class, 'showFolder'])
            ->name('showFolder');
        Route::get('/filters', [DocumentReviewController::class, 'getFilters']);
        Route::post('/{id}/revise', [DocumentReviewController::class, 'revise'])->name('revise');
        Route::post('/{id}/approve-with-dates', [DocumentReviewController::class, 'approveWithDates'])->name('approveWithDates');
        Route::post('/{id}/reject', [DocumentReviewController::class, 'reject'])->name('reject');
        Route::get('/live-search', [DocumentReviewController::class, 'liveSearch'])->name('liveSearch');
        Route::get('/get-filters', [DocumentReviewController::class, 'getFiltersByPlant'])->name('getFiltersByPlant');
    });

    // Document Control
    Route::prefix('document-control')->name('document-control.')->group(function () {
        Route::get('/', [DocumentControlController::class, 'index'])->name('index');
        Route::get('/department/{department}', [DocumentControlController::class, 'showByDepartment'])->name('department');
        Route::post('{mapping}/reject', [DocumentControlController::class, 'reject'])->name('reject');
        Route::post('{mapping}/approve', [DocumentControlController::class, 'approve'])->name('approve');
        Route::post('{mapping}/revise', [DocumentControlController::class, 'revise'])->name('revise');
    });

    /*
    |--------------------------------------------------------------------------
    | AJAX for Select2
    |--------------------------------------------------------------------------
    */
    Route::get('/ajax/products', [PartNumberController::class, 'ajaxProductIndex']);
    Route::post('/ajax/products', [PartNumberController::class, 'ajaxProductStore']);
    Route::get('/ajax/models', [PartNumberController::class, 'ajaxModelIndex']);
    Route::post('/ajax/models', [PartNumberController::class, 'ajaxModelStore']);

    /*
    |--------------------------------------------------------------------------
    | Master FTPP & related
    |--------------------------------------------------------------------------
    */
    Route::prefix('ftpp')->name('ftpp.')->group(function () {

        // Master FTTP
        Route::get('/', [FtppController::class, 'index'])->name('index');
        Route::get('/{id}', [FtppController::class, 'show'])->name('show');
        Route::post('/', [FtppController::class, 'store'])->name('store');
        Route::put('/{id}', [FtppController::class, 'update'])->name('update');
        Route::get('/api/klausul', [KlausulController::class, 'getAll']);
    });

    /*
    |--------------------------------------------------------------------------
    | Document Mapping Review & Control
    |--------------------------------------------------------------------------
    */
    Route::prefix('master')->name('master.')->group(function () {
        // Document Review
        Route::prefix('document-review')->name('document-review.')->group(function () {
            // Route::get('/', [DocumentMappingController::class, 'reviewIndex'])->name('index');
            Route::get('/', [DocumentMappingController::class, 'reviewIndex2'])->name('index2');
            // Route::post('/store', [DocumentMappingController::class, 'storeReview'])->name('store');
            Route::post('/store', [DocumentMappingController::class, 'storeReview2'])->name('store2');
            // Route::put('/update/{mapping}', [DocumentMappingController::class, 'updateReview'])->name('update');
            Route::put('/update/{mapping}', [DocumentMappingController::class, 'updateReview2'])->name('update2');
            Route::delete('/destroy/{mapping}', [DocumentMappingController::class, 'destroy'])->name('destroy');
            Route::post('/reject/{mapping}', [DocumentMappingController::class, 'reject'])->name('reject');
            Route::post('{mapping}/approve', [DocumentMappingController::class, 'approveWithDates'])->name('approveWithDates');
            Route::post('/revise/{mapping}', [DocumentMappingController::class, 'revise'])->name('revise');
        });

        // Document Control
        Route::prefix('document-control')->name('document-control.')->group(function () {
            Route::get('/', [DocumentMappingController::class, 'controlIndex'])->name('index');
            Route::post('/store', [DocumentMappingController::class, 'storeControl'])->name('store');
            Route::post('/reject/{mapping}', [DocumentMappingController::class, 'reject'])->name('reject');
            Route::post('{mapping}/approve', [DocumentMappingController::class, 'approveControl'])->name('approve');
            Route::put('/update/{mapping}', [DocumentMappingController::class, 'updateControl'])->name('update');
            Route::delete('/destroy/{mapping}', [DocumentMappingController::class, 'destroy'])->name('destroy');
            Route::post('/revise/{mapping}', [DocumentMappingController::class, 'revise'])->name('revise');
        });

        Route::post('/bulk-destroy', [DocumentMappingController::class, 'bulkDestroy'])->name('bulkDestroy');
    });
});
