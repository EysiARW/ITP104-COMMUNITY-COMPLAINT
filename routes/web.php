<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/track', [HomeController::class, 'track'])->name('track');
Route::view('/privacy', 'privacy')->name('privacy');

// "Report" in the navbar: send people to the right place for their role.
Route::get('/report', function () {
    return auth()->check() && auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('complaints.create');
})->name('report');

/*
|--------------------------------------------------------------------------
| Guests only: login, register, password reset
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Logged-in users (both roles)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

    /* Complainant (Aquino) */
    Route::middleware(EnsureRole::class . ':complainant')->group(function () {
        Route::resource('complaints', ComplaintController::class)->only(['index', 'create', 'store', 'show']);
    });

    /* Admin / barangay staff (Balbero, Bandong, Capoy add their routes inside this group) */
    Route::middleware(EnsureRole::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        /* Balbero: All Complaints & Status / Officer Assignment */
        Route::get('complaints', [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('complaints/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->name('complaints.show');
        Route::put('complaints/{complaint}/status', [\App\Http\Controllers\Admin\ComplaintController::class, 'updateStatus'])->name('complaints.status');
        Route::post('complaints/{complaint}/assign', [\App\Http\Controllers\Admin\ComplaintController::class, 'assign'])->name('complaints.assign');

        /* Capoy: Manage Users & Verification Queue */
        Route::get('users/pending', [\App\Http\Controllers\Admin\UserController::class, 'pending'])->name('users.pending');
        Route::get('users/{user}/document', [\App\Http\Controllers\Admin\UserController::class, 'document'])->name('users.document');
        Route::post('users/{user}/approve', [\App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/reject', [\App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        /* Capoy: Manage Departments & Areas */
        Route::get('areas', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'index'])->name('areas.index');
        Route::post('areas', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'storeArea'])->name('areas.store');
        Route::put('areas/{area}', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'updateArea'])->name('areas.update');
        Route::delete('areas/{area}', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'destroyArea'])->name('areas.destroy');

        Route::get('departments', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'index'])->name('departments.index');
        Route::post('departments', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'storeDepartment'])->name('departments.store');
        Route::put('departments/{department}', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'updateDepartment'])->name('departments.update');
        Route::delete('departments/{department}', [\App\Http\Controllers\Admin\AreaDepartmentController::class, 'destroyDepartment'])->name('departments.destroy');

        /* Capoy: Manage Complaint Categories */
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['create', 'show', 'edit']);

        /* Capoy: Reports & Analytics */
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('reports/print', [\App\Http\Controllers\Admin\ReportController::class, 'print'])->name('reports.print');

        /* Capoy: Audit Logs */
        Route::get('logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    });
});
