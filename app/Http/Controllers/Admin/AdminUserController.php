<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $planKeys = array_keys(config('plans', []));
        $isAdmin = $request->boolean('is_admin');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'plan' => [$isAdmin ? 'nullable' : 'required', 'string', Rule::in($planKeys)],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => $isAdmin,
            'plan' => $data['plan'] ?? null,
            'usage_count' => 0,
            'usage_period' => now()->format('Y-m'),
        ]);

        return back()->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function updatePlan(Request $request, User $user): RedirectResponse
    {
        $planKeys = array_keys(config('plans', []));

        $data = $request->validate([
            'plan' => ['nullable', 'string', Rule::in($planKeys)],
        ]);

        $user->update([
            'plan' => $data['plan'] ?: null,
        ]);

        return back()->with('success', 'تم تحديث باقة المستخدم.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'لا يمكنك حذف حسابك الحالي.']);
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم.');
    }
}
