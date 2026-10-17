<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(
                Auth::user()->is_admin ? 'admin.dashboard' : 'search'
            );
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('name', $data['username'])
            ->orWhere('email', $data['username'])
            ->first();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $data['password']], true)) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'بيانات الدخول غير صحيحة.']);
        }

        $request->session()->regenerate();

        if (Auth::user()->is_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('search'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
