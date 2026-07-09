<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User - RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/admin-user-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

<div class="page">
    <header class="topbar">
        <div>
            <p class="eyebrow">Admin Panel</p>
            <h1>Tambah User</h1>
            <p class="subtitle">Buat akun user baru dan hubungkan langsung ke database RiseUp.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Kembali
        </a>
    </header>

    <main class="layout">
        <section class="info-card">
            <div class="icon-badge">＋</div>
            <h2>User Baru</h2>
            <p>
                User yang dibuat dari halaman ini akan masuk ke tabel <b>usr_user</b>,
                lalu data XP awal dibuat di tabel gamification.
            </p>

            <ul>
                <li>Email harus unik.</li>
                <li>Password minimal 8 karakter.</li>
                <li>Status active bisa langsung login.</li>
                <li>Status inactive tidak bisa login sebagai user.</li>
            </ul>
        </section>

        <section class="form-card">
            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" id="adminUserForm">
                @csrf

                <div class="field">
                    <label>Email User</label>
                    <input
                        type="email"
                        name="email"
                        required
                        value="{{ old('email') }}"
                        placeholder="userbaru@riseup.test"
                    >
                </div>

                <div class="field">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        id="passwordInput"
                        required
                        minlength="8"
                        placeholder="Minimal 8 karakter"
                    >

                    <div class="password-meter">
                        <span id="passwordMeterBar"></span>
                    </div>

                    <p class="hint" id="passwordHint">
                        Gunakan minimal 8 karakter.
                    </p>
                </div>

                <div class="field">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan User
                </button>
            </form>
        </section>
    </main>
</div>

<script src="{{ asset('js/admin-user-form.js') }}"></script>
    @include('partials.footer')

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>