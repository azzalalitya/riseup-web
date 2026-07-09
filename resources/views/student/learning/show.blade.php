<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $module->lrn_title }} - RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/learning.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

@include('student.partials.navbar')
<div class="page page-narrow">
    <header class="topbar">
        <div>
            <p class="eyebrow">Day {{ $module->lrn_day_number }} • {{ $module->lrn_category }}</p>
            <h1>{{ $module->lrn_title }}</h1>
            <p class="subtitle">Baca materi singkat ini, lalu selesaikan untuk mendapatkan XP.</p>
        </div>

        <a href="{{ route('student.learning.index') }}" class="btn btn-ghost">
            ← Kembali
        </a>
    </header>

    <section class="card lesson-card">
        <div class="lesson-meta">
            <span class="day-badge">Day {{ $module->lrn_day_number }}</span>
            <span class="xp-badge">Reward +{{ $module->lrn_xp_reward }} XP</span>
        </div>

        <div class="lesson-content">
            {{ $module->lrn_content }}
        </div>

        @if ($isCompleted)
            <div class="success">
                Materi ini sudah selesai.
            </div>
        @else
            <form method="POST" action="{{ route('student.learning.complete', $module->lrn_id) }}">
                @csrf

                <button type="submit" class="btn btn-primary btn-full">
                    Saya sudah memahami materi ini
                </button>
            </form>
        @endif
    </section>
</div>
    @include('partials.footer')


    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>