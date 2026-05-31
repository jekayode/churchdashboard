<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuilderRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BuilderRegistrationAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BuilderRegistration::query()
            ->with(['user:id,name,email', 'contactedBy:id,name'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        $registrations = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $registrations,
        ]);
    }

    public function show(BuilderRegistration $registration): JsonResponse
    {
        $registration->load(['user:id,name,email,phone', 'contactedBy:id,name']);

        return response()->json([
            'success' => true,
            'data' => $registration,
        ]);
    }

    public function markContacted(BuilderRegistration $registration): JsonResponse
    {
        $registration->markContacted(auth()->user());

        return response()->json([
            'success' => true,
            'data' => $registration->fresh(['contactedBy:id,name']),
            'message' => 'Marked as contacted.',
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => BuilderRegistration::query()->count(),
                'new' => BuilderRegistration::query()->where('status', 'new')->count(),
                'contacted' => BuilderRegistration::query()->where('status', 'contacted')->count(),
            ],
        ]);
    }
}
