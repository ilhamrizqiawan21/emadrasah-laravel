<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Check role authorization
        // Role hierarchy: superadmin > admin > guru > staff
        $roleHierarchy = [
            'superadmin' => ['superadmin', 'admin', 'guru', 'staff'],
            'admin' => ['admin', 'guru', 'staff'],
            'guru' => ['guru', 'staff'],
            'staff' => ['staff'],
        ];

        $allowedRoles = $roleHierarchy[$role] ?? [$role];

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
