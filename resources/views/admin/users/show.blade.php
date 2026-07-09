<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail User - RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/admin-user-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

<div class="page">
    <header class="topbar">
        <div>
            <p class="eyebrow">Admin Panel</p>
            <h1>Detail User</h1>
            <p class="subtitle">Monitoring data onboarding, check-in, XP, dan pola risiko user.</p>
        </div>

        <div class="top-actions">
            <a href="{{ route('admin.users.edit', $user->usr_id) }}" class="btn btn-primary">
                Edit User
            </a>

            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                ← Dashboard Admin
            </a>
        </div>
    </header>

    <section class="profile-card">
        <div class="profile-main">
            <div class="avatar">
                {{ strtoupper(substr($user->usr_email, 0, 1)) }}
            </div>

            <div>
                <h2>{{ $user->usr_email }}</h2>

                <div class="profile-meta">
                    <span class="status-pill status-{{ $user->usr_status }}">
                        {{ $user->usr_status }}
                    </span>

                    <span>User ID: {{ $user->usr_id }}</span>
                </div>
            </div>
        </div>

        <div class="risk-box risk-{{ $riskClass }}">
            <span>Level Risiko</span>
            <strong>{{ $riskLevel }}</strong>
        </div>
    </section>

    <section class="stats-grid">
        <article class="card stat-card">
            <p>Total Check-in</p>
            <strong>{{ $totalCheckins }}</strong>
        </article>

        <article class="card stat-card">
            <p>Hari Hijau</p>
            <strong>{{ $greenDays }}</strong>
        </article>

        <article class="card stat-card">
            <p>Relapse</p>
            <strong>{{ $redDays }}</strong>
        </article>

        <article class="card stat-card">
            <p>Rata-rata Urge</p>
            <strong>{{ $avgUrge }}/5</strong>
        </article>

        <article class="card stat-card">
            <p>Total XP</p>
            <strong>{{ $user->gamification->gms_total_xp ?? 0 }}</strong>
        </article>

        <article class="card stat-card">
            <p>Level</p>
            <strong>{{ $user->gamification->gms_level_num ?? 1 }}</strong>
        </article>
    </section>

    <section class="grid-two">
        <article class="card">
            <div class="section-head">
                <div>
                    <h2>Baseline Onboarding</h2>
                    <p>Data awal user untuk memahami kondisi dan target perubahan.</p>
                </div>
            </div>

            @if ($onboarding)
                <div class="detail-list">
                    <div>
                        <span>Lama terpapar</span>
                        <strong>{{ $onboarding->bas_exposure_duration }}</strong>
                    </div>

                    <div>
                        <span>Alasan utama</span>
                        <strong>{{ $onboarding->bas_main_reason }}</strong>
                    </div>

                    <div>
                        <span>Target perubahan</span>
                        <strong>{{ $onboarding->bas_target_goal }}</strong>
                    </div>

                    <div>
                        <span>Durasi harian</span>
                        <strong>{{ $onboarding->bas_daily_duration }}</strong>
                    </div>

                    <div>
                        <span>Estimasi kerugian/bulan</span>
                        <strong>Rp {{ number_format($onboarding->bas_est_loss_monthly ?? 0, 0, ',', '.') }}</strong>
                    </div>

                    <div>
                        <span>Estimasi pendapatan/bulan</span>
                        <strong>Rp {{ number_format($onboarding->bas_est_income_monthly ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            @else
                <div class="empty-box">
                    User belum mengisi onboarding.
                </div>
            @endif
        </article>

        <article class="card">
            <div class="section-head">
                <div>
                    <h2>Analisis Ringkas</h2>
                    <p>Analisis rule-based sederhana dari check-in user.</p>
                </div>
            </div>

            <div class="insight-box">
                <div>
                    <span>Persentase relapse</span>
                    <strong>{{ $redRate }}%</strong>
                </div>

                <div>
                    <span>Trigger dominan</span>
                    <strong>{{ $dominantTrigger ?? 'Belum ada data' }}</strong>
                </div>

                <div>
                    <span>Estimasi uang terselamatkan</span>
                    <strong>Rp {{ number_format($estimatedSaved, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="recommendation risk-{{ $riskClass }}">
                <span>Rekomendasi Admin</span>
                <p>{{ $recommendation }}</p>
            </div>
        </article>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Riwayat Check-in Terakhir</h2>
                <p>Menampilkan maksimal 10 check-in terbaru dari user.</p>
            </div>

            <input type="search" id="checkinSearch" class="search-input" placeholder="Cari mood, trigger, status...">
        </div>

        <div class="table-wrap">
            <table id="checkinTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mood</th>
                        <th>Urge</th>
                        <th>Trigger</th>
                        <th>Status</th>
                        <th>Alasan Relapse</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($latestCheckins as $checkin)
                        <tr>
                            <td>{{ $checkin->chk_date }}</td>
                            <td>{{ $checkin->chk_mood }}</td>
                            <td>{{ $checkin->chk_urge_level }}/5</td>
                            <td>{{ $checkin->chk_trigger }}</td>
                            <td>
                                <span class="status-pill status-{{ $checkin->chk_status_color }}">
                                    {{ $checkin->chk_status_color }}
                                </span>
                            </td>
                            <td>{{ $checkin->chk_relapse_reason ?: '-' }}</td>
                            <td>{{ $checkin->chk_note_text ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">Belum ada riwayat check-in.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="{{ asset('js/admin-user-detail.js') }}"></script>
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>