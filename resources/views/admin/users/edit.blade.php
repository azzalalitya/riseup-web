<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User - RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/admin-user-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
</head>
<body>

<div class="page">
    <header class="topbar">
        <div>
            <p class="eyebrow">Admin Panel</p>
            <h1>Edit User</h1>
            <p class="subtitle">Perbarui email, status, atau password user.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            ← Kembali
        </a>
    </header>

    <main class="layout">
        <section class="info-card">
            <div class="icon-badge">✎</div>
            <h2>{{ $user->usr_email }}</h2>
            <p>
                Jika password dikosongkan, password lama tetap digunakan.
                Gunakan status inactive untuk menonaktifkan akses user.
            </p>

            <ul>
                <li>Email tetap harus unik.</li>
                <li>Password baru opsional.</li>
                <li>Status active bisa login.</li>
                <li>Status inactive tidak bisa login.</li>
            </ul>
        </section>

        <section class="form-card">
            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user->usr_id) }}" id="adminUserForm">
                @csrf
                @method('PUT')

                <div class="field">
                    <label>Email User</label>
                    <input
                        type="email"
                        name="email"
                        required
                        value="{{ old('email', $user->usr_email) }}"
                    >
                </div>

                <div class="field">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active" {{ old('status', $user->usr_status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->usr_status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="field">
                    <label>Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        id="passwordInput"
                        minlength="8"
                        placeholder="Kosongkan kalau tidak diganti"
                    >

                    <div class="password-meter">
                        <span id="passwordMeterBar"></span>
                    </div>

                    <p class="hint" id="passwordHint">
                        Kosongkan jika tidak ingin mengganti password.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update User
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