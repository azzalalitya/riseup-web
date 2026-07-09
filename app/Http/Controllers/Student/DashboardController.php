<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\RiseUpUser;
use App\Models\DailyCheckin;
use App\Models\OnboardingBaseline;
use App\Models\SaveUpTarget;
use App\Models\SaveUpDeposit;
use App\Models\MicrolearningModule;
use App\Models\LearningProgress;
use App\Models\PositiveQuest;
use App\Models\QuestProgress;
use App\Models\SaveUpWithdrawal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = session('auth_id');

        $hasOnboarding = OnboardingBaseline::where('bas_usr_id', $userId)->exists();

        if (!$hasOnboarding) {
            return redirect()->route('onboarding.create');
        }

        $user = RiseUpUser::with(['gamification', 'profile'])
            ->where('usr_id', $userId)
            ->firstOrFail();

        $displayName = optional($user->profile)->prf_full_name
            ?: explode('@', $user->usr_email)[0];

        $checkins = DailyCheckin::where('chk_usr_id', $userId)
            ->orderByDesc('chk_date')
            ->get();

        $greenDays = $checkins
            ->where('chk_status_color', 'green')
            ->count();

        $redDays = $checkins
            ->where('chk_status_color', 'red')
            ->count();

        $onboarding = OnboardingBaseline::where('bas_usr_id', $userId)->first();

        /*Estimasi uang terselamatkan dari hari hijau*/
        $totalSaved = $greenDays * 50000;

        if ($onboarding && $onboarding->bas_est_loss_monthly) {
            $dailyLossEstimate = $onboarding->bas_est_loss_monthly / 30;
            $totalSaved = round($greenDays * $dailyLossEstimate);
        }

        /*U Save Up*/
        $saveupTarget = SaveUpTarget::where('sav_usr_id', $userId)->first();

        $saveupDeposits = SaveUpDeposit::where('dep_usr_id', $userId)
            ->orderByDesc('dep_date')
            ->orderByDesc('dep_id')
            ->get();

        // Hanya deposit yang benar-benar masuk kas yang dihitung ke progres
        // (manual atau midtrans yang sudah paid). Pending/failed diabaikan.
        $totalManualDeposit = $saveupDeposits
            ->whereIn('dep_status', ['manual', 'paid'])
            ->sum('dep_amount');

        // Status Midtrans terakhir (untuk badge di kartu SaveUp)
        $lastMidtransDeposit = $saveupDeposits
            ->where('dep_source', 'midtrans')
            ->first();

        $saveupTotalProgressAmount = $totalSaved + $totalManualDeposit;

        $saveupProgress = 0;

        if ($saveupTarget && $saveupTarget->sav_target_amount > 0) {
            $saveupProgress = min(
                100,
                round(($saveupTotalProgressAmount / $saveupTarget->sav_target_amount) * 100)
            );
        }

        // ------ Vault / Withdrawal ------
        $vaultUnlocked = $saveupTarget
            && $saveupTarget->sav_target_amount > 0
            && $saveupTotalProgressAmount >= $saveupTarget->sav_target_amount;

        $pendingWithdrawal = SaveUpWithdrawal::where('wdr_usr_id', $userId)
            ->where('wdr_status', 'pending')
            ->first();

        $lastWithdrawal = SaveUpWithdrawal::where('wdr_usr_id', $userId)
            ->orderByDesc('wdr_id')
            ->first();

        /*Kalender Progres Bulanan*/
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $monthCheckins = DailyCheckin::where('chk_usr_id', $userId)
            ->whereBetween('chk_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy('chk_date');

        $calendarDays = [];

        $firstDayPadding = $startOfMonth->dayOfWeekIso - 1;

        for ($i = 0; $i < $firstDayPadding; $i++) {
            $calendarDays[] = [
                'day' => null,
                'date' => null,
                'status' => 'empty',
                'mood' => null,
                'urge' => null,
                'trigger' => null,
            ];
        }

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateString = $date->toDateString();
            $checkin = $monthCheckins->get($dateString);

            $calendarDays[] = [
                'day' => $date->day,
                'date' => $dateString,
                'status' => $checkin ? $checkin->chk_status_color : 'none',
                'mood' => $checkin->chk_mood ?? null,
                'urge' => $checkin->chk_urge_level ?? null,
                'trigger' => $checkin->chk_trigger ?? null,
            ];
        }

        $greenThisMonth = $monthCheckins
            ->where('chk_status_color', 'green')
            ->count();

        $redThisMonth = $monthCheckins
            ->where('chk_status_color', 'red')
            ->count();

        $currentStreak = 0;

        for ($date = Carbon::today(); $date->gte($startOfMonth); $date->subDay()) {
            $dateString = $date->toDateString();
            $checkin = $monthCheckins->get($dateString);

            if (!$checkin || $checkin->chk_status_color !== 'green') {
                break;
            }

            $currentStreak++;
        }

        $monthName = $today->translatedFormat('F Y');

        /*Ringkasan Microlearning dan Positive Quest*/
        $totalLearningModules = MicrolearningModule::where('lrn_is_active', 1)->count();

        $completedLearningCount = LearningProgress::where('lrp_usr_id', $userId)
            ->count();

        $learningProgressPercent = $totalLearningModules > 0
            ? round(($completedLearningCount / $totalLearningModules) * 100)
            : 0;

        $totalQuests = PositiveQuest::where('qst_is_active', 1)->count();

        $todayQuestCount = QuestProgress::where('qrp_usr_id', $userId)
            ->whereDate('qrp_date', today())
            ->count();

        $questProgressPercent = $totalQuests > 0
            ? round(($todayQuestCount / $totalQuests) * 100)
            : 0;

        $activityMessage = 'Mulai dari satu aktivitas kecil hari ini.';

        if ($completedLearningCount > 0 && $todayQuestCount > 0) {
            $activityMessage = 'Bagus, kamu sudah belajar dan menyelesaikan quest hari ini.';
        } elseif ($completedLearningCount > 0) {
            $activityMessage = 'Materi sudah berjalan. Coba lanjutkan dengan satu Positive Quest.';
        } elseif ($todayQuestCount > 0) {
            $activityMessage = 'Quest sudah berjalan. Lengkapi juga dengan Microlearning singkat.';
        }

        return view('student.dashboard', compact(
            'displayName',
            'user',
            'checkins',
            'greenDays',
            'redDays',
            'totalSaved',
            'calendarDays',
            'greenThisMonth',
            'redThisMonth',
            'currentStreak',
            'monthName',
            'saveupTarget',
            'saveupDeposits',
            'totalManualDeposit',
            'saveupTotalProgressAmount',
            'saveupProgress',
            'lastMidtransDeposit',
            'vaultUnlocked',
            'pendingWithdrawal',
            'lastWithdrawal',
            'totalLearningModules',
            'completedLearningCount',
            'learningProgressPercent',
            'totalQuests',
            'todayQuestCount',
            'questProgressPercent',
            'activityMessage'
        ));
    }
}
