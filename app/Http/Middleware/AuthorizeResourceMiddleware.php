<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class AuthorizeResourceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $ability = 'viewAny', ?string $model = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If a specific model is provided, check authorization for that model
        if ($model) {
            $modelClass = "App\\Models\\{$model}";
            
            if (class_exists($modelClass)) {
                // For resource routes, try to get the model instance from route parameters
                $modelInstance = null;
                $routeParameters = $request->route()->parameters();
                
                // Look for a parameter that matches the model name (singular)
                $parameterName = strtolower($model);
                if (isset($routeParameters[$parameterName])) {
                    $parameterValue = $routeParameters[$parameterName];
                    
                    // If it's already a model instance, use it
                    if (is_object($parameterValue) && $parameterValue instanceof $modelClass) {
                        $modelInstance = $parameterValue;
                    } 
                    // If it's a string/ID, try to resolve the model
                    elseif (is_string($parameterValue) || is_numeric($parameterValue)) {
                        try {
                            $modelInstance = $modelClass::findOrFail($parameterValue);
                        } catch (\Exception $e) {
                            // Model not found, let the route handle it
                            return $next($request);
                        }
                    }
                }
                
                // Check authorization
                if ($modelInstance) {
                    // For specific model instances
                    if (!Gate::forUser($user)->allows($ability, $modelInstance)) {
                        abort(403, 'This action is unauthorized.');
                    }
                } else {
                    // For model class (e.g., viewAny, create)
                    if (!Gate::forUser($user)->allows($ability, $modelClass)) {
                        abort(403, 'This action is unauthorized.');
                    }
                }
            }
        }

        return $next($request);
    }
} 