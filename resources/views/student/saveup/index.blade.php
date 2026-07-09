<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Student RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

<div class="page riseup-shell">
    @include('student.partials.navbar')

    <section class="riseup-hero">
        <div>
            <p class="hero-eyebrow">U Save Up</p>
            <h1>Kunci uangmu untuk masa depan.</h1>
            <p class="hero-sub">Tentukan target, setor rutin, dan dana terkunci sampai targetmu tercapai.</p>
        </div>
    </section>

    <section class="card saveup-card">
        <div class="section-head">
            <div>
                <h2>U Save Up</h2>
                <p>Gabungan estimasi uang terselamatkan dan setoran tabungan manual.</p>
            </div>

            <span class="api-badge">Financial Tracker</span>
        </div>

        <div class="saveup-layout">
            <div class="saveup-main">
                <p class="saveup-label">Total progress U Save Up</p>
                <h3>Rp {{ number_format($saveupTotalProgressAmount, 0, ',', '.') }}</h3>

                <div class="saveup-breakdown">
                    <div>
                        <span>Estimasi dari hari hijau</span>
                        <strong>Rp {{ number_format($totalSaved, 0, ',', '.') }}</strong>
                    </div>

                    <div>
                        <span>Setoran via Midtrans</span>
                        <strong>Rp {{ number_format($totalManualDeposit, 0, ',', '.') }}</strong>
                    </div>
                </div>

                @if ($saveupTarget)
                    <p class="saveup-target">
                        Target: <strong>{{ $saveupTarget->sav_target_name }}</strong>
                        sebesar
                        <strong>Rp {{ number_format($saveupTarget->sav_target_amount, 0, ',', '.') }}</strong>
                    </p>

                    <div class="saveup-progress">
                        <span style="width: {{ $saveupProgress }}%"></span>
                    </div>

                    <p class="saveup-percent">{{ $saveupProgress }}% tercapai</p>

                    @if ($saveupProgress >= 100)
                        <div class="saveup-message success-message">
                            Target tercapai. Kamu sudah berhasil mencapai target tabungan ini.
                        </div>
                    @elseif ($saveupProgress >= 50)
                        <div class="saveup-message">
                            Sudah lebih dari setengah jalan. Lanjutkan setoran dan hari hijau.
                        </div>
                    @else
                        <div class="saveup-message">
                            Mulai dari nominal kecil. Setoran Midtrans dan hari hijau akan menambah progresmu.
                        </div>
                    @endif
                @else
                    <p class="saveup-target">
                        Kamu belum punya target tabungan. Buat target agar progress lebih terarah.
                    </p>
                @endif
            </div>

            <div class="saveup-form-box">
                <h3>{{ $saveupTarget ? 'Update Target' : 'Buat Target' }}</h3>

                <form method="POST" action="{{ route('student.saveup.target.store') }}">
                    @csrf

                    <div class="field">
                        <label>Nama Target</label>
                        <input
                            type="text"
                            name="target_name"
                            required
                            maxlength="100"
                            value="{{ old('target_name', $saveupTarget->sav_target_name ?? '') }}"
                            placeholder="Contoh: Beli buku, tabungan kuliah"
                        >
                    </div>

                    <div class="field">
                        <label>Nominal Target</label>
                        <input
                            type="number"
                            name="target_amount"
                            required
                            min="1000"
                            max="9999999999"
                            value="{{ old('target_amount', $saveupTarget->sav_target_amount ?? '') }}"
                            placeholder="Contoh: 500000"
                        >
                        <p class="hint">Isi angka saja, tanpa titik atau Rp.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Simpan Target
                    </button>
                </form>
            </div>
        </div>

        <div class="saveup-deposit-area">
            <div class="saveup-deposit-form">
                <h3>Setor Tabungan via Midtrans</h3>

                <div class="form-grid">
                    <div class="field">
                        <label>Nominal Setoran</label>
                        <input
                            type="number"
                            id="depositAmountInput"
                            required
                            min="10000"
                            max="9999999999"
                            placeholder="Minimal Rp 10.000"
                        >
                    </div>

                    <div class="field">
                        <label>Catatan (opsional)</label>
                        <input
                            type="text"
                            id="depositNoteInput"
                            maxlength="255"
                            placeholder="Contoh: Sisihkan uang jajan"
                        >
                    </div>
                </div>

                <div class="deposit-actions">
                    <button type="button" id="btnPayMidtrans" class="btn btn-midtrans">
                        💳 Setor via Midtrans
                    </button>
                </div>

                <p class="hint">
                    Bayar aman via <strong>QRIS, GoPay, ShopeePay, atau Virtual Account bank</strong>.
                    Dana dihitung ke progres setelah pembayaran <em>lunas</em>, dan terkunci sampai targetmu tercapai.
                </p>

                @if ($lastMidtransDeposit)
                    <div class="midtrans-status status-{{ $lastMidtransDeposit->dep_status }}">
                        Transaksi Midtrans terakhir:
                        <strong>Rp {{ number_format($lastMidtransDeposit->dep_amount, 0, ',', '.') }}</strong>
                        —
                        @switch($lastMidtransDeposit->dep_status)
                            @case('paid')    Lunas ✅ @break
                            @case('pending') Menunggu pembayaran ⏳ @break
                            @case('failed')  Gagal / dibatalkan ❌ @break
                            @default {{ $lastMidtransDeposit->dep_status }}
                        @endswitch
                    </div>
                @endif
            </div>

            <div class="saveup-vault">
                <h3>🔒 Tarik Dana (Vault)</h3>

                @if (!$saveupTarget)
                    <p class="vault-note">Buat target dulu untuk mengunci dana tabunganmu.</p>
                @elseif ($pendingWithdrawal)
                    <div class="vault-status is-pending">
                        Pengajuan penarikan sebesar
                        <strong>Rp {{ number_format($pendingWithdrawal->wdr_amount, 0, ',', '.') }}</strong>
                        sedang menunggu persetujuan admin.
                    </div>
                @elseif ($vaultUnlocked)
                    <div class="vault-status is-unlocked">
                        🎉 Target tercapai — vault terbuka. Kamu bisa mengajukan penarikan sekarang.
                    </div>
                    <form method="POST" action="{{ route('student.saveup.withdraw') }}" class="vault-form">
                        @csrf
                        <div class="field">
                            <label>Alasan / Kebutuhan (opsional)</label>
                            <input type="text" name="withdraw_reason" maxlength="255" placeholder="Contoh: bayar UKT, beli laptop">
                        </div>
                        <button type="submit" class="btn btn-primary">Ajukan Penarikan</button>
                    </form>
                @else
                    @php
                        $sisa = ($saveupTarget->sav_target_amount ?? 0) - $saveupTotalProgressAmount;
                    @endphp
                    <div class="vault-status is-locked">
                        Vault terkunci. Kurang
                        <strong>Rp {{ number_format(max($sisa, 0), 0, ',', '.') }}</strong>
                        lagi untuk membuka penarikan.
                    </div>
                @endif

                @if ($lastWithdrawal && $lastWithdrawal->wdr_status !== 'pending')
                    <p class="vault-last">
                        Pengajuan terakhir: Rp {{ number_format($lastWithdrawal->wdr_amount, 0, ',', '.') }}
                        —
                        @switch($lastWithdrawal->wdr_status)
                            @case('approved') <span class="tag-ok">disetujui</span> @break
                            @case('rejected') <span class="tag-no">ditolak</span> @break
                        @endswitch
                    </p>
                @endif
            </div>

            <div class="saveup-history">
                <h3>Riwayat Setoran</h3>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($saveupDeposits as $deposit)
                                <tr>
                                    <td>{{ $deposit->dep_date }}</td>
                                    <td>Rp {{ number_format($deposit->dep_amount, 0, ',', '.') }}</td>
                                    <td>{{ $deposit->dep_note ?: '-' }}</td>
                                    <td>
                                        <form
                                            method="POST"
                                            action="{{ route('student.saveup.deposit.destroy', $deposit->dep_id) }}"
                                            class="inline-delete-form"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-small-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada setoran manual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

</div>

<script src="{{ asset('js/student-dashboard.js') }}"></script>
{{-- Midtrans Snap.js (sandbox) --}}
    <script
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
    (function () {
        var btn = document.getElementById('btnPayMidtrans');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var amountEl = document.getElementById('depositAmountInput');
            var noteEl   = document.getElementById('depositNoteInput');
            var amount   = parseInt(amountEl && amountEl.value, 10);
            var note     = noteEl ? noteEl.value : '';

            if (!amount || amount < 10000) {
                alert('Minimal setoran via Midtrans Rp 10.000');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Memproses...';

            fetch('{{ route('student.saveup.snap.token') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ amount: amount, note: note })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = '💳 Setor via Midtrans';

                if (data.error) {
                    alert('Gagal: ' + data.error);
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    alert('Midtrans Snap belum siap. Cek koneksi & MIDTRANS_CLIENT_KEY di .env.');
                    return;
                }

                window.snap.pay(data.snap_token, {
                    onSuccess: function () { window.location.reload(); },
                    onPending: function () { window.location.reload(); },
                    onError:   function () { alert('Pembayaran gagal.'); },
                    onClose:   function () {}
                });
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.innerHTML = '💳 Setor via Midtrans';
                alert('Terjadi kesalahan: ' + err);
            });
        });
    })();
    </script>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
