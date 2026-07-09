<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RiseUpUser;
use App\Models\GamificationStat;
use App\Models\OnboardingBaseline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class UserAuthController extends Controller
{
    /* ============================================================
       Halaman gabungan login + register user (dua tab di UI yang sama)
       ============================================================ */
    public function showLogin()
    {
        return view('auth.login');
    }

    /* ============================================================
       Register email/password → langsung ke onboarding baseline
       ============================================================ */
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|unique:usr_user,usr_email',
            'password' => 'required|min:8',
        ]);

        $user = RiseUpUser::create([
            'usr_email'         => $request->email,
            'usr_password_hash' => Hash::make($request->password),
            'usr_status'        => 'active',
        ]);

        GamificationStat::create([
            'gms_usr_id'    => $user->usr_id,
            'gms_total_xp'  => 0,
            'gms_level_num' => 1,
            'gms_weekly_xp' => 0,
            'gms_updated_at' => now(),
        ]);

        $this->startUserSession($user);

        return redirect()->route('onboarding.create');
    }

    /* ============================================================
       Login user email/password
       ============================================================ */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = RiseUpUser::where('usr_email', $request->email)
            ->where('usr_status', 'active')
            ->first();

        if (!$user || !$user->usr_password_hash) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar sebagai akun password. Kalau daftar via Google, klik tombol Google.',
            ])->withInput();
        }

        if (!Hash::check($request->password, $user->usr_password_hash)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput();
        }

        $this->startUserSession($user);

        return $this->afterLoginRedirect($user);
    }

    /* ============================================================
       Google OAuth — redirect ke Google
       ============================================================ */
    public function googleRedirect()
    {
        if (empty(config('services.google.client_id'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google belum dikonfigurasi. Hubungi admin.',
            ]);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /* ============================================================
       Google OAuth — callback: link/buat user lalu login
       ============================================================ */
    public function googleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google gagal: ' . $e->getMessage(),
            ]);
        }

        $googleId = $googleUser->getId();
        $email    = $googleUser->getEmail();
        $name     = $googleUser->getName();
        $avatar   = $googleUser->getAvatar();

        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google tidak memberi akses ke email.',
            ]);
        }

        // Urutan pencarian: (1) google_id, (2) email yang sama
        $user = RiseUpUser::where('usr_google_id', $googleId)->first()
             ?? RiseUpUser::where('usr_email', $email)->first();

        $isNew = false;

        if (!$user) {
            // Akun baru — buat + gamification
            $user = RiseUpUser::create([
                'usr_email'      => $email,
                'usr_google_id'  => $googleId,
                'usr_avatar_url' => $avatar,
                'usr_status'     => 'active',
            ]);

            GamificationStat::create([
                'gms_usr_id'    => $user->usr_id,
                'gms_total_xp'  => 0,
                'gms_level_num' => 1,
                'gms_weekly_xp' => 0,
                'gms_updated_at' => now(),
            ]);

            $isNew = true;
        } else {
            // Update Google fields kalau kosong
            $updates = [];
            if (empty($user->usr_google_id))  $updates['usr_google_id']  = $googleId;
            if (empty($user->usr_avatar_url)) $updates['usr_avatar_url'] = $avatar;
            if (!empty($updates)) $user->update($updates);
        }

        $this->startUserSession($user);

        // User baru via Google → langsung ke onboarding (tetap sama dgn flow register)
        if ($isNew) {
            return redirect()->route('onboarding.create');
        }

        return $this->afterLoginRedirect($user);
    }

    /* ============================================================
       Logout user
       ============================================================ */
    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }

    /* ---------- helpers ---------- */
    private function startUserSession(RiseUpUser $user): void
    {
        session([
            'auth_role'   => 'user',
            'auth_id'     => $user->usr_id,
            'auth_name'   => $user->usr_email,
            'auth_avatar' => $user->usr_avatar_url,
        ]);
    }

    private function afterLoginRedirect(RiseUpUser $user)
    {
        $hasOnboarding = OnboardingBaseline::where('bas_usr_id', $user->usr_id)->exists();

        if (!$hasOnboarding) {
            return redirect()->route('onboarding.create');
        }

        return redirect()->route('student.dashboard');
    }
}
