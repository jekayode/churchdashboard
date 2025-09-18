<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        $hasRequiredRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            // Redirect to appropriate dashboard based on user's primary role
            $primaryRole = $user->getPrimaryRole();
            
            if (!$primaryRole) {
                return redirect()->route('dashboard')->with('error', 'Access denied. No valid role assigned.');
            }

            return redirect()->route('dashboard')->with('error', 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
