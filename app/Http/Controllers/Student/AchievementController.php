<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\GamificationStat;
use App\Models\RiseUpUser;
use App\Models\UserProfile;
use App\Models\Admin;
use App\Services\BadgeService;

class AchievementController extends Controller
{
    public function index(BadgeService $badgeService)
    {
        $userId = session('auth_id');

        // Award badge yang sudah layak (lazy sync)
        $newlyEarned = $badgeService->sync($userId);
        $metrics = $badgeService->metrics($userId);

        // Semua badge + status kepemilikan
        $allBadges = Badge::where('bdg_is_active', 1)
            ->orderBy('bdg_id')
            ->get();

        $ownedMap = UserBadge::where('ubd_usr_id', $userId)
            ->get()
            ->keyBy('ubd_bdg_id');

        $badges = $allBadges->map(function ($b) use ($ownedMap, $metrics) {
            $owned = $ownedMap->has($b->bdg_id);
            $progressValue = $metrics[$b->bdg_condition_type] ?? 0;
            $percent = $b->bdg_condition_value > 0
                ? min(100, round(($progressValue / $b->bdg_condition_value) * 100))
                : 0;

            return [
                'name'        => $b->bdg_name,
                'description' => $b->bdg_description,
                'icon'        => $b->bdg_icon,
                'owned'       => $owned,
                'earned_at'   => $owned ? $ownedMap[$b->bdg_id]->ubd_earned_at : null,
                'progress'    => $progressValue,
                'target'      => $b->bdg_condition_value,
                'percent'     => $percent,
            ];
        });

        $earnedCount = $badges->where('owned', true)->count();
        $totalBadges = $badges->count();

        // ---------- Leaderboard mingguan ----------
        // Ambil email admin supaya bisa di-exclude dari leaderboard
        $adminEmails = Admin::pluck('adm_email')->toArray();
        $adminUserIds = \App\Models\RiseUpUser::whereIn('usr_email', $adminEmails)
            ->pluck('usr_id')->toArray();

        $stats = GamificationStat::orderByDesc('gms_weekly_xp')
            ->orderByDesc('gms_total_xp')
            ->whereNotIn('gms_usr_id', $adminUserIds)
            ->get();

        $profiles = UserProfile::pluck('prf_full_name', 'prf_usr_id');
        $emails = RiseUpUser::pluck('usr_email', 'usr_id');

        $rank = 0;
        $leaderboard = $stats->map(function ($s) use (&$rank, $profiles, $emails, $userId) {
            $rank++;
            $name = $profiles[$s->gms_usr_id]
                ?? \Illuminate\Support\Str::before($emails[$s->gms_usr_id] ?? 'User', '@');

            return [
                'rank'      => $rank,
                'name'      => $name,
                'level'     => $s->gms_level_num,
                'weekly_xp' => $s->gms_weekly_xp,
                'total_xp'  => $s->gms_total_xp,
                'is_me'     => $s->gms_usr_id == $userId,
            ];
        });

        $myRankRow = $leaderboard->firstWhere('is_me', true);
        $topLeaderboard = $leaderboard->take(10);

        return view('student.achievements.index', compact(
            'badges',
            'earnedCount',
            'totalBadges',
            'newlyEarned',
            'topLeaderboard',
            'myRankRow'
        ));
    }
}
