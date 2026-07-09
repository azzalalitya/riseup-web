<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiseUpUser;
use App\Models\DailyCheckin;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $users = RiseUpUser::with('gamification')->get();

        $totalUsers = $users->count();

        $todayCheckins = DailyCheckin::whereDate('chk_date', Carbon::today()->toDateString())->count();

        // User "perlu perhatian": punya check-in merah dalam 7 hari terakhir
        $attentionIds = DailyCheckin::where('chk_status_color', 'red')
            ->whereDate('chk_date', '>=', now()->subDays(7)->toDateString())
            ->distinct()
            ->pluck('chk_usr_id')
            ->toArray();

        $attentionUsers = count($attentionIds);

        return view('admin.dashboard', compact(
            'users',
            'totalUsers',
            'todayCheckins',
            'attentionUsers',
            'attentionIds'
        ));
    }
}
