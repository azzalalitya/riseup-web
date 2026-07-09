{{--
    Footer RiseUp dengan wave SVG.
    Pakai: @include('partials.footer')
    Butuh: css/footer.css (di-link otomatis di bawah).
--}}
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">

<footer class="riseup-footer">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path class="wave wave-back"
                d="M0,64 C240,110 480,10 720,50 C960,90 1200,20 1440,60 L1440,120 L0,120 Z"/>
            <path class="wave wave-front"
                d="M0,80 C260,30 520,110 780,70 C1040,30 1280,90 1440,50 L1440,120 L0,120 Z"/>
        </svg>
    </div>

    <div class="footer-body">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="{{ asset('assets/img/logo-riseup.png') }}" alt="RiseUp" onerror="this.style.display='none'">
                <p>
                    Mulai langkah kecil, naik lagi bersama RiseUp.
                    Teman perjalananmu menuju kebiasaan yang lebih sehat.
                </p>
            </div>

            <div class="footer-col">
                <h4>Fitur</h4>
                <ul>
                    <li><a href="{{ session('auth_role') === 'user' ? route('student.dashboard') : '#' }}">Dashboard</a></li>
                    <li><a href="{{ session('auth_role') === 'user' ? route('student.learning.index') : '#' }}">Microlearning</a></li>
                    <li><a href="{{ session('auth_role') === 'user' ? route('student.quests.index') : '#' }}">Positive Quest</a></li>
                    <li><a href="{{ session('auth_role') === 'user' ? route('student.journal.index') : '#' }}">Jurnal Harian</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Dukungan</h4>
                <ul>
                    <li><a href="#">Tentang RiseUp</a></li>
                    <li><a href="#">Panduan Pemulihan</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                    <li><a href="#">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="footer-col footer-quote">
                <h4>Pengingat Hari Ini</h4>
                <blockquote>
                    &ldquo;Setiap langkah kecil menjauh dari kebiasaan lama adalah kemenangan yang layak dirayakan.&rdquo;
                </blockquote>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} RiseUp &mdash; Universitas Airlangga</span>
            <span class="footer-dot-sep">Dibuat dengan <span class="footer-heart">&#10084;</span> untuk pemulihan yang lebih baik</span>
        </div>
    </div>
</footer>
