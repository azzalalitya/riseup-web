<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\DailyCheckin;
use App\Models\GamificationStat;
use App\Models\LearningProgress;
use App\Models\QuestProgress;
use App\Models\Journal;

class BadgeService
{
    /**
     * Hitung metrik user lalu berikan badge yang syaratnya sudah terpenuhi
     * tapi belum dimiliki. Hanya menambah (tidak pernah mencabut).
     *
     * @return array kode badge yang BARU diperoleh pada pemanggilan ini
     */
    public function sync(int $userId): array
    {
        $metrics = $this->metrics($userId);

        $badges = Badge::where('bdg_is_active', 1)->get();

        $ownedBadgeIds = UserBadge::where('ubd_usr_id', $userId)
            ->pluck('ubd_bdg_id')
            ->toArray();

        $newlyEarned = [];

        foreach ($badges as $badge) {
            if (in_array($badge->bdg_id, $ownedBadgeIds)) {
                continue;
            }

            $value = $metrics[$badge->bdg_condition_type] ?? 0;

            if ($value >= $badge->bdg_condition_value) {
                UserBadge::create([
                    'ubd_usr_id'    => $userId,
                    'ubd_bdg_id'    => $badge->bdg_id,
                    'ubd_earned_at' => now(),
                ]);

                $newlyEarned[] = $badge->bdg_code;
            }
        }

        return $newlyEarned;
    }

    /**
     * Sync badge lalu kembalikan pesan notifikasi:
     * - Kalau ada badge baru: "🏅 Badge baru: <nama>!"
     * - Kalau belum: progres menuju badge terdekat, mis. "🏅 Pembelajar: 2/3"
     */
    public function syncWithMessage(int $userId): ?string
    {
        $newCodes = $this->sync($userId);

        if (!empty($newCodes)) {
            $names = Badge::whereIn('bdg_code', $newCodes)->pluck('bdg_name')->implode(', ');
            return '🏅 Badge baru: ' . $names . '!';
        }

        // Cari badge belum dimiliki yang progresnya paling dekat selesai
        $metrics = $this->metrics($userId);

        $ownedIds = UserBadge::where('ubd_usr_id', $userId)->pluck('ubd_bdg_id')->toArray();

        $closest = null;
        $closestRatio = -1;

        foreach (Badge::where('bdg_is_active', 1)->get() as $badge) {
            if (in_array($badge->bdg_id, $ownedIds)) continue;
            if ($badge->bdg_condition_value <= 0) continue;

            $value = min($metrics[$badge->bdg_condition_type] ?? 0, $badge->bdg_condition_value);
            $ratio = $value / $badge->bdg_condition_value;

            if ($ratio > $closestRatio && $value > 0) {
                $closestRatio = $ratio;
                $closest = [$badge, $value];
            }
        }

        if ($closest) {
            [$badge, $value] = $closest;
            return '🏅 ' . $badge->bdg_name . ': ' . $value . '/' . $badge->bdg_condition_value;
        }

        return null;
    }

    /**
     * Kumpulan metrik yang dipakai untuk syarat badge.
     */
    public function metrics(int $userId): array
    {
        $stat = GamificationStat::where('gms_usr_id', $userId)->first();

        $checkins = DailyCheckin::where('chk_usr_id', $userId)
            ->orderBy('chk_date')
            ->get();

        return [
            'total_xp'      => $stat->gms_total_xp ?? 0,
            'green_streak'  => $this->longestGreenStreak($checkins),
            'checkin_count' => $checkins->count(),
            'learning_done' => LearningProgress::where('lrp_usr_id', $userId)->count(),
            'quest_done'    => QuestProgress::where('qrp_usr_id', $userId)->count(),
            'journal_count' => Journal::where('jrn_usr_id', $userId)->count(),
        ];
    }

    /**
     * Rangkaian hari hijau terpanjang (berdasarkan tanggal berurutan).
     */
    private function longestGreenStreak($checkins): int
    {
        $longest = 0;
        $current = 0;
        $prevDate = null;

        foreach ($checkins as $c) {
            if ($c->chk_status_color !== 'green') {
                $current = 0;
                $prevDate = $c->chk_date;
                continue;
            }

            $date = \Carbon\Carbon::parse($c->chk_date);

            if ($prevDate !== null && \Carbon\Carbon::parse($prevDate)->copy()->addDay()->isSameDay($date)) {
                $current++;
            } else {
                $current = 1;
            }

            $longest = max($longest, $current);
            $prevDate = $c->chk_date;
        }

        return $longest;
    }
}
