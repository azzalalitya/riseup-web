<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\MicrolearningModule;
use App\Models\LearningProgress;
use App\Models\GamificationStat;

class LearningController extends Controller
{
    public function index()
    {
        $userId = session('auth_id');

        $modules = MicrolearningModule::where('lrn_is_active', 1)
            ->orderBy('lrn_day_number')
            ->get();

        $completedModuleIds = LearningProgress::where('lrp_usr_id', $userId)
            ->pluck('lrp_lrn_id')
            ->toArray();

        $completedCount = count($completedModuleIds);
        $totalModules = $modules->count();

        $progressPercent = $totalModules > 0
            ? round(($completedCount / $totalModules) * 100)
            : 0;

        return view('student.learning.index', compact(
            'modules',
            'completedModuleIds',
            'completedCount',
            'totalModules',
            'progressPercent'
        ));
    }

    public function show($id)
    {
        $userId = session('auth_id');

        $module = MicrolearningModule::where('lrn_is_active', 1)
            ->where('lrn_id', $id)
            ->firstOrFail();

        $isCompleted = LearningProgress::where('lrp_usr_id', $userId)
            ->where('lrp_lrn_id', $id)
            ->exists();

        return view('student.learning.show', compact(
            'module',
            'isCompleted'
        ));
    }

    public function complete($id)
    {
        $userId = session('auth_id');

        $module = MicrolearningModule::where('lrn_is_active', 1)
            ->where('lrn_id', $id)
            ->firstOrFail();

        $alreadyCompleted = LearningProgress::where('lrp_usr_id', $userId)
            ->where('lrp_lrn_id', $module->lrn_id)
            ->exists();

        if (!$alreadyCompleted) {
            LearningProgress::create([
                'lrp_usr_id' => $userId,
                'lrp_lrn_id' => $module->lrn_id,
                'lrp_status' => 'completed',
                'lrp_completed_at' => now(),
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

            $newTotalXp = $stat->gms_total_xp + $module->lrn_xp_reward;
            $newLevel = floor($newTotalXp / 100) + 1;

            $stat->update([
                'gms_total_xp' => $newTotalXp,
                'gms_level_num' => $newLevel,
                'gms_weekly_xp' => $stat->gms_weekly_xp + $module->lrn_xp_reward,
                'gms_updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('student.learning.index')
            ->with('success', 'Materi berhasil diselesaikan. XP bertambah +' . $module->lrn_xp_reward . (($bm = app(\App\Services\BadgeService::class)->syncWithMessage(session('auth_id'))) ? ' ' . $bm : ''));
    }
}