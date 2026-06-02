<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Mengecek apakah role user sesuai dengan yang dibutuhkan
        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses');
        }

        // Mengecek apakah password default adalah NIP dan user belum diarahkan ke halaman ganti password
        $user = auth()->user();

        // Jika password masih default (NIP), arahkan pengguna untuk mengganti password
        if (Hash::check($user->nip, $user->password) && !$request->is('change-password')) {
            // Jika password masih default, arahkan ke halaman ganti password
            return redirect()->route('change-password');
        }

        // Melanjutkan request ke controller jika tidak ada pengalihan
        return $next($request);
    }
}