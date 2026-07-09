<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

<div class="page riseup-shell">
    <header class="riseup-navbar">
        <div class="riseup-brand">
            <div class="riseup-brand-mark">A</div>

            <div class="riseup-brand-text">
                <strong>RiseUp Admin</strong>
                <span>{{ session('auth_name') }}</span>
            </div>
        </div>

        <div class="riseup-nav-actions">
            <a href="{{ route('admin.dashboard') }}" class="riseup-nav-link">
                Dashboard
            </a>

            <a href="{{ route('admin.users.create') }}" class="riseup-nav-link">
                Tambah User
            </a>

            <a href="{{ route('admin.withdrawals.index') }}" class="riseup-nav-link">
                Pengajuan Tarik
            </a>

            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="riseup-nav-button">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <section class="riseup-hero">
        <div>
            <p class="eyebrow">Admin Panel</p>
            <h1>Dashboard Admin RiseUp</h1>
            <p>
                Kelola user, pantau aktivitas check-in, microlearning, quest,
                dan progress pemulihan pengguna.
            </p>
        </div>

        <div class="riseup-hero-badge">
            {{ $totalUsers }} User
        </div>
    </section>

    @if (session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="stats-grid">
        <article class="card stat-card">
            <p>Total User</p>
            <strong>{{ $totalUsers }}</strong>
        </article>

        <article class="card stat-card">
            <p>Check-in Hari Ini</p>
            <strong>{{ $todayCheckins }}</strong>
        </article>

        <article class="card stat-card">
            <p>User Perlu Perhatian</p>
            <strong>{{ $attentionUsers }}</strong>
        </article>
    </section>

    <section class="card api-card">
        <div class="section-head">
            <div>
                <h2>Ringkasan Admin dari API</h2>
                <p>Data ini diambil menggunakan JavaScript Fetch API dari endpoint admin.</p>
            </div>

            <span class="api-badge">Admin API</span>
        </div>

        <div
            class="api-grid"
            id="adminSummaryApi"
            data-api-url="{{ route('api.admin.summary') }}"
        >
            <article class="mini-card">
                <p>User Aktif</p>
                <strong id="apiActiveUsers">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>User Nonaktif</p>
                <strong id="apiInactiveUsers">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Sudah Onboarding</p>
                <strong id="apiOnboardedUsers">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>XP Terkumpul</p>
                <strong id="apiTotalXp">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Hijau Minggu Ini</p>
                <strong id="apiGreenWeek">Loading...</strong>
            </article>

            <article class="mini-card">
                <p>Relapse Minggu Ini</p>
                <strong id="apiRedWeek">Loading...</strong>
            </article>
        </div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Data User</h2>
                <p>Lihat, cari, tambah, edit, hapus, dan pantau detail user RiseUp.</p>
            </div>

            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                + Tambah User
            </a>
        </div>

        <div class="toolbar">
            <div class="search-box">
                <input
                    type="search"
                    id="searchInput"
                    placeholder="Cari email, status, XP, atau level..."
                >
            </div>

            <div class="table-info">
                <span id="visibleCount">{{ $users->count() }}</span> dari {{ $users->count() }} user tampil
            </div>
        </div>

        <div class="table-wrap">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Status</th>
                        <th>XP</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        @php $needsAttention = in_array($user->usr_id, $attentionIds ?? []); @endphp
                        <tr class="{{ $needsAttention ? 'row-attention' : '' }}">
                            <td>
                                {{ $user->usr_email }}
                                @if ($needsAttention)
                                    <span class="attention-badge" title="Ada check-in merah dalam 7 hari terakhir">&#9888; Perlu perhatian</span>
                                @endif
                            </td>

                            <td>
                                <span class="status-pill status-{{ $user->usr_status }}">
                                    {{ $user->usr_status }}
                                </span>
                            </td>

                            <td>{{ $user->gamification->gms_total_xp ?? 0 }}</td>

                            <td>{{ $user->gamification->gms_level_num ?? 1 }}</td>

                            <td class="action-cell">
                                <a href="{{ route('admin.users.show', $user->usr_id) }}" class="btn-action btn-detail">
                                    Detail
                                </a>

                                <a href="{{ route('admin.users.edit', $user->usr_id) }}" class="btn-action btn-edit">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.users.destroy', $user->usr_id) }}"
                                    class="delete-form"
                                    data-email="{{ $user->usr_email }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-action btn-delete">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-cell">
                                Belum ada user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="{{ asset('js/admin-dashboard.js') }}"></script>
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>