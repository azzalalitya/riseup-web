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
    <header class="riseup-navbar">
        <div class="riseup-brand">
            <div class="riseup-brand-mark">R</div>

            <div class="riseup-brand-text">
                <strong>RiseUp Student</strong>
                <span>{{ session('auth_name') }}</span>
            </div>
        </div>

        <div class="riseup-nav-actions">
            <a href="{{ route('student.dashboard') }}" class="riseup-nav-link">
                Dashboard
            </a>

            <a href="{{ route('student.checkin.index') }}" class="riseup-nav-link">
                Check-in
            </a>

            <a href="{{ route('student.saveup.index') }}" class="riseup-nav-link">
                Save Up
            </a>

            <a href="{{ route('student.learning.index') }}" class="riseup-nav-link">
                Microlearning
            </a>

            <a href="{{ route('student.quests.index') }}" class="riseup-nav-link">
                Positive Quest
            </a>

            <a href="{{ route('student.journal.index') }}" class="riseup-nav-link">
                Jurnal
            </a>

            <a href="{{ route('student.achievements.index') }}" class="riseup-nav-link">
                Pencapaian
            </a>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="riseup-nav-button">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <section class="riseup-hero">
        <div>
            <p class="eyebrow">Dashboard Student</p>
            <h1>Halo, {{ $displayName ?? session('auth_name') }} 👋</h1>
            <p>
                Lanjutkan progress hari ini lewat check-in, aktivitas positif,
                microlearning, dan target U Save Up.
            </p>
        </div>

        <div class="riseup-hero-badge">
            Level {{ $user->gamification->gms_level_num ?? 1 }}
        </div>
    </section>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="error-flash">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <section class="quick-access-card">
        <div>
            <p class="eyebrow">Akses Cepat</p>
            <h2>Lanjutkan Aktivitas RiseUp</h2>
            <p>Pilih fitur pemulihan yang ingin kamu lanjutkan hari ini.</p>
        </div>

        <div class="quick-actions">
            <a href="{{ route('student.checkin.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-5" stroke="#22c55e" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="4" width="18" height="17" rx="4" stroke="#f97316" stroke-width="1.8"/><path d="M8 2v4M16 2v4" stroke="#f97316" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <strong>Check-in Harian</strong>
                <span>Catat kondisimu hari ini dalam 1 menit.</span>
            </a>

            <a href="{{ route('student.saveup.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M17 9V7a5 5 0 10-10 0v2" stroke="#f97316" stroke-width="1.8" stroke-linecap="round"/><rect x="4" y="9" width="16" height="12" rx="3" stroke="#f97316" stroke-width="1.8"/><circle cx="12" cy="15" r="2" fill="#ffca6b"/></svg>
                </span>
                <strong>U Save Up</strong>
                <span>Tabungan terkunci sampai target tercapai.</span>
            </a>

            <a href="{{ route('student.learning.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 5c-2.5-1.6-5.5-2-8-1.5V19c2.5-.5 5.5-.1 8 1.5 2.5-1.6 5.5-2 8-1.5V3.5C17.5 3 14.5 3.4 12 5Z" stroke="#f97316" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 5v15.5" stroke="#f97316" stroke-width="1.8"/></svg>
                </span>
                <strong>Microlearning</strong>
                <span>Materi singkat untuk awareness dan coping.</span>
            </a>

            <a href="{{ route('student.quests.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8Z" fill="#ffca6b" stroke="#f97316" stroke-width="1.6" stroke-linejoin="round"/></svg>
                </span>
                <strong>Positive Quest</strong>
                <span>Aktivitas pengalih dorongan dan penambah XP.</span>
            </a>

            <a href="{{ route('student.journal.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M4 20V6a2 2 0 012-2h12a2 2 0 012 2v14l-4-2-4 2-4-2-4 2Z" stroke="#f97316" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 9h8M8 13h5" stroke="#f97316" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <strong>Jurnal Harian</strong>
                <span>Refleksi singkat + pengingat nilai kebaikan.</span>
            </a>

            <a href="{{ route('student.achievements.index') }}" class="quick-action">
                <span class="qa-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="9" r="6" stroke="#f97316" stroke-width="1.8"/><path d="M12 6.2l.9 1.8 2 .3-1.4 1.4.3 2-1.8-.9-1.8.9.3-2L9.1 8.3l2-.3.9-1.8Z" fill="#ffca6b"/><path d="M8.5 14.5L7 22l5-3 5 3-1.5-7.5" stroke="#f97316" stroke-width="1.8" stroke-linejoin="round"/></svg>
                </span>
                <strong>Pencapaian</strong>
                <span>Lihat badge dan peringkat XP mingguanmu.</span>
            </a>
        </div>
    </section>

    <section class="card activity-summary-card">
        <div class="section-head">
            <div>
                <h2>Progress Aktivitas Hari Ini</h2>
                <p>{{ $activityMessage }}</p>
            </div>

            <span class="api-badge">Activity Tracker</span>
        </div>

        <div class="activity-summary-grid">
            <article class="activity-summary-item">
                <p>Microlearning</p>
                <strong>{{ $completedLearningCount }} / {{ $totalLearningModules }}</strong>

                <div class="activity-progress">
                    <span style="width: {{ $learningProgressPercent }}%"></span>
                </div>

                <small>{{ $learningProgressPercent }}% materi selesai</small>

                <a href="{{ route('student.learning.index') }}" class="activity-link">
                    Lanjut belajar
                </a>
            </article>

            <article class="activity-summary-item">
                <p>Positive Quest Hari Ini</p>
                <strong>{{ $todayQuestCount }} / {{ $totalQuests }}</strong>

                <div class="activity-progress">
                    <span style="width: {{ $questProgressPercent }}%"></span>
                </div>

                <small>{{ $questProgressPercent }}% quest hari ini selesai</small>

                <a href="{{ route('student.quests.index') }}" class="activity-link activity-link-green">
                    Lanjut quest
                </a>
            </article>

            <article class="activity-summary-item">
                <p>Total XP</p>
                <strong>{{ $user->gamification->gms_total_xp ?? 0 }}</strong>
                <small>XP bertambah dari check-in, microlearning, dan quest.</small>
            </article>

            <article class="activity-summary-item">
                <p>Level</p>
                <strong>{{ $user->gamification->gms_level_num ?? 1 }}</strong>
                <small>Level naik setiap akumulasi XP bertambah.</small>
            </article>
        </div>
    </section>

    <section class="stats-grid">
        <article class="card stat-card">
            <p class="stat-label">Hari Hijau</p>
            <div class="stat-number count-up" data-target="{{ $greenDays }}">{{ $greenDays }}</div>
        </article>

        <article class="card stat-card">
            <p class="stat-label">Estimasi Hemat</p>
            <div class="stat-number">Rp <span class="count-up" data-target="{{ $totalSaved }}" data-format="plain">{{ number_format($totalSaved, 0, ',', '.') }}</span></div>
        </article>

        <article class="card stat-card">
            <p class="stat-label">Total XP</p>
            <div class="stat-number count-up" data-target="{{ $user->gamification->gms_total_xp ?? 0 }}">{{ $user->gamification->gms_total_xp ?? 0 }}</div>
        </article>
    </section>

    <section class="card api-card">
        <div class="section-head">
            <div>
                <h2>Ringkasan dari API</h2>
                <p>Data ini diambil menggunakan JavaScript Fetch API dari endpoint REST sederhana.</p>
            </div>

            <span class="api-badge">Fetch API</span>
        </div>

        <div
            class="api-grid"
            data-api-url="{{ route('api.student.summary') }}"
            id="studentSummaryApi"
        >
            <article class="mini-card">
                <p>Hari Hijau API</p>
                <strong id="apiGreenDays">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Relapse API</p>
                <strong id="apiRedDays">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Total XP API</p>
                <strong id="apiTotalXp">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Level API</p>
                <strong id="apiLevel">Loading...</strong>
            </article>
        </div>
    </section>


    <section class="summary-duo">
        <article class="card mini-summary">
            <div class="mini-summary-head">
                <h2>Check-in Hari Ini</h2>
                @php $todayCheckin = $checkins->firstWhere('chk_date', today()->toDateString()); @endphp
                @if ($todayCheckin)
                    <span class="status-pill status-{{ $todayCheckin->chk_status_color }}">{{ $todayCheckin->chk_status_color === 'green' ? 'Hijau' : 'Merah' }}</span>
                @else
                    <span class="status-pill status-none">Belum</span>
                @endif
            </div>
            <p class="mini-summary-text">
                @if ($todayCheckin)
                    Kamu sudah check-in hari ini. Streak bulan ini: <strong>{{ $currentStreak }} hari</strong>.
                @else
                    Kamu belum check-in hari ini. Yuk luangkan 1 menit.
                @endif
            </p>
            <a href="{{ route('student.checkin.index') }}" class="btn btn-primary">Buka Check-in &rarr;</a>
        </article>

        <article class="card mini-summary">
            <div class="mini-summary-head">
                <h2>U Save Up</h2>
                <span class="api-badge">{{ $saveupProgress }}%</span>
            </div>
            @if ($saveupTarget)
                <div class="activity-progress"><span style="width: {{ $saveupProgress }}%"></span></div>
                <p class="mini-summary-text">
                    Rp {{ number_format($saveupTotalProgressAmount, 0, ',', '.') }} dari target
                    Rp {{ number_format($saveupTarget->sav_target_amount, 0, ',', '.') }}
                    ({{ $saveupTarget->sav_target_name }})
                </p>
            @else
                <p class="mini-summary-text">Belum ada target tabungan. Buat target pertamamu.</p>
            @endif
            <a href="{{ route('student.saveup.index') }}" class="btn btn-primary">Buka U Save Up &rarr;</a>
        </article>
    </section>
</div>

<script src="{{ asset('js/student-dashboard.js') }}"></script>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
