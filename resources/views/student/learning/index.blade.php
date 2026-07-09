<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Microlearning RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/learning.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

@include('student.partials.navbar')
<div class="page">
    <header class="topbar">
        <div>
            <p class="eyebrow">Microlearning Path</p>
            <h1>Belajar Singkat, Naik Bertahap</h1>
            <p class="subtitle">Selesaikan materi harian untuk membangun awareness, coping, dan motivasi finansial.</p>
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
            <h2>Progress Belajar</h2>
            <p>{{ $completedCount }} dari {{ $totalModules }} materi selesai.</p>
        </div>

        <div class="progress-info">
            <strong>{{ $progressPercent }}%</strong>
            <div class="progress-bar">
                <span style="width: {{ $progressPercent }}%"></span>
            </div>
        </div>
    </section>

    <section class="module-grid">
        @forelse ($modules as $module)
            @php
                $isCompleted = in_array($module->lrn_id, $completedModuleIds);
            @endphp

            <article class="module-card {{ $isCompleted ? 'completed' : '' }}">
                <div class="module-top">
                    <span class="day-badge">Day {{ $module->lrn_day_number }}</span>
                    <span class="category-badge">{{ $module->lrn_category }}</span>
                </div>

                <h2>{{ $module->lrn_title }}</h2>

                <p>{{ Str::limit($module->lrn_content, 130) }}</p>

                <div class="module-footer">
                    <span class="xp-badge">+{{ $module->lrn_xp_reward }} XP</span>

                    @if ($isCompleted)
                        <span class="done-badge">Completed</span>
                    @else
                        <a href="{{ route('student.learning.show', $module->lrn_id) }}" class="btn btn-primary">
                            Mulai
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="card">
                Belum ada materi aktif.
            </div>
        @endforelse
    </section>
</div>

    @include('student.partials.buddy')
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>