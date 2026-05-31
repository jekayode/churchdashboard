<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\DirectoryCategory;
use App\Models\DirectoryChangelogEntry;
use App\Models\DirectorySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicBizController extends Controller
{
    public function landing(): JsonResponse
    {
        $settings = DirectorySetting::instance();

        $categories = DirectoryCategory::query()
            ->where('is_active', true)
            ->withCount(['businesses' => fn ($q) => $q->publiclyVisible()])
            ->orderByDesc('businesses_count')
            ->limit(8)
            ->get();

        $featured = Business::query()
            ->publiclyVisible()
            ->featured()
            ->with('categories:id,name,slug')
            ->latest('approved_at')
            ->limit(6)
            ->get();

        $recent = Business::query()
            ->publiclyVisible()
            ->with('categories:id,name,slug')
            ->latest('approved_at')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => array_merge($settings->toArray(), ['logo_url' => $settings->logo_url]),
                'categories' => $categories,
                'featured' => $featured,
                'recent' => $recent,
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = Business::query()
            ->publiclyVisible()
            ->with('categories:id,name,slug');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($b) => $b->where('name', 'like', "%{$q}%")
                ->orWhere('tagline', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%"));
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $request->category));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'rating' => $query->orderByDesc('average_rating'),
            'popular' => $query->orderByDesc('views_count'),
            default => $query->latest('approved_at'),
        };

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->integer('per_page', 12)),
        ]);
    }

    public function category(string $slug): JsonResponse
    {
        $category = DirectoryCategory::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $businesses = $category->businesses()
            ->publiclyVisible()
            ->with('categories:id,name,slug')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => ['category' => $category, 'businesses' => $businesses],
        ]);
    }

    public function show(Business $business): JsonResponse
    {
        abort_unless($business->isPubliclyVisible(), 404);

        $business->incrementViews();
        $business->load([
            'categories',
            'teamMembers',
            'services' => fn ($q) => $q->where('is_active', true),
            'products' => fn ($q) => $q->where('is_active', true),
            'posts' => fn ($q) => $q->published(),
            'reviews' => fn ($q) => $q->approved()->with('user:id,name'),
        ]);

        return response()->json(['success' => true, 'data' => $business]);
    }

    public function changelog(): JsonResponse
    {
        $entries = DirectoryChangelogEntry::query()->published()->get();

        return response()->json(['success' => true, 'data' => $entries]);
    }
}
