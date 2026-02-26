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

        // #region agent log
        try {
            $path = '/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log';
            $dir = dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $payload = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'E',
                'location' => 'app/Http/Middleware/RoleBasedAccess.php:handle:write-test',
                'message' => 'Debug log write test (pre-flight)',
                'data' => [
                    'path' => $request->path(),
                    'debug_dir_exists' => is_dir($dir),
                    'debug_dir_writable' => is_writable($dir),
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);

            @file_put_contents($path, $payload.PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        // #region agent log
        try {
            file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'A',
                'location' => 'app/Http/Middleware/RoleBasedAccess.php:handle:entry',
                'message' => 'Role middleware check (entry)',
                'data' => [
                    'path' => $request->path(),
                    'required_roles' => $roles,
                    'user_id' => $user?->id,
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        if (! $user) {
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

        if (! $hasRequiredRole) {
            // #region agent log
            try {
                file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Http/Middleware/RoleBasedAccess.php:handle:deny',
                    'message' => 'Role middleware denied request',
                    'data' => [
                        'path' => $request->path(),
                        'required_roles' => $roles,
                        'user_id' => $user->id,
                        'primary_role' => $user->getPrimaryRole()?->name,
                    ],
                    'timestamp' => (int) round(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
            } catch (\Throwable) {
            }
            // #endregion

            // Redirect to appropriate dashboard based on user's primary role
            $primaryRole = $user->getPrimaryRole();

            if (! $primaryRole) {
                return redirect()->route('dashboard')->with('error', 'Access denied. No valid role assigned.');
            }

            return redirect()->route('dashboard')->with('error', 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}
