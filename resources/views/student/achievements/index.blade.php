<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pencapaian RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/achievements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

<div class="riseup-shell">
@include('student.partials.navbar')
<div class="ach-page">

    <header class="ach-topbar">
        <div>
            <p class="eyebrow">Pencapaian</p>
            <h1>Badge &amp; Leaderboard</h1>
            <p class="subtitle">
                Kumpulkan badge dari konsistensimu dan lihat peringkat XP mingguan.
            </p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="ach-back">← Dashboard</a>
    </header>

    @if (!empty($newlyEarned))
        <div class="ach-flash">
            🎉 Selamat! Kamu baru saja mendapatkan {{ count($newlyEarned) }} badge baru.
        </div>
    @endif

    {{-- ---------- Badge ---------- --}}
    <div class="ach-section-head">
        <h2>Koleksi Badge</h2>
        <span class="count">{{ $earnedCount }} / {{ $totalBadges }} diperoleh</span>
    </div>

    <section class="badge-grid">
        @foreach ($badges as $b)
            <article class="badge-card {{ $b['owned'] ? 'owned' : 'locked' }}">
                <div class="b-icon">{{ $b['icon'] }}</div>
                <p class="b-name">{{ $b['name'] }}</p>
                <p class="b-desc">{{ $b['description'] }}</p>

                @if ($b['owned'])
                    <span class="b-earned">✓ Diperoleh</span>
                @else
                    <div class="b-progress">
                        <div class="b-bar"><span style="width: {{ $b['percent'] }}%"></span></div>
                        <p class="b-progress-text">{{ $b['progress'] }} / {{ $b['target'] }}</p>
                    </div>
                @endif
            </article>
        @endforeach
    </section>

    {{-- ---------- Leaderboard ---------- --}}
    <div class="ach-section-head">
        <h2>Leaderboard Mingguan</h2>
        <span class="count">Berdasarkan XP minggu ini</span>
    </div>

    <section class="lb-card">
        @forelse ($topLeaderboard as $row)
            <div class="lb-row {{ $row['is_me'] ? 'is-me' : '' }}">
                <div class="lb-rank">{{ $row['rank'] }}</div>
                <div class="lb-name">
                    {{ $row['name'] }}{{ $row['is_me'] ? ' (kamu)' : '' }}
                    <small>Total {{ number_format($row['total_xp']) }} XP</small>
                </div>
                <div class="lb-level">Lv {{ $row['level'] }}</div>
                <div class="lb-week">{{ number_format($row['weekly_xp']) }}<small> XP minggu ini</small></div>
            </div>
        @empty
            <p class="lb-empty">Belum ada data leaderboard.</p>
        @endforelse
    </section>

    @if ($myRankRow && $myRankRow['rank'] > 10)
        <div class="lb-me-note">
            Peringkatmu saat ini: <strong>#{{ $myRankRow['rank'] }}</strong>
            dengan {{ number_format($myRankRow['weekly_xp']) }} XP minggu ini.
        </div>
    @endif

</div>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
