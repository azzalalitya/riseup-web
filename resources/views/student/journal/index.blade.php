<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Harian RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/journal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

<div class="riseup-shell">
@include('student.partials.navbar')
<div class="jrn-page">

    <header class="jrn-topbar">
        <div>
            <p class="eyebrow">Jurnal Harian</p>
            <h1>Refleksi Hari Ini</h1>
            <p class="subtitle">
                Satu pertanyaan reflektif setiap hari, dipilih dari kondisi check-in terakhirmu,
                ditemani pengingat nilai kebaikan.
            </p>
        </div>

        <a href="{{ route('student.dashboard') }}" class="jrn-back">← Dashboard</a>
    </header>

    @if (session('success'))
        <div class="jrn-flash is-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="jrn-flash is-error">{{ $errors->first() }}</div>
    @endif

    {{-- Refleksi religius (multi-agama, sesuai preferensi) --}}
    @if ($reflection)
        <section class="jrn-reflection">
            <p class="rel-label">Pengingat Hari Ini</p>
            <blockquote>"{{ $reflection->rel_text }}"</blockquote>
            <p class="rel-source">
                — {{ $reflection->rel_source ?? 'Refleksi' }}
                <br>
                <span class="rel-pref">Preferensi: {{ ucfirst($religion) }}</span>
            </p>
        </section>
    @endif

    {{-- Prompt + form jurnal --}}
    <section class="jrn-card">
        <h2>Pertanyaan Reflektif</h2>
        <p class="hint">
            @if ($mood)
                Disesuaikan dengan mood check-in terakhirmu: <strong>{{ ucfirst($mood) }}</strong>.
            @else
                Belum ada check-in terakhir, jadi kami mulai dari pertanyaan umum.
            @endif
        </p>

        <div class="jrn-prompt">{{ $prompt }}</div>

        <form method="POST" action="{{ route('student.journal.store') }}">
            @csrf
            <input type="hidden" name="prompt" value="{{ $prompt }}">

            <textarea
                name="answer_text"
                class="jrn-textarea"
                maxlength="1000"
                placeholder="Tulis refleksimu di sini..."
                required>{{ old('answer_text', $todayJournal->jrn_answer_text ?? '') }}</textarea>

            <div class="jrn-actions">
                <button type="submit" class="jrn-btn">
                    {{ $todayJournal ? 'Perbarui Jurnal' : 'Simpan Jurnal' }}
                </button>
                <span class="jrn-xp-note">
                    {{ $todayJournal ? 'Jurnal hari ini sudah tersimpan.' : 'Isi pertama hari ini memberi +15 XP.' }}
                </span>
            </div>
        </form>
    </section>

    {{-- Riwayat jurnal --}}
    <section class="jrn-history">
        <h2>Riwayat Jurnal</h2>

        @forelse ($recentJournals as $entry)
            <article class="jrn-entry">
                <div class="entry-head">
                    <span class="entry-date">
                        {{ \Carbon\Carbon::parse($entry->jrn_date)->translatedFormat('l, d F Y') }}
                    </span>
                    @if ($entry->jrn_mood_ref)
                        <span class="entry-mood">{{ ucfirst($entry->jrn_mood_ref) }}</span>
                    @endif
                </div>

                @if ($entry->jrn_prompt)
                    <p class="entry-prompt">{{ $entry->jrn_prompt }}</p>
                @endif

                <p class="entry-answer">{{ $entry->jrn_answer_text }}</p>
            </article>
        @empty
            <div class="jrn-empty">
                Belum ada jurnal. Mulai refleksi pertamamu hari ini.
            </div>
        @endforelse
    </section>

</div>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
