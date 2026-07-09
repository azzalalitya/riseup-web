<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\PositiveQuest;
use App\Models\QuestProgress;
use App\Models\GamificationStat;

class QuestController extends Controller
{
    public function index()
    {
        $userId = session('auth_id');
        $today = today()->toDateString();

        $quests = PositiveQuest::where('qst_is_active', 1)
            ->orderBy('qst_category')
            ->orderBy('qst_id')
            ->get();

        $completedQuestIds = QuestProgress::where('qrp_usr_id', $userId)
            ->where('qrp_date', $today)
            ->pluck('qrp_qst_id')
            ->toArray();

        $completedToday = count($completedQuestIds);
        $totalQuests = $quests->count();

        $progressPercent = $totalQuests > 0
            ? round(($completedToday / $totalQuests) * 100)
            : 0;

        return view('student.quests.index', compact(
            'quests',
            'completedQuestIds',
            'completedToday',
            'totalQuests',
            'progressPercent'
        ));
    }

    public function complete($id)
    {
        $userId = session('auth_id');
        $today = today()->toDateString();

        $quest = PositiveQuest::where('qst_is_active', 1)
            ->where('qst_id', $id)
            ->firstOrFail();

        $alreadyCompleted = QuestProgress::where('qrp_usr_id', $userId)
            ->where('qrp_qst_id', $quest->qst_id)
            ->where('qrp_date', $today)
            ->exists();

        if (!$alreadyCompleted) {
            QuestProgress::create([
                'qrp_usr_id' => $userId,
                'qrp_qst_id' => $quest->qst_id,
                'qrp_date' => $today,
                'qrp_status' => 'completed',
                'qrp_completed_at' => now(),
            ]);

            $stat = GamificationStat::firstOrCreate(
                [
                    'gms_usr_id' => $userId,
                ],
                [
                    'gms_total_xp' => 0,
                    'gms_level_num' => 1,
                    'gms_weekly_xp' => 0,
                    'gms_updated_at' => now(),
                ]
            );

            $newTotalXp = $stat->gms_total_xp + $quest->qst_xp_reward;
            $newLevel = floor($newTotalXp / 100) + 1;

            $stat->update([
                'gms_total_xp' => $newTotalXp,
                'gms_level_num' => $newLevel,
                'gms_weekly_xp' => $stat->gms_weekly_xp + $quest->qst_xp_reward,
                'gms_updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('student.quests.index')
            ->with('success', 'Quest selesai. XP bertambah +' . $quest->qst_xp_reward . (($bm = app(\App\Services\BadgeService::class)->syncWithMessage(session('auth_id'))) ? ' ' . $bm : ''));
    }
}