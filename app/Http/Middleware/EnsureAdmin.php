<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            if ($request->expectsJson()) {
                abort(403, 'غير مصرح.');
            }

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'يجب تسجيل الدخول كأدمن.']);
        }

        return $next($request);
    }
}
