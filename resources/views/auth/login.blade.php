<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSS khusus halaman login --}}
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body data-open-modal="{{ $errors->any() ? '1' : '0' }}">

<div class="page">
    <div class="bubble bubble-one"></div>
    <div class="bubble bubble-two"></div>
    <div class="bubble bubble-three"></div>

    {{-- Aset dekoratif animasi --}}
    <div class="decor-layer" aria-hidden="true">
        <img class="decor-shape s1" src="{{ asset('assets/svg/leaf.svg') }}" alt="">
        <img class="decor-shape s2" src="{{ asset('assets/svg/star.svg') }}" alt="">
        <img class="decor-shape s3" src="{{ asset('assets/svg/heart.svg') }}" alt="">
        <img class="decor-shape s4" src="{{ asset('assets/svg/rise.svg') }}" alt="">
        <img class="decor-shape s5" src="{{ asset('assets/svg/star.svg') }}" alt="">
        <span class="spark k1"></span>
        <span class="spark k2"></span>
        <span class="spark k3"></span>
        <span class="spark k4"></span>
    </div>

    <nav class="navbar">
        <div class="logo">
            <img src="{{ asset('assets/img/logo-riseup.png') }}" alt="RiseUp Logo">
        </div>

        <div class="mini-nav">
            <button type="button" class="mini-pill" onclick="openAuthModal('loginForm')">Masuk</button>
            <button type="button" class="mini-pill mini-pill-primary" onclick="openAuthModal('registerForm')">Daftar Gratis</button>
        </div>
    </nav>

    <main class="main">
        <section class="hero">
            <div class="eyebrow">
                <span class="eyebrow-dot"></span>
                ringan, seru, berdampak
            </div>

            <h1>
                Mulai langkah kecil,<br>
                <span>naik lagi bersama RiseUp.</span>
            </h1>

            <p>
                Catat progress, lakukan check-in harian, dan bangun kebiasaan baru.
                RiseUp membantu kamu melihat perubahan kecil yang konsisten setiap hari.
            </p>

            <div class="hero-actions">
                <button type="button" class="soft-button" id="goRegister">Mulai sekarang</button>
                <button type="button" class="ghost-button" id="goLogin">Saya sudah punya akun</button>
            </div>

            <div class="mascot-stage">
                <div class="mascot-glow"></div>

                <img
                    class="main-mascot"
                    id="mainMascot"
                    src="{{ asset('assets/img/mascot-happy.png') }}"
                    alt="Mascot RiseUp"
                    data-happy="{{ asset('assets/img/mascot-happy.png') }}"
                    data-normal="{{ asset('assets/img/mascot-normal.png') }}"
                    data-wow="{{ asset('assets/img/mascot-wow.png') }}"
                    data-huh="{{ asset('assets/img/mascot-huh.png') }}"
                    data-sad="{{ asset('assets/img/mascot-sad.png') }}"
                    data-no="{{ asset('assets/img/mascot-no.png') }}"
                    data-crossed="{{ asset('assets/img/mascot-crossedhands.png') }}"
                >

                <img
                    class="mini-mascot mini-one"
                    src="{{ asset('assets/img/mascot-wow.png') }}"
                    alt="Mascot Wow"
                >

                <img
                    class="mini-mascot mini-two"
                    src="{{ asset('assets/img/mascot-normal.png') }}"
                    alt="Mascot Normal"
                >

                <div class="floating-card floating-a">
                    <strong>+20 XP</strong><br>
                    saat check-in positif
                </div>

                <div class="floating-card floating-b">
                    Progress kamu<br>
                    tersimpan otomatis
                </div>
            </div>
        </section>

        <div class="auth-backdrop" id="authBackdrop"></div>

        <div class="auth-modal" id="authModal" role="dialog" aria-modal="true">
        <section class="auth-panel">
            <button type="button" class="auth-close" id="authClose" aria-label="Tutup">&times;</button>
            <div class="auth-head">
                <img
                    id="authMascot"
                    src="{{ asset('assets/img/mascot-normal.png') }}"
                    alt="Mascot kecil"
                >

                <div>
                    <h2>Masuk RiseUp</h2>
                    <p id="authSubtitle">Lanjutkan perjalananmu hari ini.</p>
                </div>
            </div>

            <div class="tabs">
                <button type="button" class="tab-btn active" data-target="loginForm">Masuk</button>
                <button type="button" class="tab-btn" data-target="registerForm">Daftar</button>
            </div>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div id="loginForm" class="form-card active">
                <h3>Selamat datang kembali</h3>
                <p class="subtitle">Masuk untuk melihat dashboard, check-in, dan progress kamu.</p>

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <label>Email</label>
                    <div class="input-wrap">
                        <input
                            type="email"
                            name="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="contoh@email.com"
                        >
                        <span class="input-icon">✉</span>
                    </div>

                    <label>Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            name="password"
                            id="loginPassword"
                            required
                            placeholder="Masukkan password"
                        >
                        <span class="input-icon">●</span>
                    </div>

                    <button type="submit" class="submit-btn">Masuk</button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <a href="{{ route('auth.google.redirect') }}" class="google-btn">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Lanjutkan dengan Google</span>
                </a>
            </div>

            <div id="registerForm" class="form-card">
                <h3>Buat akun baru</h3>
                <p class="subtitle">Setelah daftar, kamu akan mengisi onboarding singkat terlebih dahulu.</p>

                <form method="POST" action="{{ route('register.process') }}">
                    @csrf

                    <label>Email</label>
                    <div class="input-wrap">
                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="contoh@email.com"
                        >
                        <span class="input-icon">✉</span>
                    </div>

                    <label>Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            name="password"
                            id="registerPassword"
                            minlength="8"
                            required
                            placeholder="Minimal 8 karakter"
                        >
                        <span class="input-icon">●</span>
                    </div>

                    <div class="password-meter">
                        <span id="passwordMeterBar"></span>
                    </div>

                    <div class="password-hint" id="passwordHint">
                        Gunakan minimal 8 karakter.
                    </div>

                    <button type="submit" class="submit-btn">Daftar dan mulai onboarding</button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <a href="{{ route('auth.google.redirect') }}" class="google-btn">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Daftar dengan Google</span>
                </a>
            </div>

            <div class="note">
                Login akun user. Kalau baru pertama kali, klik tab "Daftar" untuk membuat akun.
            </div>
        </section>
        </div>{{-- /.auth-modal --}}
    </main>
</div>

{{-- JS khusus halaman login --}}
<script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>