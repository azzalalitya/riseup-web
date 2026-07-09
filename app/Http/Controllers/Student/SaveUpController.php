<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SaveUpTarget;
use App\Models\SaveUpDeposit;
use App\Models\SaveUpWithdrawal;
use App\Models\RiseUpUser;
use App\Models\UserProfile;
use App\Models\OnboardingBaseline;
use App\Models\DailyCheckin;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class SaveUpController extends Controller
{
    /* ==============================================================
       HALAMAN U SAVE UP (index)
       ============================================================== */
    public function index()
    {
        $userId = session('auth_id');

        $saveupTarget = SaveUpTarget::where('sav_usr_id', $userId)->first();

        $saveupDeposits = SaveUpDeposit::where('dep_usr_id', $userId)
            ->orderByDesc('dep_date')
            ->orderByDesc('dep_id')
            ->get();

        $totalManualDeposit = $saveupDeposits
            ->whereIn('dep_status', ['manual', 'paid'])
            ->sum('dep_amount');

        $lastMidtransDeposit = $saveupDeposits->where('dep_source', 'midtrans')->first();

        // Estimasi uang terselamatkan dari hari hijau
        $onboarding = OnboardingBaseline::where('bas_usr_id', $userId)->first();
        $greenDays = DailyCheckin::where('chk_usr_id', $userId)
            ->where('chk_status_color', 'green')
            ->count();

        $totalSaved = $greenDays * 50000;
        if ($onboarding && $onboarding->bas_est_loss_monthly) {
            $totalSaved = round($greenDays * ($onboarding->bas_est_loss_monthly / 30));
        }

        $saveupTotalProgressAmount = $totalSaved + $totalManualDeposit;

        $saveupProgress = 0;
        if ($saveupTarget && $saveupTarget->sav_target_amount > 0) {
            $saveupProgress = min(100, round(($saveupTotalProgressAmount / $saveupTarget->sav_target_amount) * 100));
        }

        $vaultUnlocked = $saveupTarget
            && $saveupTarget->sav_target_amount > 0
            && $saveupTotalProgressAmount >= $saveupTarget->sav_target_amount;

        $pendingWithdrawal = SaveUpWithdrawal::where('wdr_usr_id', $userId)
            ->where('wdr_status', 'pending')->first();

        $lastWithdrawal = SaveUpWithdrawal::where('wdr_usr_id', $userId)
            ->orderByDesc('wdr_id')->first();

        return view('student.saveup.index', compact(
            'saveupTarget', 'saveupDeposits', 'totalManualDeposit', 'totalSaved',
            'saveupTotalProgressAmount', 'saveupProgress', 'lastMidtransDeposit',
            'vaultUnlocked', 'pendingWithdrawal', 'lastWithdrawal'
        ));
    }

    /* ==============================================================
       TARGET
       ============================================================== */
    public function storeTarget(Request $request)
    {
        $request->validate([
            'target_name'   => 'required|max:100',
            'target_amount' => 'required|numeric|min:1000|max:9999999999',
        ]);

        SaveUpTarget::updateOrCreate(
            ['sav_usr_id' => session('auth_id')],
            [
                'sav_target_name'   => $request->target_name,
                'sav_target_amount' => $request->target_amount,
                'sav_created_at'    => now(),
            ]
        );

        return redirect()
            ->route('student.saveup.index')
            ->with('success', 'Target U Save Up berhasil disimpan.');
    }

    public function destroyDeposit($id)
    {
        $deposit = SaveUpDeposit::where('dep_usr_id', session('auth_id'))
            ->where('dep_id', $id)
            ->firstOrFail();

        // Yang sudah dibayar via Midtrans tidak boleh dihapus manual
        // (jaga integritas vault).
        if ($deposit->dep_source === 'midtrans' && $deposit->dep_status === 'paid') {
            return redirect()
                ->route('student.saveup.index')
                ->with('error', 'Setoran Midtrans yang sudah lunas tidak dapat dihapus.');
        }

        $deposit->delete();

        return redirect()
            ->route('student.saveup.index')
            ->with('success', 'Setoran berhasil dihapus.');
    }

    /* ==============================================================
       MIDTRANS SNAP — buat token untuk 1 setoran
       (dipanggil via AJAX oleh tombol "Setor via Midtrans")
       ============================================================== */
    public function createSnapToken(Request $request, MidtransService $midtrans)
    {
        $request->validate([
            'amount' => 'required|integer|min:10000|max:9999999999',
            'note'   => 'nullable|max:255',
        ]);

        $userId = session('auth_id');
        $user    = RiseUpUser::where('usr_id', $userId)->firstOrFail();
        $profile = UserProfile::where('prf_usr_id', $userId)->first();

        // order_id unik: RSU-<userId>-<timestamp>-<rand>
        $orderId = 'RSU-' . $userId . '-' . time() . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Catat deposit dulu dengan status 'pending', supaya webhook
        // tinggal update status berdasarkan order_id yang sama.
        SaveUpDeposit::create([
            'dep_usr_id'     => $userId,
            'dep_amount'     => $request->amount,
            'dep_note'       => $request->note,
            'dep_date'       => today(),
            'dep_status'     => 'pending',
            'dep_source'     => 'midtrans',
            'dep_order_id'   => $orderId,
            'dep_created_at' => now(),
        ]);

        try {
            $token = $midtrans->createSaveUpSnapToken(
                $orderId,
                (int) $request->amount,
                [
                    'name'  => $profile->prf_full_name ?? explode('@', $user->usr_email)[0],
                    'email' => $user->usr_email,
                ],
                'Setoran U Save Up RiseUp'
            );
        } catch (\Throwable $e) {
            // Kalau gagal buat token, hapus deposit pending itu supaya nggak nyampah.
            SaveUpDeposit::where('dep_order_id', $orderId)->delete();

            return response()->json([
                'error' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'snap_token' => $token,
            'order_id'   => $orderId,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /* ==============================================================
       MIDTRANS WEBHOOK — dipanggil oleh Midtrans, bukan user
       Route: POST /midtrans/callback  (di-exclude dari CSRF)
       ============================================================== */
    public function midtransCallback(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        if (!$midtrans->verifySignature($payload)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;

        $deposit = SaveUpDeposit::where('dep_order_id', $orderId)->first();

        if (!$deposit) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $newStatus = $midtrans->mapStatus($payload);

        $deposit->dep_status       = $newStatus;
        $deposit->dep_payment_type = $payload['payment_type'] ?? null;

        if ($newStatus === 'paid') {
            $deposit->dep_paid_at = now();
        }

        $deposit->save();

        return response()->json(['message' => 'ok']);
    }

    /* ==============================================================
       PENARIKAN DANA (VAULT) — hanya boleh kalau progres >= 100%
       ============================================================== */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'withdraw_reason' => 'nullable|max:255',
        ]);

        $userId = session('auth_id');

        // Cek target
        $target = SaveUpTarget::where('sav_usr_id', $userId)->first();
        if (!$target || !$target->sav_target_amount) {
            return redirect()
                ->route('student.saveup.index')
                ->with('error', 'Kamu belum memiliki target U Save Up.');
        }

        // Hitung total progres (paid + manual + estimasi hijau)
        $totalProgress = $this->totalProgressAmount($userId);

        if ($totalProgress < $target->sav_target_amount) {
            $sisa = $target->sav_target_amount - $totalProgress;
            return redirect()
                ->route('student.saveup.index')
                ->with('error', 'Belum bisa tarik dana. Target belum tercapai (kurang Rp ' . number_format($sisa, 0, ',', '.') . ').');
        }

        // Cegah pengajuan ganda yang masih pending
        $pendingExists = SaveUpWithdrawal::where('wdr_usr_id', $userId)
            ->where('wdr_status', 'pending')
            ->exists();

        if ($pendingExists) {
            return redirect()
                ->route('student.saveup.index')
                ->with('error', 'Sudah ada pengajuan penarikan yang menunggu persetujuan admin.');
        }

        SaveUpWithdrawal::create([
            'wdr_usr_id'    => $userId,
            'wdr_amount'    => $totalProgress,
            'wdr_reason'    => $request->withdraw_reason,
            'wdr_status'    => 'pending',
            'wdr_created_at'=> now(),
        ]);

        return redirect()
            ->route('student.saveup.index')
            ->with('success', 'Pengajuan penarikan dana terkirim. Menunggu persetujuan admin.');
    }

    /* ==============================================================
       Helper: total progres (setoran paid+manual + estimasi hari hijau)
       ============================================================== */
    private function totalProgressAmount(int $userId): float
    {
        // Setoran yang dihitung ke progres: manual + midtrans yang sudah PAID
        $manualPaid = SaveUpDeposit::where('dep_usr_id', $userId)
            ->whereIn('dep_status', ['manual', 'paid'])
            ->sum('dep_amount');

        // Estimasi uang terselamatkan (sama seperti Dashboard)
        $onboarding = OnboardingBaseline::where('bas_usr_id', $userId)->first();
        $greenDays = DailyCheckin::where('chk_usr_id', $userId)
            ->where('chk_status_color', 'green')
            ->count();

        $estimasi = $greenDays * 50000;
        if ($onboarding && $onboarding->bas_est_loss_monthly) {
            $daily = $onboarding->bas_est_loss_monthly / 30;
            $estimasi = round($greenDays * $daily);
        }

        return $manualPaid + $estimasi;
    }
}
