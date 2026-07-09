{{-- Navbar Student RiseUp --}}
<header class="riseup-navbar">
    <div class="riseup-brand">
        <div class="riseup-brand-mark">
            @if(session('auth_avatar'))
                <img src="{{ session('auth_avatar') }}" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            @else
                R
            @endif
        </div>
        <div class="riseup-brand-text">
            <strong>RiseUp Student</strong>
            <span>{{ session('auth_name') }}</span>
        </div>
    </div>

    <div class="riseup-nav-actions">
        <a href="{{ route('student.dashboard') }}" class="riseup-nav-link">Dashboard</a>
        <a href="{{ route('student.checkin.index') }}" class="riseup-nav-link">Check-in</a>
        <a href="{{ route('student.saveup.index') }}" class="riseup-nav-link">Save Up</a>
        <a href="{{ route('student.learning.index') }}" class="riseup-nav-link">Microlearning</a>
        <a href="{{ route('student.quests.index') }}" class="riseup-nav-link">Positive Quest</a>
        <a href="{{ route('student.journal.index') }}" class="riseup-nav-link">Jurnal</a>
        <a href="{{ route('student.achievements.index') }}" class="riseup-nav-link">Pencapaian</a>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="riseup-nav-button">Logout</button>
        </form>
    </div>
</header>
