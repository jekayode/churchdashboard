<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

final class BranchScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admins can access all branches
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get the user's primary branch
        $userBranch = $user->getPrimaryBranch();

        if (!$userBranch) {
            // User has no branch assigned, redirect to dashboard with error
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any branch. Please contact your administrator.');
        }

        // Store the user's branch in the request for use in controllers
        $request->merge(['user_branch_id' => $userBranch->id]);

        // Check if the request is for a specific branch resource
        $routeParameters = $request->route()->parameters();
        
        // Look for branch_id in route parameters or model relationships
        foreach ($routeParameters as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getAttribute')) {
                $branchId = $parameter->getAttribute('branch_id');
                if ($branchId && $branchId !== $userBranch->id) {
                    abort(403, 'You do not have access to resources from this branch.');
                }
            }
        }

        return $next($request);
    }

    /**
     * Extract branch ID from the request parameters or route.
     */
    private function extractBranchIdFromRequest(Request $request): ?int
    {
        // Check route parameters for branch ID
        if ($request->route('branch')) {
            $branchParam = $request->route('branch');
            return is_numeric($branchParam) ? (int) $branchParam : null;
        }

        // Check query parameters for branch_id
        if ($request->has('branch_id')) {
            return (int) $request->get('branch_id');
        }

        // Check if the request contains a model with branch_id
        $routeParameters = $request->route()->parameters();
        
        foreach ($routeParameters as $parameter) {
            if (is_object($parameter) && isset($parameter->branch_id)) {
                return (int) $parameter->branch_id;
            }
        }

        return null;
    }

    /**
     * Check if a model belongs to the user's branch.
     */
    private function modelBelongsToUserBranch($model, int $userBranchId): bool
    {
        // If model has a branch_id property
        if (isset($model->branch_id)) {
            return $model->branch_id === $userBranchId;
        }

        // If model is a Branch itself
        if ($model instanceof \App\Models\Branch) {
            return $model->id === $userBranchId;
        }

        // If model belongs to a user (check user's branch)
        if (isset($model->user_id)) {
            $modelUser = \App\Models\User::find($model->user_id);
            if ($modelUser) {
                $modelUserBranch = $modelUser->getPrimaryBranch();
                return $modelUserBranch && $modelUserBranch->id === $userBranchId;
            }
        }

        // If model has a member relationship
        if (method_exists($model, 'member') && $model->member) {
            return $this->modelBelongsToUserBranch($model->member, $userBranchId);
        }

        return false;
    }
} 