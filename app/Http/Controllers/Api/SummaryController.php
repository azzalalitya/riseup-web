<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\RiseUpUser;
use App\Models\DailyCheckin;
use App\Models\GamificationStat;
use App\Models\OnboardingBaseline;

class SummaryController extends Controller
{
    public function studentSummary()
    {
        if (session('auth_role') !== 'user') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $userId = session('auth_id');

        $checkins = DailyCheckin::where('chk_usr_id', $userId)->get();

        $greenDays = $checkins->where('chk_status_color', 'green')->count();
        $redDays = $checkins->where('chk_status_color', 'red')->count();
        $totalSaved = $greenDays * 50000;

        $stat = GamificationStat::where('gms_usr_id', $userId)->first();

        return response()->json([
            'green_days' => $greenDays,
            'red_days' => $redDays,
            'total_saved' => $totalSaved,
            'total_xp' => $stat->gms_total_xp ?? 0,
            'level' => $stat->gms_level_num ?? 1,
        ]);
    }

    public function adminSummary()
    {
        if (session('auth_role') !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $totalUsers = RiseUpUser::count();

        $activeUsers = RiseUpUser::where('usr_status', 'active')->count();

        $inactiveUsers = RiseUpUser::where('usr_status', 'inactive')->count();

        $onboardedUsers = OnboardingBaseline::count();

        $todayCheckins = DailyCheckin::whereDate('chk_date', today())->count();

        $redCheckinsThisWeek = DailyCheckin::where('chk_status_color', 'red')
            ->whereDate('chk_date', '>=', now()->subDays(7))
            ->count();

        $greenCheckinsThisWeek = DailyCheckin::where('chk_status_color', 'green')
            ->whereDate('chk_date', '>=', now()->subDays(7))
            ->count();

        $totalXp = GamificationStat::sum('gms_total_xp');

        return response()->json([
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'onboarded_users' => $onboardedUsers,
            'today_checkins' => $todayCheckins,
            'green_checkins_this_week' => $greenCheckinsThisWeek,
            'red_checkins_this_week' => $redCheckinsThisWeek,
            'total_xp' => $totalXp,
        ]);
    }
}