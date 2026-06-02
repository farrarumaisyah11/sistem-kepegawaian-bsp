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
     * Proses login & redirect berdasarkan role
     */
    public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials)) {
        $request->session()->regenerate();

      return match (auth()->user()->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'hcm'     => redirect()->route('hcm.dashboard'),
        'pegawai' => redirect()->route('pegawai.show', auth()->user()->nip),
        default   => redirect()->route('login'),
        };
    }

    return back()->withErrors([
        'username' => 'Username atau password salah',
    ]);
}
    /**
     * Proses logout
     */                                             
    public function logout(Request $request)
    {
        Auth::logout();                 // kalau kamu pakai Auth bawaan

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Berhasil logout');
    }
}
