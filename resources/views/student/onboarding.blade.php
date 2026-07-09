<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Onboarding RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

<div class="page">
    <header class="topbar">
        <div class="brand">
            <img src="{{ asset('assets/img/logo-riseup.png') }}" alt="RiseUp Logo">
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost">Logout</button>
        </form>
    </header>

    <main class="layout">
        <section class="intro">
            <div class="badge">Onboarding</div>

            <h1>
                Kenali titik awalmu,<br>
                lalu mulai naik lagi.
            </h1>

            <p>
                Data awal ini dipakai untuk mencatat baseline perjalanan kamu.
                Setelah selesai, kamu akan masuk ke dashboard dan bisa mulai check-in harian.
            </p>

            <div class="mascot-card">
                <div class="mascot-glow"></div>
                <img
                    id="onboardingMascot"
                    src="{{ asset('assets/img/mascot-normal.png') }}"
                    alt="Mascot RiseUp"
                    data-normal="{{ asset('assets/img/mascot-normal.png') }}"
                    data-happy="{{ asset('assets/img/mascot-happy.png') }}"
                    data-wow="{{ asset('assets/img/mascot-wow.png') }}"
                    data-huh="{{ asset('assets/img/mascot-huh.png') }}"
                >

                <div class="tip-card" id="onboardingTip">
                    Isi pelan-pelan. Tidak harus sempurna, yang penting mulai.
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="form-head">
                <div>
                    <p class="eyebrow">Halo, {{ session('auth_name') }}</p>
                    <h2>Form Onboarding</h2>
                </div>

                <span class="step-pill">
                    Step <span id="stepNumber">1</span>/6
                </span>
            </div>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('onboarding.store') }}" id="onboardingForm">
                @csrf

                <div class="field">
                    <label>Sudah berapa lama terpapar judi online?</label>
                    <select name="exposure_duration" class="tracked-field" required>
                        <option value="">Pilih durasi</option>
                        <option value="<6m">Kurang dari 6 bulan</option>
                        <option value="6-12m">6–12 bulan</option>
                        <option value=">12m">Lebih dari 12 bulan</option>
                    </select>
                </div>

                <div class="field">
                    <label>Alasan utama ingin berhenti</label>
                    <select name="main_reason" class="tracked-field" required>
                        <option value="">Pilih alasan</option>
                        <option value="stres">Stres</option>
                        <option value="bosan">Bosan</option>
                        <option value="teman">Pengaruh teman</option>
                        <option value="uang">Masalah uang</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="field">
                    <label>Target utama</label>
                    <select name="target_goal" class="tracked-field" required>
                        <option value="">Pilih target</option>
                        <option value="stop">Berhenti total</option>
                        <option value="reduce_frequency">Mengurangi frekuensi</option>
                        <option value="reduce_duration">Mengurangi durasi</option>
                    </select>
                </div>

                <div class="field">
                    <label>Durasi rata-rata per hari</label>
                    <select name="daily_duration" class="tracked-field" required>
                        <option value="">Pilih durasi</option>
                        <option value="<30m">Kurang dari 30 menit</option>
                        <option value="30-60m">30–60 menit</option>
                        <option value="1-2h">1–2 jam</option>
                        <option value=">2h">Lebih dari 2 jam</option>
                    </select>
                </div>

                <div class="field">
                    <label>Perkiraan kerugian per bulan</label>
                    <input
                        type="number"
                        name="estimated_loss"
                        class="tracked-field money-field"
                        min="0"
                        max="9999999999"
                        placeholder="Contoh: 500000"
                    >
                    <p class="hint">Isi angka saja, tanpa titik atau Rp.</p>
                </div>

                <div class="field">
                    <label>Perkiraan pendapatan bulanan</label>
                    <input
                        type="number"
                        name="monthly_income"
                        class="tracked-field money-field"
                        min="0"
                        max="9999999999"
                        placeholder="Contoh: 1500000"
                    >
                </div>

                <div class="field">
                    <label>Jam rawan (waktu paling sering tergoda)</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="time" name="risk_hour_start" class="tracked-field" style="flex:1;">
                        <span style="color:#6b7280;">s/d</span>
                        <input type="time" name="risk_hour_end" class="tracked-field" style="flex:1;">
                    </div>
                    <p class="hint">Buddy akan mengingatkanmu pada rentang jam ini. Boleh dikosongkan.</p>
                </div>

                <div class="progress-wrap">
                    <div class="progress-label">
                        <span>Kelengkapan form</span>
                        <strong id="progressText">0%</strong>
                    </div>
                    <div class="progress-bar">
                        <span id="progressBar"></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan dan Masuk Dashboard
                </button>
            </form>
        </section>
    </main>
</div>

<script src="{{ asset('js/onboarding.js') }}"></script>
    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>