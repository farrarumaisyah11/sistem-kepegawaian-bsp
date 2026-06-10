<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login & redirect berdasarkan role yang tersimpan di database.
     *
     * Penting:
     * - Kalau user masuk dari link QR approval, Laravel menyimpan URL tujuan sebagai "intended".
     * - Setelah login berhasil, redirect()->intended() akan mengembalikan user ke halaman approval.
     * - Kalau login biasa, baru diarahkan berdasarkan role yang tersimpan di database.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            return redirect()->intended($this->redirectByRole($user));
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => 'Username atau password salah',
            ]);
    }

    /**
     * Redirect default berdasarkan role dari database.
     * Ini hanya dipakai kalau user login biasa, bukan dari halaman QR approval.
     */
    private function redirectByRole($user): string
    {
        $role = $user->role ?? null;

        return match ($role) {
            'admin' => route('admin.dashboard'),

            'hcm' => route('hcm.dashboard'),

            /*
            |----------------------------------------------------------------------
            | Kalau nanti role pegawai sudah dipakai, redirect ini tetap aman.
            | Kalau sekarang belum ada role pegawai di DB, bagian ini tidak akan kepakai.
            |----------------------------------------------------------------------
            */
            'pegawai' => !empty($user->nip)
                ? route('pegawai.show', $user->nip)
                : route('pegawai.saya'),

            /*
            |----------------------------------------------------------------------
            | Untuk role lain yang tersimpan di DB, misalnya manager:
            | - Kalau login dari QR approval, akan tetap kembali ke halaman approval
            |   karena redirect()->intended().
            | - Kalau login biasa dan belum punya dashboard khusus, arahkan ke root.
            |----------------------------------------------------------------------
            */
            default => url('/'),
        };
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Berhasil logout');
    }
}