<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Access — RiseUp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <link rel="stylesheet" href="{{ asset('css/riseup-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <style>
        :root {
            --orange: #ff8a00; --orange-dark: #f97316; --orange-soft: #fff1d6;
            --cream: #fff7df; --cream-light: #fffdf5; --dark: #1f2937; --muted: #6b7280;
        }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(180deg, var(--cream) 0%, var(--cream-light) 100%);
            min-height: 100vh;
            display: grid; place-items: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        /* blob dekoratif lembut biar senada page biasa */
        body::before, body::after {
            content: ""; position: absolute; border-radius: 50%;
            filter: blur(8px); opacity: .5; z-index: 0;
        }
        body::before {
            width: 320px; height: 320px; top: -80px; left: -60px;
            background: radial-gradient(circle at 30% 30%, #ffe3a3, transparent 70%);
            animation: floatBlob 9s ease-in-out infinite;
        }
        body::after {
            width: 360px; height: 360px; bottom: -100px; right: -80px;
            background: radial-gradient(circle at 30% 30%, #ffd08a, transparent 70%);
            animation: floatBlob 11s ease-in-out infinite reverse;
        }
        @keyframes floatBlob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-24px)} }

        .admin-card {
            position: relative; z-index: 2;
            width: 100%; max-width: 420px;
            background: #ffffff;
            border: 1px solid rgba(255, 138, 0, .18);
            border-radius: 22px;
            padding: 36px 32px;
            box-shadow: 0 30px 80px rgba(249, 115, 22, 0.16);
            animation: adminIn .5s cubic-bezier(.2,.7,.2,1) both;
        }
        @keyframes adminIn { from { opacity:0; transform: translateY(20px);} to { opacity:1; transform:none;} }

        .admin-logo { height: 40px; margin-bottom: 18px; }

        .admin-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px;
            background: var(--orange-soft); color: var(--orange-dark);
            border-radius: 999px;
            font-size: 11px; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase;
            margin-bottom: 16px;
        }
        h1 { margin: 0 0 6px; font-size: 22px; color: var(--dark); }
        p.subtitle { margin: 0 0 22px; color: var(--muted); font-size: 14px; }

        label { display: block; font-size: 13px; font-weight: 600; margin: 12px 0 6px; color: #374151; }
        input[type=email], input[type=password] {
            width: 100%; padding: 12px 14px;
            border: 1px solid #d1d5db; border-radius: 10px;
            font-size: 14px; font-family: inherit;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        input:focus {
            outline: none; border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(255, 138, 0, .18);
        }
        .admin-submit {
            width: 100%; padding: 12px 18px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: #fff; border: none; border-radius: 10px;
            font-weight: 700; font-size: 15px;
            margin-top: 20px; cursor: pointer;
            transition: transform .15s ease, box-shadow .2s ease;
        }
        .admin-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(249, 115, 22, 0.3);
        }
        .error {
            background: #fee2e2; color: #991b1b;
            padding: 10px 14px; border-radius: 10px;
            font-size: 13px; margin-bottom: 12px;
        }
        .footer-note {
            margin-top: 22px; text-align: center;
            font-size: 12px; color: #9ca3af;
        }
        .footer-note a { color: #6b7280; text-decoration: none; }
        .footer-note a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="admin-card">
    <img class="admin-logo" src="{{ asset('assets/img/logo-riseup.png') }}" alt="RiseUp">
    <span class="admin-badge">🔐 Restricted Access</span>
    <h1>Admin Portal</h1>
    <p class="subtitle">Halaman ini khusus admin RiseUp. Silakan masuk dengan kredensial admin.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.process') }}">
        @csrf

        <label>Email Admin</label>
        <input type="email" name="email" required value="{{ old('email') }}" placeholder="admin@riseup.test">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Masukkan password admin">

        <button type="submit" class="admin-submit">Masuk sebagai Admin</button>
    </form>

    <p class="footer-note">
        Bukan admin? <a href="{{ route('login') }}">← Kembali ke login user</a>
    </p>
</div>

    <script src="{{ asset('js/animations.js') }}"></script>
</body>
</html>
