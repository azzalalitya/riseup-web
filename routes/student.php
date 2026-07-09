<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\OnboardingController;
use App\Http\Controllers\Student\CheckinController;
use App\Http\Controllers\Student\SaveUpController;
use App\Http\Controllers\Student\LearningController;
use App\Http\Controllers\Student\QuestController;
use App\Http\Controllers\Student\JournalController;
use App\Http\Controllers\Student\AchievementController;

/*
|--------------------------------------------------------------------------
| Student (User) Routes  -> middleware 'user'
|--------------------------------------------------------------------------
*/

Route::middleware('user')->group(function () {

    /* Onboarding (URL /onboarding, di luar prefix student) */
    Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    /* Semua menu student -> prefix /student, nama student.* */
    Route::prefix('student')->name('student.')->group(function () {

        // Menu: Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Menu: Daily Check-in
        Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
        Route::post('/checkin', [CheckinController::class, 'store'])->name('checkin.store');

        // Menu: U Save Up
        Route::get('/saveup', [SaveUpController::class, 'index'])->name('saveup.index');
        Route::post('/saveup/target', [SaveUpController::class, 'storeTarget'])->name('saveup.target.store');
        Route::delete('/saveup/deposit/{id}', [SaveUpController::class, 'destroyDeposit'])->name('saveup.deposit.destroy');
        // Midtrans (buat token AJAX)
        Route::post('/saveup/snap-token', [SaveUpController::class, 'createSnapToken'])->name('saveup.snap.token');
        // Penarikan dana (vault)
        Route::post('/saveup/withdraw', [SaveUpController::class, 'requestWithdrawal'])->name('saveup.withdraw');

        // Menu: Microlearning
        Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
        Route::get('/learning/{id}', [LearningController::class, 'show'])->name('learning.show');
        Route::post('/learning/{id}/complete', [LearningController::class, 'complete'])->name('learning.complete');

        // Menu: Positive Quest
        Route::get('/quests', [QuestController::class, 'index'])->name('quests.index');
        Route::post('/quests/{id}/complete', [QuestController::class, 'complete'])->name('quests.complete');

        // Menu: Jurnal Harian
        Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
        Route::post('/journal', [JournalController::class, 'store'])->name('journal.store');

        // Menu: Pencapaian (Badge + Leaderboard)
        Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
    });
});
