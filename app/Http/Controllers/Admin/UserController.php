<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\RiseUpUser;
use App\Models\GamificationStat;
use App\Models\DailyCheckin;
use App\Models\OnboardingBaseline;
use App\Models\SaveUpTarget;
use App\Models\SaveUpDeposit;
use App\Models\MicrolearningModule;
use App\Models\LearningProgress;
use App\Models\PositiveQuest;
use App\Models\QuestProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:120|unique:usr_user,usr_email',
            'password' => 'required|min:8',
            'status' => 'required|in:active,inactive',
        ]);

        $user = RiseUpUser::create([
            'usr_email' => $request->email,
            'usr_password_hash' => Hash::make($request->password),
            'usr_status' => $request->status,
        ]);

        GamificationStat::updateOrCreate(
            [
                'gms_usr_id' => $user->usr_id,
            ],
            [
                'gms_total_xp' => 0,
                'gms_level_num' => 1,
                'gms_weekly_xp' => 0,
                'gms_updated_at' => now(),
            ]
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show($id)
    {
        $user = RiseUpUser::with(['gamification', 'onboarding'])
            ->where('usr_id', $id)
            ->firstOrFail();

        $checkins = DailyCheckin::where('chk_usr_id', $id)
            ->orderByDesc('chk_date')
            ->get();

        $totalCheckins = $checkins->count();

        $greenDays = $checkins
            ->where('chk_status_color', 'green')
            ->count();

        $redDays = $checkins
            ->where('chk_status_color', 'red')
            ->count();

        $avgUrge = $totalCheckins > 0
            ? round($checkins->avg('chk_urge_level'), 1)
            : 0;

        $redRate = $totalCheckins > 0
            ? round(($redDays / $totalCheckins) * 100, 1)
            : 0;

        $dominantTrigger = $checkins
            ->groupBy('chk_trigger')
            ->sortByDesc(function ($items) {
                return $items->count();
            })
            ->keys()
            ->first();

        $onboarding = $user->onboarding;

        $estimatedSaved = $greenDays * 50000;

        if ($onboarding && $onboarding->bas_est_loss_monthly) {
            $dailyLossEstimate = $onboarding->bas_est_loss_monthly / 30;
            $estimatedSaved = round($greenDays * $dailyLossEstimate);
        }

        $riskLevel = 'Rendah';
        $riskClass = 'low';

        if ($redRate >= 50 || $avgUrge >= 4) {
            $riskLevel = 'Tinggi';
            $riskClass = 'high';
        } elseif ($redRate >= 25 || $avgUrge >= 3) {
            $riskLevel = 'Sedang';
            $riskClass = 'medium';
        }

        $recommendation = 'Pertahankan rutinitas check-in dan beri penguatan positif.';

        if ($riskClass === 'high') {
            $recommendation = 'User perlu perhatian lebih. Sarankan aktivitas distraksi, coping saat trigger dominan muncul, dan pantau check-in berikutnya.';
        } elseif ($riskClass === 'medium') {
            $recommendation = 'User cukup stabil namun masih perlu monitoring. Perhatikan trigger dominan dan urge level harian.';
        }

        $latestCheckins = $checkins->take(10);

        $saveupTarget = SaveUpTarget::where('sav_usr_id', $id)->first();

        $saveupDeposits = SaveUpDeposit::where('dep_usr_id', $id)
            ->orderByDesc('dep_date')
            ->orderByDesc('dep_id')
            ->take(5)
            ->get();

        $totalManualDeposit = SaveUpDeposit::where('dep_usr_id', $id)
            ->sum('dep_amount');

        $saveupTotalProgressAmount = $estimatedSaved + $totalManualDeposit;

        $saveupProgress = 0;

        if ($saveupTarget && $saveupTarget->sav_target_amount > 0) {
            $saveupProgress = min(
                100,
                round(($saveupTotalProgressAmount / $saveupTarget->sav_target_amount) * 100)
            );
        }

        $totalLearningModules = MicrolearningModule::where('lrn_is_active', 1)->count();

        $completedLearningCount = LearningProgress::where('lrp_usr_id', $id)->count();

        $learningProgressPercent = $totalLearningModules > 0
            ? round(($completedLearningCount / $totalLearningModules) * 100)
            : 0;

        $latestLearningProgress = LearningProgress::with('module')
            ->where('lrp_usr_id', $id)
            ->orderByDesc('lrp_completed_at')
            ->take(5)
            ->get();

        $totalQuests = PositiveQuest::where('qst_is_active', 1)->count();

        $todayQuestCount = QuestProgress::where('qrp_usr_id', $id)
            ->whereDate('qrp_date', today())
            ->count();

        $totalQuestCompleted = QuestProgress::where('qrp_usr_id', $id)->count();

        $questTodayPercent = $totalQuests > 0
            ? round(($todayQuestCount / $totalQuests) * 100)
            : 0;

        $latestQuestProgress = QuestProgress::with('quest')
            ->where('qrp_usr_id', $id)
            ->orderByDesc('qrp_completed_at')
            ->take(5)
            ->get();

        $engagementScore = 0;

        if ($totalCheckins >= 3) {
            $engagementScore += 30;
        }

        if ($completedLearningCount > 0) {
            $engagementScore += 25;
        }

        if ($totalQuestCompleted > 0) {
            $engagementScore += 25;
        }

        if (($user->gamification->gms_total_xp ?? 0) >= 50) {
            $engagementScore += 20;
        }

        $engagementLabel = 'Rendah';
        $engagementClass = 'low';

        if ($engagementScore >= 70) {
            $engagementLabel = 'Aktif';
            $engagementClass = 'high';
        } elseif ($engagementScore >= 40) {
            $engagementLabel = 'Cukup';
            $engagementClass = 'medium';
        }

        return view('admin.users.show', compact(
            'user',
            'onboarding',
            'checkins',
            'latestCheckins',
            'totalCheckins',
            'greenDays',
            'redDays',
            'avgUrge',
            'redRate',
            'dominantTrigger',
            'estimatedSaved',
            'riskLevel',
            'riskClass',
            'recommendation',
            'saveupTarget',
            'saveupDeposits',
            'totalManualDeposit',
            'saveupTotalProgressAmount',
            'saveupProgress',
            'totalLearningModules',
            'completedLearningCount',
            'learningProgressPercent',
            'latestLearningProgress',
            'totalQuests',
            'todayQuestCount',
            'totalQuestCompleted',
            'questTodayPercent',
            'latestQuestProgress',
            'engagementScore',
            'engagementLabel',
            'engagementClass'
        ));
    }

    public function edit($id)
    {
        $user = RiseUpUser::where('usr_id', $id)->firstOrFail();

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = RiseUpUser::where('usr_id', $id)->firstOrFail();

        $request->validate([
            'email' => 'required|email|max:120|unique:usr_user,usr_email,' . $user->usr_id . ',usr_id',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|min:8',
        ]);

        $user->usr_email = $request->email;
        $user->usr_status = $request->status;

        if ($request->filled('password')) {
            $user->usr_password_hash = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = RiseUpUser::where('usr_id', $id)->firstOrFail();
        $user->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User berhasil dihapus.');
    }
}