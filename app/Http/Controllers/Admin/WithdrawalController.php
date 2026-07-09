<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaveUpWithdrawal;
use App\Models\RiseUpUser;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = SaveUpWithdrawal::orderByDesc('wdr_id')->get();

        $emails = RiseUpUser::pluck('usr_email', 'usr_id');
        $names  = UserProfile::pluck('prf_full_name', 'prf_usr_id');

        $rows = $withdrawals->map(function ($w) use ($emails, $names) {
            return [
                'id'      => $w->wdr_id,
                'user'    => $names[$w->wdr_usr_id] ?? explode('@', $emails[$w->wdr_usr_id] ?? 'User')[0],
                'email'   => $emails[$w->wdr_usr_id] ?? '-',
                'amount'  => $w->wdr_amount,
                'reason'  => $w->wdr_reason,
                'status'  => $w->wdr_status,
                'admin_note' => $w->wdr_admin_note,
                'created_at'   => $w->wdr_created_at,
                'processed_at' => $w->wdr_processed_at,
            ];
        });

        return view('admin.withdrawals.index', ['rows' => $rows]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'decision'   => 'required|in:approved,rejected',
            'admin_note' => 'nullable|max:255',
        ]);

        $wdr = SaveUpWithdrawal::findOrFail($id);

        if ($wdr->wdr_status !== 'pending') {
            return redirect()
                ->route('admin.withdrawals.index')
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $wdr->update([
            'wdr_status'       => $request->decision,
            'wdr_admin_note'   => $request->admin_note,
            'wdr_processed_at' => now(),
        ]);

        return redirect()
            ->route('admin.withdrawals.index')
            ->with('success', 'Keputusan pengajuan disimpan.');
    }
}
