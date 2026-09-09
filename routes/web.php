<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CommentReviewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemResetController;
use App\Http\Controllers\BazaarDemandController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    // Main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Pre-Bazaar Shopping Checklist & Demand System
    Route::get('/bazaar-demands', [BazaarDemandController::class, 'index'])->name('bazaar-demands.index');
    Route::get('/bazaar-demands/create', [BazaarDemandController::class, 'create'])->name('bazaar-demands.create');
    Route::post('/bazaar-demands', [BazaarDemandController::class, 'store'])->name('bazaar-demands.store');
    Route::get('/bazaar-demands/{demand}', [BazaarDemandController::class, 'show'])->name('bazaar-demands.show');
    Route::get('/bazaar-demands/{demand}/print', [BazaarDemandController::class, 'print'])->name('bazaar-demands.print');
    Route::post('/bazaar-demand-items/{item}/toggle', [BazaarDemandController::class, 'toggleItem'])->name('bazaar-demands.toggle-item');
    Route::delete('/bazaar-demands/{demand}', [BazaarDemandController::class, 'destroy'])->name('bazaar-demands.destroy');

    // Dedicated Voucher-Wise Bill Verification & Inspection Portal
    Route::get('/voucher-check', [ExpenseController::class, 'voucherCheck'])->name('expenses.voucher-check');

    // Expense Management (Bazaar Expense & Bill Submission)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('role:principal');
    Route::get('/expenses/{expense}/whatsapp', [ExpenseController::class, 'whatsapp'])->name('expenses.whatsapp');

    // Comments, Reviews & Verification
    Route::post('/expenses/{expense}/comments', [CommentReviewController::class, 'storeComment'])->name('expenses.comments.store');
    Route::delete('/comments/{comment}', [CommentReviewController::class, 'destroyComment'])->name('expenses.comments.destroy');
    Route::patch('/expenses/{expense}/status', [CommentReviewController::class, 'updateStatus'])->name('expenses.status.update');

    // Wallet & Fund Operations
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('/wallets/top-up', [WalletController::class, 'topUp'])->name('wallets.topup')->middleware('role:principal,pa');
    Route::post('/fund-requests', [WalletController::class, 'storeFundRequest'])->name('fund-requests.store');
    Route::patch('/fund-requests/{fundRequest}/action', [WalletController::class, 'actionFundRequest'])->name('fund-requests.action')->middleware('role:principal,pa');
    Route::delete('/fund-requests/{fundRequest}', [WalletController::class, 'destroyFundRequest'])->name('fund-requests.destroy')->middleware('role:principal');
    Route::delete('/wallet-transactions/{transaction}', [WalletController::class, 'destroyTransaction'])->name('wallet-transactions.destroy')->middleware('role:principal');

    // Super Admin Full System Reset, 1-Click DB Backup & Monthly Budget Setting
    Route::post('/system/reset-all', [SystemResetController::class, 'resetAll'])->name('system.reset-all')->middleware('role:principal');
    Route::get('/system/backup', [SystemResetController::class, 'downloadBackup'])->name('system.backup')->middleware('role:principal');
    Route::post('/system/backup-upload', [SystemResetController::class, 'uploadBackup'])->name('system.backup-upload')->middleware('role:principal');
    Route::post('/system/budget', [SystemResetController::class, 'updateBudget'])->name('system.budget')->middleware('role:principal,pa');
    Route::get('/system/db-update', function () {
        require public_path('auto_update_db.php');
        exit;
    })->name('system.db-update');

    // Super Admin User Management (Create, Edit & Delete Users)
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('role:principal');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('role:principal');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('role:principal');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('role:principal');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('role:principal');

    // Reports, CSV/Excel Export & Print Views
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/print-day', [ReportController::class, 'printDay'])->name('reports.print-day');
    Route::get('/reports/print-range', [ReportController::class, 'printRange'])->name('reports.print-range');
});

// Guaranteed Storage Image Serving (Works in all hosting environments with/without symlink)
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path("app/public/{$folder}/{$filename}");
    if (!file_exists($path)) {
        $path = public_path("storage/{$folder}/{$filename}");
    }
    if (!file_exists($path)) {
        abort(404);
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'pdf'  => 'application/pdf',
    ];
    $mime = $mimes[$ext] ?? 'image/jpeg';
    return response()->file($path, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('folder', '[A-Za-z0-9_-]+')->where('filename', '[A-Za-z0-9_.-]+');

