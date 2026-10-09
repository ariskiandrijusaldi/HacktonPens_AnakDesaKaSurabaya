<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('campuscare_token')) {
            return redirect()->route('dasbor');
        }

        return view('layouts.masuk');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:500'],
            'two_factor_code' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->post(
                    rtrim(config('services.campuscare.url'), '/').'/auth/login',
                    [
                        'username' => $validated['username'],
                        'password' => $validated['password'],
                        'totp_code' => $validated['two_factor_code'] ?? null,
                    ]
                );

            if ($response->status() === 401) {
                return back()->withInput($request->only('username'))
                    ->withErrors(['login' => 'Username atau password salah.']);
            }

            if ($response->status() === 429) {
                return back()->withInput($request->only('username'))
                    ->withErrors([
                        'login' => 'Terlalu banyak percobaan. Tunggu sebelum mencoba lagi.',
                    ]);
            }

            if (! $response->successful()) {
                return back()->withInput($request->only('username'))
                    ->withErrors(['login' => 'Login gagal. Periksa konfigurasi backend.']);
            }

            $data = $response->json('data') ?? $response->json();
            $token = $data['access_token'] ?? $data['token'] ?? null;

            if (! $token) {
                return back()->withErrors([
                    'login' => 'Backend tidak mengembalikan token autentikasi.',
                ]);
            }

            $request->session()->regenerate();
            session([
                'campuscare_token' => $token,
                'campuscare_user' => [
                    'username' => $data['username'] ?? null,
                    'role' => strtoupper((string) ($data['role'] ?? '')),
                ],
            ]);

            return redirect()->route('dasbor');

        } catch (\Throwable $e) {
            report($e);

            return back()->withInput($request->only('username'))
                ->withErrors(['login' => 'Backend login tidak dapat dihubungi.']);
        }
    }

    public function logout(Request $request)
    {
        // JWT bersifat stateless di backend, tidak ada endpoint logout.
        // Cukup hapus sesi lokal.
        session()->forget(['campuscare_token', 'campuscare_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }
}
