<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Positive Quest RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/quests.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
>

@include('student.partials.navbar')
<div class="page">
    <header class="topbar">
        <div>
            <p class="eyebrow">Positive Quest</p>
            <h1>Alihkan Dorongan dengan Aksi Kecil</h1>
            <p class="subtitle">
                Pilih aktivitas singkat saat urge muncul. Selesaikan quest untuk mendapatkan XP.
            </p>
        </div>

        <a href="{{ route('student.dashboard') }}" class="btn btn-ghost">
            ← Dashboard
        </a>
    </header>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <section class="card progress-card">
        <div>
            <h2>Progress Quest Hari Ini</h2>
            <p>{{ $completedToday }} dari {{ $totalQuests }} quest selesai hari ini.</p>
        </div>

        <div class="progress-info">
            <strong>{{ $progressPercent }}%</strong>
            <div class="progress-bar">
                <span style="width: {{ $progressPercent }}%"></span>
            </div>
        </div>
    </section>

    <section class="quest-grid">
        @forelse ($quests as $quest)
            @php
                $isCompleted = in_array($quest->qst_id, $completedQuestIds);
            @endphp

            <article class="quest-card {{ $isCompleted ? 'completed' : '' }}">
                <div class="quest-top">
                    <span class="category-badge category-{{ $quest->qst_category }}">
                        {{ $quest->qst_category }}
                    </span>

                    <span class="duration-badge">
                        {{ $quest->qst_duration_min }} menit
                    </span>
                </div>

                <h2>{{ $quest->qst_title }}</h2>

                <p>{{ $quest->qst_description }}</p>

                <div class="quest-footer">
                    <span class="xp-badge">+{{ $quest->qst_xp_reward }} XP</span>

                    @if ($isCompleted)
                        <span class="done-badge">Completed</span>
                    @else
                        <form method="POST" action="{{ route('student.quests.complete', $quest->qst_id) }}"
                              onsubmit="this.querySelector('button').innerHTML='⏳ Menyimpan...'; this.querySelector('button').disabled=true;">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Selesaikan
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="card">
                Belum ada quest aktif.
            </div>
        @endforelse
    </section>
</div>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>