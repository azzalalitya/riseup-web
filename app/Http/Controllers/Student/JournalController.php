<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\DailyCheckin;
use App\Models\UserProfile;
use App\Models\ReligiousContent;
use App\Models\GamificationStat;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Pertanyaan reflektif dipilih berdasarkan mood check-in terakhir.
     */
    private array $promptMap = [
        'sedih'  => 'Apa yang sedang kamu rasakan, dan satu hal kecil apa yang bisa membuatmu sedikit lebih tenang hari ini?',
        'cemas'  => 'Kekhawatiran apa yang paling terasa hari ini? Mana yang masih ada dalam kendalimu untuk dihadapi?',
        'stres'  => 'Apa pemicu tekanan terbesarmu hari ini, dan cara sehat apa yang bisa kamu coba untuk meredakannya?',
        'netral' => 'Apa satu keputusan baik yang ingin kamu pertahankan sampai besok?',
        'baik'   => 'Apa yang membuat hari ini terasa baik, dan bagaimana kamu bisa mengulanginya besok?',
    ];

    private string $defaultPrompt = 'Apa satu hal yang kamu syukuri dari dirimu hari ini?';

    public function index()
    {
        $userId = session('auth_id');
        $today = today()->toDateString();

        // mood check-in terakhir -> prompt
        $lastCheckin = DailyCheckin::where('chk_usr_id', $userId)
            ->orderByDesc('chk_date')
            ->first();

        $mood = $lastCheckin->chk_mood ?? null;
        $prompt = $this->promptMap[$mood] ?? $this->defaultPrompt;

        // jurnal hari ini (kalau sudah diisi)
        $todayJournal = Journal::where('jrn_usr_id', $userId)
            ->where('jrn_date', $today)
            ->first();

        // preferensi agama -> refleksi religius (fallback ke 'umum')
        $profile = UserProfile::where('prf_usr_id', $userId)->first();
        $religion = $profile->prf_religion_pref ?? 'umum';

        $reflection = ReligiousContent::where('rel_is_active', 1)
            ->whereIn('rel_religion_pref', [$religion, 'umum'])
            ->inRandomOrder()
            ->first();

        // riwayat 7 jurnal terakhir
        $recentJournals = Journal::where('jrn_usr_id', $userId)
            ->orderByDesc('jrn_date')
            ->take(7)
            ->get();

        return view('student.journal.index', compact(
            'prompt',
            'mood',
            'todayJournal',
            'reflection',
            'religion',
            'recentJournals'
        ));
    }

    public function store(Request $request)
    {
        $userId = session('auth_id');
        $today = today()->toDateString();

        $request->validate([
            'answer_text' => 'required|max:1000',
            'prompt'      => 'nullable|max:255',
        ]);

        $lastCheckin = DailyCheckin::where('chk_usr_id', $userId)
            ->orderByDesc('chk_date')
            ->first();

        $alreadyToday = Journal::where('jrn_usr_id', $userId)
            ->where('jrn_date', $today)
            ->exists();

        Journal::updateOrCreate(
            [
                'jrn_usr_id' => $userId,
                'jrn_date'   => $today,
            ],
            [
                'jrn_prompt'      => $request->prompt,
                'jrn_answer_text' => $request->answer_text,
                'jrn_mood_ref'    => $lastCheckin->chk_mood ?? null,
                'jrn_created_at'  => now(),
            ]
        );

        // XP hanya diberikan sekali per hari (saat pertama mengisi)
        $message = 'Jurnal hari ini berhasil diperbarui.';

        if (!$alreadyToday) {
            $stat = GamificationStat::firstOrCreate(
                ['gms_usr_id' => $userId],
                [
                    'gms_total_xp'  => 0,
                    'gms_level_num' => 1,
                    'gms_weekly_xp' => 0,
                    'gms_updated_at' => now(),
                ]
            );

            $xpAdd = 15;
            $newTotalXp = $stat->gms_total_xp + $xpAdd;

            $stat->update([
                'gms_total_xp'  => $newTotalXp,
                'gms_level_num' => floor($newTotalXp / 100) + 1,
                'gms_weekly_xp' => $stat->gms_weekly_xp + $xpAdd,
                'gms_updated_at' => now(),
            ]);

            $message = 'Jurnal hari ini tersimpan. XP bertambah +' . $xpAdd;
        }

        $badgeMsg = app(BadgeService::class)->syncWithMessage($userId);

        return redirect()
            ->route('student.journal.index')
            ->with('success', $message . ($badgeMsg ? ' ' . $badgeMsg : ''));
    }
}
