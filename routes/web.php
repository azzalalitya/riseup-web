<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Api\SummaryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| File ini hanya memuat route dasar + Auth. Menu User dan Admin
| dipisah ke routes/student.php dan routes/admin.php agar setiap
| bagian terkelompok per menu/role.
*/

Route::get('/', function () {
    // Root langsung mengarah ke login user (admin punya URL tersembunyi).
    return redirect()->route('login');
});

/* ---------------------------- Auth USER ---------------------------- */
Route::get('/login', [UserAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.process');
Route::post('/register', [UserAuthController::class, 'register'])->name('register.process');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('/auth/google/redirect',  [UserAuthController::class, 'googleRedirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback',  [UserAuthController::class, 'googleCallback'])->name('auth.google.callback');

/* ---------------------------- Auth ADMIN (URL tersembunyi) ---------------------------- */
Route::get('/admin-access',  [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin-access', [AdminAuthController::class, 'login'])->name('admin.login.process');
Route::post('/admin-access/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

/* ---------------------------- API (session-based) ---------------------------- */
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/student-summary', [SummaryController::class, 'studentSummary'])->name('student.summary');
    Route::get('/admin-summary', [SummaryController::class, 'adminSummary'])->name('admin.summary');
});

/* ---------------------------- Midtrans Webhook (public, no CSRF) ---------------------------- */
Route::post('/midtrans/callback', [\App\Http\Controllers\Student\SaveUpController::class, 'midtransCallback'])
    ->name('midtrans.callback');

/* ---------------------------- Menu terpisah per role ---------------------------- */
require __DIR__.'/student.php';
require __DIR__.'/admin.php';
