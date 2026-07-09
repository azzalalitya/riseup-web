<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Penarikan — Admin RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">
    <style>
        .wdr-page { max-width: 1080px; margin: 0 auto; padding: 28px 20px 60px; }
        .wdr-topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:14px; }
        .wdr-topbar h1 { margin:0; font-size:24px; }
        .wdr-back { padding:10px 16px; border-radius:10px; background:#fff; border:1px solid var(--riseup-line, #e5e7eb); text-decoration:none; color:var(--riseup-dark, #1f2937); font-weight:600; }

        .wdr-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--riseup-line, #e5e7eb); border-radius:12px; overflow:hidden; }
        .wdr-table th, .wdr-table td { padding:12px 14px; text-align:left; vertical-align:top; font-size:14px; }
        .wdr-table thead { background:var(--riseup-cream, #fff8ec); }
        .wdr-table tr + tr td { border-top:1px solid var(--riseup-line, #e5e7eb); }
        .wdr-user strong { display:block; }
        .wdr-user small { color:var(--riseup-muted, #6b7280); }
        .wdr-amount { font-weight:800; color:var(--riseup-orange-dark, #f97316); white-space:nowrap; }

        .wdr-badge { padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .wdr-badge.s-pending  { background:#fef9c3; color:#854d0e; }
        .wdr-badge.s-approved { background:#dcfce7; color:#166534; }
        .wdr-badge.s-rejected { background:#fee2e2; color:#991b1b; }

        .wdr-actions form { display:inline; }
        .wdr-actions .decide { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
        .wdr-actions input[type=text] { padding:6px 10px; border:1px solid var(--riseup-line, #e5e7eb); border-radius:8px; font-size:12px; min-width:140px; }
        .wdr-btn { border:none; padding:6px 12px; border-radius:8px; font-weight:700; cursor:pointer; font-size:12px; }
        .wdr-btn.approve { background:#22c55e; color:#fff; }
        .wdr-btn.reject  { background:#ef4444; color:#fff; }

        .wdr-empty { padding:32px; text-align:center; color:var(--riseup-muted, #6b7280); }
    </style>
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

<div class="wdr-page">

    <header class="wdr-topbar">
        <h1>Pengajuan Penarikan Dana</h1>
        <a href="{{ route('admin.dashboard') }}" class="wdr-back">← Admin Dashboard</a>
    </header>

    @if (session('success')) <div class="success">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="error-flash">{{ session('error') }}</div> @endif

    <table class="wdr-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Jumlah</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Diajukan</th>
                <th>Keputusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="wdr-user">
                        <strong>{{ $r['user'] }}</strong>
                        <small>{{ $r['email'] }}</small>
                    </td>
                    <td class="wdr-amount">Rp {{ number_format($r['amount'], 0, ',', '.') }}</td>
                    <td>{{ $r['reason'] ?: '-' }}</td>
                    <td><span class="wdr-badge s-{{ $r['status'] }}">{{ ucfirst($r['status']) }}</span></td>
                    <td>{{ $r['created_at'] ? \Carbon\Carbon::parse($r['created_at'])->translatedFormat('d M Y H:i') : '-' }}</td>
                    <td class="wdr-actions">
                        @if ($r['status'] === 'pending')
                            <form method="POST" action="{{ route('admin.withdrawals.update', $r['id']) }}" class="decide">
                                @csrf
                                <input type="text" name="admin_note" maxlength="255" placeholder="Catatan (opsional)">
                                <button type="submit" name="decision" value="approved" class="wdr-btn approve">Setujui</button>
                                <button type="submit" name="decision" value="rejected" class="wdr-btn reject">Tolak</button>
                            </form>
                        @else
                            <small>
                                {{ $r['processed_at'] ? \Carbon\Carbon::parse($r['processed_at'])->translatedFormat('d M Y H:i') : '-' }}
                                @if ($r['admin_note'])
                                    <br><em>{{ $r['admin_note'] }}</em>
                                @endif
                            </small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="wdr-empty">Belum ada pengajuan penarikan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
    @include('partials.footer')


    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
