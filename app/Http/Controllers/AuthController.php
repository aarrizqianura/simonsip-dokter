<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        $demoUsers = User::with('dokter.spesialisasi')
            ->orderByRaw("FIELD(role, 'super_admin', 'admin_sdm', 'dokter')")
            ->limit(8)
            ->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau NIP/NIK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user());
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => 'Kredensial login (Username / NIP atau Password) tidak sesuai.',
            ]);
    }

    public function quickLogin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'exists:users,username'],
        ]);

        $user = User::where('username', $data['username'])->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user)
            ->with('success', 'Berhasil masuk sebagai ' . $user->name . ' (' . $user->role_label . ').');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', 'Password akun Anda berhasil diperbarui.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari sistem SIMONSIP.');
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        if ($user->role === 'dokter') {
            return redirect()->route('portal.dashboard');
        }

        return redirect()->route('dashboard');
    }
}

