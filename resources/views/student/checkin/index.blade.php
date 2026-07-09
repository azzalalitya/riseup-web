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
            <p class="hero-eyebrow">Check-in Harian</p>
            <h1>Bagaimana kondisimu hari ini?</h1>
            <p class="hero-sub">Isi check-in singkat, lalu lihat progresmu di kalender bulanan.</p>
        </div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Check-in Harian</h2>
                <p>Catat kondisi hari ini. Data akan tersimpan ke database.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('student.checkin.store') }}" class="checkin-form">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label>Mood</label>
                    <select name="mood" required>
                        <option value="">Pilih mood</option>
                        <option value="baik">Baik</option>
                        <option value="netral">Netral</option>
                        <option value="sedih">Sedih</option>
                        <option value="cemas">Cemas</option>
                        <option value="stres">Stres</option>
                    </select>
                </div>

                <div class="field">
                    <label>Trigger</label>
                    <select name="trigger" required>
                        <option value="bosan">Bosan</option>
                        <option value="stress">Stress</option>
                        <option value="teman">Teman</option>
                        <option value="iklan">Iklan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="field">
                    <label>Status</label>
                    <select name="status_color" id="statusColor" required>
                        <option value="green">Tidak judi</option>
                        <option value="red">Relapse</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Urge Level: <span id="urgeText">0</span>/5</label>
                <input type="range" name="urge_level" id="urgeSlider" min="0" max="5" value="0">
            </div>

            <div class="field">
                <label>Alasan relapse</label>
                <input
                    type="text"
                    name="relapse_reason"
                    id="relapseReason"
                    placeholder="Boleh kosong kalau tidak relapse"
                >
                <p class="hint" id="relapseHint">Jika status Relapse, alasan wajib diisi.</p>
            </div>

            <div class="field">
                <label>Catatan</label>
                <textarea name="note_text" rows="3" placeholder="Cerita singkat hari ini..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Check-in</button>
        </form>
    </section>

    <section class="card calendar-card">
        <div class="section-head">
            <div>
                <h2>Kalender Progres Bulanan</h2>
                <p>Visualisasi check-in bulan {{ $monthName }}. Hijau berarti tidak judi, merah berarti relapse.</p>
            </div>

            <span class="api-badge">Streak: {{ $currentStreak }} hari</span>
        </div>

        <div class="calendar-summary">
            <article>
                <span>Hijau Bulan Ini</span>
                <strong>{{ $greenThisMonth }}</strong>
            </article>

            <article>
                <span>Relapse Bulan Ini</span>
                <strong>{{ $redThisMonth }}</strong>
            </article>

            <article>
                <span>Total Streak Sekarang</span>
                <strong>{{ $currentStreak }}</strong>
            </article>
        </div>

        <div class="calendar-weekdays">
            <span>Sen</span>
            <span>Sel</span>
            <span>Rab</span>
            <span>Kam</span>
            <span>Jum</span>
            <span>Sab</span>
            <span>Min</span>
        </div>

        <div class="calendar-grid">
            @foreach ($calendarDays as $day)
                @if ($day['day'] === null)
                    <div class="calendar-day calendar-empty"></div>
                @else
                    <div
                        class="calendar-day calendar-{{ $day['status'] }}"
                        title="{{ $day['date'] }} | status: {{ $day['status'] }} | mood: {{ $day['mood'] ?? '-' }} | urge: {{ $day['urge'] ?? '-' }} | trigger: {{ $day['trigger'] ?? '-' }}"
                    >
                        <strong>{{ $day['day'] }}</strong>

                        @if ($day['status'] === 'green')
                            <span>Tidak judi</span>
                        @elseif ($day['status'] === 'red')
                            <span>Relapse</span>
                        @else
                            <span>Belum isi</span>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        <div class="calendar-legend">
            <span><i class="legend-dot legend-green"></i> Tidak judi</span>
            <span><i class="legend-dot legend-red"></i> Relapse</span>
            <span><i class="legend-dot legend-none"></i> Belum check-in</span>
        </div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Riwayat Check-in</h2>
                <p>Daftar check-in yang sudah tersimpan.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mood</th>
                        <th>Urge</th>
                        <th>Trigger</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($checkins as $checkin)
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada check-in.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<script src="{{ asset('js/student-dashboard.js') }}"></script>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
