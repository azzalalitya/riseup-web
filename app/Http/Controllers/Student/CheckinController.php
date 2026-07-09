<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\DailyCheckin;
use App\Models\GamificationStat;
use App\Services\BadgeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckinController extends Controller
{

    /**
     * Halaman Check-in: form + kalender bulanan + riwayat.
     */
    public function index()
    {
        $userId = session('auth_id');

        $checkins = DailyCheckin::where('chk_usr_id', $userId)
            ->orderByDesc('chk_date')
            ->get();

        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $monthCheckins = DailyCheckin::where('chk_usr_id', $userId)
            ->whereBetween('chk_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy('chk_date');

        $calendarDays = [];
        $firstDayPadding = $startOfMonth->dayOfWeekIso - 1;

        for ($i = 0; $i < $firstDayPadding; $i++) {
            $calendarDays[] = ['day' => null, 'date' => null, 'status' => 'empty', 'mood' => null, 'urge' => null, 'trigger' => null];
        }

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $d = $date->toDateString();
            $c = $monthCheckins->get($d);
            $calendarDays[] = [
                'day' => $date->day, 'date' => $d,
                'status' => $c ? $c->chk_status_color : 'none',
                'mood' => $c->chk_mood ?? null, 'urge' => $c->chk_urge_level ?? null, 'trigger' => $c->chk_trigger ?? null,
            ];
        }

        $greenThisMonth = $monthCheckins->where('chk_status_color', 'green')->count();
        $redThisMonth = $monthCheckins->where('chk_status_color', 'red')->count();

        $currentStreak = 0;
        for ($date = Carbon::today(); $date->gte($startOfMonth); $date->subDay()) {
            $c = $monthCheckins->get($date->toDateString());
            if (!$c || $c->chk_status_color !== 'green') break;
            $currentStreak++;
        }

        $monthName = $today->translatedFormat('F Y');

        return view('student.checkin.index', compact(
            'checkins', 'calendarDays', 'greenThisMonth', 'redThisMonth', 'currentStreak', 'monthName'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:baik,netral,sedih,cemas,stres',
            'urge_level' => 'required|integer|min:0|max:5',
            'trigger' => 'required|max:20',
            'status_color' => 'required|in:green,red',
            'relapse_reason' => 'nullable|max:20',
            'note_text' => 'nullable|max:255',
        ]);

        if ($request->status_color === 'red' && !$request->filled('relapse_reason')) {
            return back()
                ->withErrors([
                    'relapse_reason' => 'Alasan relapse wajib diisi kalau status hari ini Relapse.',
                ])
                ->withInput();
        }

        $relapseReason = null;

        if ($request->status_color === 'red') {
            $relapseReason = $request->relapse_reason;
        }

        $userId = session('auth_id');

        DailyCheckin::updateOrCreate(
            [
                'chk_usr_id' => $userId,
                'chk_date' => today()->toDateString(),
            ],
            [
                'chk_mood' => $request->mood,
                'chk_urge_level' => $request->urge_level,
                'chk_trigger' => $request->trigger,
                'chk_status_color' => $request->status_color,
                'chk_relapse_reason' => $relapseReason,
                'chk_note_text' => $request->note_text,
                'chk_created_at' => now(),
            ]
        );

        $xpAdd = $request->status_color === 'green' ? 20 : 5;

        $stat = GamificationStat::where('gms_usr_id', $userId)->first();

        if ($stat) {
            $stat->gms_total_xp += $xpAdd;
            $stat->gms_weekly_xp = ($stat->gms_weekly_xp ?? 0) + $xpAdd;
            $stat->gms_level_num = max(1, floor($stat->gms_total_xp / 100) + 1);
            $stat->gms_updated_at = now();
            $stat->save();
        }

        $badgeMsg = app(BadgeService::class)->syncWithMessage(session('auth_id'));

        return redirect()
            ->route('student.checkin.index')
            ->with('success', 'Check-in berhasil disimpan. XP bertambah +' . $xpAdd . ($badgeMsg ? ' ' . $badgeMsg : ''));
    }
}