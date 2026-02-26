<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuditeeActionController;
use App\Http\Controllers\AuditFindingController;
use App\Http\Controllers\AuditTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentControlController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentMappingController;
use App\Http\Controllers\DocumentReviewController;
use App\Http\Controllers\DocumentControlWatermarkController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentNumberController;
use App\Http\Controllers\FindingCategoryController;
use App\Http\Controllers\FtppApprovalController;
use App\Http\Controllers\FtppController;
use App\Http\Controllers\FtppMasterController;
use App\Http\Controllers\KlausulController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartNumberController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportSummaryController;
use App\Http\Controllers\EditorController;
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
        Route::get('/get-filter-options', [DocumentMappingController::class, 'getFilterOptions'])->name('get-filter-options');
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

    /*
    |--------------------------------------------------------------------------
    | Master FTPP & related
    |--------------------------------------------------------------------------
    */
    Route::prefix('ftpp')->name('ftpp.')->group(function () {
        Route::get('/', [FtppMasterController::class, 'index'])->name('index');
        Route::get('/load/{section}/{id?}', [FtppMasterController::class, 'loadSection'])->name('load');

        // Audit
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/', [AuditTypeController::class, 'index'])->name('index');
            Route::get('/{id}', [AuditTypeController::class, 'show']);
            Route::post('/store', [AuditTypeController::class, 'store'])->name('store');
            Route::put('/update/{id}', [AuditTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [AuditTypeController::class, 'destroy'])->name('destroy');
        });

        // Finding Category
        Route::prefix('finding-category')->name('finding-category.')->group(function () {
            Route::get('/', [FindingCategoryController::class, 'index'])->name('index');
            Route::get('/{id}', [FindingCategoryController::class, 'show']);
            Route::post('/', [FindingCategoryController::class, 'store'])->name('store');
            Route::put('/{id}', [FindingCategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [FindingCategoryController::class, 'destroy'])->name('destroy');
        });

        // Klausul
        Route::prefix('klausul')->name('klausul.')->group(function () {
            Route::get('/', [KlausulController::class, 'index'])->name('index');
            Route::get('/{id}', [KlausulController::class, 'show']);
            Route::post('/', [KlausulController::class, 'store'])->name('store');
            Route::put('/update/{id}', [KlausulController::class, 'update'])->name('update');
            Route::put('/update-main/{id}', [KlausulController::class, 'updateMain'])->name('update-main');
            Route::delete('/{id}', [KlausulController::class, 'destroy'])->name('destroy');
            Route::delete('/destroy-main/{id}', [KlausulController::class, 'destroyMain'])->name('destroy-main');

            // Sub Klausul CRUD
            Route::post('/sub', [KlausulController::class, 'storeSub'])->name('sub.store');
            Route::put('/sub/{id}', [KlausulController::class, 'updateSub'])->name('sub.update');
            Route::delete('/sub/{id}', [KlausulController::class, 'destroySub'])->name('sub.destroy');
        });
    });
    Route::resource('users', UserController::class);
});

// Recycle bin routes (Super Admin only)
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::get('/recycle-bin', [\App\Http\Controllers\RecycleBinController::class, 'index'])->name('recycle.index');
    Route::post('/recycle-bin/{id}/restore', [\App\Http\Controllers\RecycleBinController::class, 'restore'])->name('recycle.restore');
    Route::post('/recycle-bin/{id}/force-delete', [\App\Http\Controllers\RecycleBinController::class, 'forceDelete'])->name('recycle.force-delete');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'password.expired'])->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/document-number/generate', [DocumentNumberController::class, 'generate'])->name('document-number.generate');
    Route::get('/dashboard/control', [DashboardController::class, 'controlDashboard'])->name('dashboard.control');
    Route::get('/dashboard/review', [DashboardController::class, 'reviewDashboard'])->name('dashboard.review');
    Route::get('/dashboard/ftpp', [DashboardController::class, 'ftppDashboard'])->name('dashboard.ftpp');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::put('/profile/update-password', [UserController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    // Editor (OnlyOffice) - menu untuk mengedit dokumen
    // Route::get('/editor', [EditorController::class, 'index'])->name('editor.index');
    // Route::get('/editor/files', [EditorController::class, 'files'])->name('editor.files');

    // routes/web.php — ganti grup editor dengan ini:

    Route::prefix('editor')->name('editor.')->group(function () {
        Route::get('/', [EditorController::class, 'index'])->name('index');
        Route::get('/auth-token', [EditorController::class, 'authToken'])->name('token');  // ← HARUS sebelum /{file}
        Route::get('/{file}', [EditorController::class, 'editor'])->name('show');
        Route::post('/{file}/sync', [EditorController::class, 'sync'])->name('sync');
        Route::post('/{file}/reupload', [EditorController::class, 'reupload'])->name('reupload');
    });
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
        Route::get('/{id}/files', [DocumentReviewController::class, 'getFiles'])
            ->name('get-files');
        Route::get('/file/{file}/download-watermarked', [DocumentControlWatermarkController::class, 'downloadWatermarkedFile'])->name('downloadWatermarkedFile');
        Route::post('/{id}/approve-with-dates', [DocumentReviewController::class, 'approveWithDates'])->name('approveWithDates');
        Route::post('/{id}/reject', [DocumentReviewController::class, 'reject'])->name('reject');
        Route::get('/live-search', [DocumentReviewController::class, 'liveSearch'])->name('liveSearch');
        Route::get('/get-filters', [DocumentReviewController::class, 'getFiltersByPlant'])->name('getFiltersByPlant');
        Route::get('/{id}/download-report', [DocumentReviewController::class, 'getDownloadReport'])->name('document-review.download-report');
        Route::post('/{id}/log-download', [DocumentReviewController::class, 'logDownload'])->name('document-review.log-download');
        Route::get('/{id}/check-logs', [DocumentReviewController::class, 'checkDownloadLogs'])->name('document-review.check-logs');
    });

    // Document Control
    Route::prefix('document-control')->name('document-control.')->group(function () {
        Route::get('/', [DocumentControlController::class, 'index'])->name('index');
        Route::get('/department/{department}', [DocumentControlController::class, 'showByDepartment'])->name('department');
        Route::get('/approval', [DocumentControlController::class, 'approvalIndex'])
            ->name('approval');
        Route::post('{mapping}/reject', [DocumentControlController::class, 'reject'])->name('reject');
        Route::post('{mapping}/approve', [DocumentControlController::class, 'approve'])->name('approve');
        Route::post('{mapping}/revise', [DocumentControlController::class, 'revise'])->name('revise');
        Route::get('{mapping}/download-watermarked', [DocumentControlWatermarkController::class, 'downloadWatermarked'])->name('downloadWatermarked');
    });

    Route::prefix('archive')->name('archive.')->group(function () {
        Route::get('/', [ArchiveController::class, 'index'])
            ->name('index');
        Route::get('/search', [ArchiveController::class, 'search'])->name('search');
        Route::get('/review', [ArchiveController::class, 'review'])->name('review');
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
    | FTPP & related
    |--------------------------------------------------------------------------
    */
    Route::prefix('ftpp')->name('ftpp.')->group(function () {
        // Export summary (Excel template-based)
        Route::get('/export-summary', [ExportSummaryController::class, 'download'])->name('export.summary');

        Route::get('/{id}/preview-pdf', [FtppController::class, 'previewPdf'])->name('previewPdf');
        Route::get('/get-data/{auditTypeId}', [FtppController::class, 'getData']);
        Route::get('/search', [FtppController::class, 'search'])->name('search');
        Route::get('/', [FtppController::class, 'index'])->name('index');
        Route::get('/{id}', [FtppController::class, 'show'])->name('show');
        Route::delete('/{id}', [FtppController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [FtppController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::get('/{id}/download', [FtppController::class, 'download'])->name('download');
        Route::put('/{id}', [FtppController::class, 'update'])->name('update');
        // Upload Evidence
        Route::get('/{id}/evidence/upload', [AuditFindingController::class, 'showUploadEvidenceForm'])->name('evidence.upload');
        Route::post('/{id}/evidence/upload', [AuditFindingController::class, 'uploadEvidence'])->name('evidence.upload.post');

        Route::prefix('audit-finding')->name('audit-finding.')->group(function () {
            // Audit Finding routes
            Route::get('/create', [AuditFindingController::class, 'create'])->name('create');
            Route::post('/store', [AuditFindingController::class, 'store'])
                ->name('store');
            // Temp upload for client-side compression + preview
            Route::post('/upload-temp', [AuditFindingController::class, 'uploadTemp'])->name('uploadTemp');
            Route::get('/{id}/edit', [AuditFindingController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AuditFindingController::class, 'update'])->name('update');
            // Immediate delete endpoints for UI remove buttons (AJAX)
            Route::delete('/{id}/auditee/{auditee}', [AuditFindingController::class, 'destroyAuditee'])->name('auditee.destroy');
            Route::delete('/{id}/sub-klausul/{sub}', [AuditFindingController::class, 'destroySubKlausul'])->name('subklausul.destroy');
            // Attachment delete (AJAX)
            Route::delete('/attachment/{id}', [AuditFindingController::class, 'destroyAttachment'])->name('attachment.destroy');
            // Bulk notify auditees for selected findings
            Route::post('/bulk-notify', [AuditFindingController::class, 'bulkNotify'])->name('bulk-notify');
        });

        Route::prefix('auditee-action')->name('auditee-action.')->group(function () {
            // Auditee Action routes
            Route::get('/{id}', [AuditeeActionController::class, 'create'])->name('create');
            Route::post('/store', [AuditeeActionController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AuditeeActionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AuditeeActionController::class, 'update'])->name('update');
            // Attachment delete (AJAX)
            Route::delete('/attachment/{id}', [AuditeeActionController::class, 'destroyAttachment'])->name('attachment.destroy');
        });
    });
    Route::prefix('approval')->name('approval.')->group(function () {
        Route::get('/get-data/{auditTypeId}', [FtppApprovalController::class, 'getData']);
        Route::get('/', [FtppApprovalController::class, 'index'])->name('index');
        Route::get('/{id}', [FtppApprovalController::class, 'edit'])->name('edit');
        Route::post('/', [FtppApprovalController::class, 'store'])->name('store');
        Route::post('/ldr-spv-sign', [FtppApprovalController::class, 'ldrSpvSign'])->name('ldr-spv-sign');
        Route::post('/dept-head-sign', [FtppApprovalController::class, 'deptheadSign'])->name('dept-head-sign');
        Route::put('/{id}', [FtppApprovalController::class, 'update'])->name('update');
    });

    Route::get('/filter-klausul/{auditType}', [FtppController::class, 'filterKlausul']);
    Route::get('/head-klausul/{klausulId}', [FtppController::class, 'getHeadKlausul']);
    Route::get('/sub-klausul/{headId}', [FtppController::class, 'getSubKlausul']);

    Route::get('/get-departments/{plant}', [FtppController::class, 'getDepartments']);
    Route::get('/get-processes/{plant}', [FtppController::class, 'getProcesses']);
    Route::get('/get-products/{plant}', [FtppController::class, 'getProducts']);

    Route::get('/get-auditee/{departmentId}', [FtppController::class, 'getAuditee']);

    Route::post('/auditor-verify', [FtppApprovalController::class, 'auditorVerify']);
    Route::post('/auditor-return', [FtppApprovalController::class, 'auditorReturn']);
    Route::post('/lead-auditor-acknowledge', [FtppApprovalController::class, 'leadAuditorAcknowledge']);

    // Route::get('/test-pdf/{id}', function ($id) {
    //     $finding = \App\Models\AuditFinding::with(['auditeeAction.file'])->findOrFail($id);
    //     return view('contents.ftpp2.pdf', compact('finding'));
    // });

    // Route::redirect('/test-editor');
});
