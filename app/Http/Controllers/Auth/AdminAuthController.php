<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /* ============================================================
       Halaman login admin (URL "tersembunyi" /admin-access)
       ============================================================ */
    public function showLogin()
    {
        return view('auth.admin_login');
    }

    /* ============================================================
       Proses login admin
       ============================================================ */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('adm_email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->adm_password_hash)) {
            return back()->withErrors([
                'email' => 'Email atau password admin salah.',
            ])->withInput();
        }

        session([
            'auth_role' => 'admin',
            'auth_id'   => $admin->adm_id,
            'auth_name' => $admin->adm_name ?? 'Admin RiseUp',
        ]);

        return redirect()->route('admin.dashboard');
    }

    /* ============================================================
       Logout admin (kembali ke halaman login admin)
       ============================================================ */
    public function logout()
    {
        session()->flush();
        return redirect()->route('admin.login');
    }
}
