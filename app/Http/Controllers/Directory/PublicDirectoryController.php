<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\DirectoryAnnouncement;
use App\Models\DirectoryCategory;
use App\Models\DirectoryChangelogEntry;
use App\Models\DirectorySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PublicDirectoryController extends Controller
{
    public function landing(): View
    {
        $settings = DirectorySetting::instance();

        $announcements = DirectoryAnnouncement::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

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

        return view('directory.public.landing', compact('settings', 'announcements', 'categories', 'featured', 'recent'));
    }

    public function search(Request $request): View
    {
        $settings = DirectorySetting::instance();

        $query = Business::query()->publiclyVisible()->with('categories:id,name,slug');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($b) => $b->where('name', 'like', "%{$q}%")
                ->orWhere('tagline', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%"));
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $request->category));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        $businesses = $query->latest('approved_at')->paginate(12)->withQueryString();

        return view('directory.public.search', [
            'settings' => $settings,
            'query' => $request->get('q', ''),
            'category' => $request->get('category', ''),
            'city' => $request->get('city', ''),
            'businesses' => $businesses,
        ]);
    }

    public function category(string $slug): View
    {
        $category = DirectoryCategory::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $settings = DirectorySetting::instance();

        return view('directory.public.category', compact('category', 'settings'));
    }

    public function show(Business $business): View
    {
        abort_unless($business->isPubliclyVisible(), 404);

        $business->incrementViews();
        $business->load([
            'categories',
            'teamMembers',
            'services' => fn ($q) => $q->where('is_active', true),
            'products' => fn ($q) => $q->where('is_active', true),
            'posts' => fn ($q) => $q->published(),
            'reviews' => fn ($q) => $q->approved()->with(['user:id,name', 'reply']),
        ]);

        $settings = DirectorySetting::instance();

        $categoryIds = $business->categories->pluck('id')->all();

        $relatedBusinesses = Business::query()
            ->publiclyVisible()
            ->where('id', '!=', $business->id)
            ->when(count($categoryIds) > 0, fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('directory_categories.id', $categoryIds)))
            ->with(['categories:id,name,slug'])
            ->with('media')
            ->limit(4)
            ->get();

        return view('directory.public.show', compact('business', 'settings', 'relatedBusinesses'));
    }

    public function changelog(): View
    {
        $entries = DirectoryChangelogEntry::query()->published()->get();
        $settings = DirectorySetting::instance();

        return view('directory.public.changelog', compact('entries', 'settings'));
    }

    public function ownerDashboard(): View
    {
        return view('directory.owner.index');
    }

    public function ownerCreateBusiness(): View
    {
        $categories = DirectoryCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('directory.owner.create-business', compact('categories'));
    }

    public function ownerEditBusiness(Business $business): View
    {
        Gate::authorize('update', $business);

        $business->load([
            'categories',
            'teamMembers',
            'services',
            'products',
        ]);

        $categories = DirectoryCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $galleryMedia = $business->getMedia('gallery')->map(function ($media) {
            return ['id' => $media->id, 'url' => $media->getUrl()];
        })->values()->all();

        $teamMembersForm = $business->teamMembers->map(fn ($member) => [
            'id' => $member->id,
            'localKey' => (string) $member->id.'-existing',
            'name' => $member->name,
            'role' => $member->role,
            'bio' => $member->bio,
            'sort_order' => $member->sort_order,
            'photo_url' => $member->photo_url,
        ])->values()->all();

        $servicesForm = $business->services->map(fn ($service) => [
            'id' => $service->id,
            'localKey' => (string) $service->id.'-existing',
            'name' => $service->name,
            'description' => $service->description,
            'duration_text' => $service->duration_text,
            'price_text' => $service->price_text,
            'sort_order' => $service->sort_order,
            'is_active' => $service->is_active,
        ])->values()->all();

        $productsForm = $business->products->map(fn ($product) => [
            'id' => $product->id,
            'localKey' => (string) $product->id.'-existing',
            'name' => $product->name,
            'description' => $product->description,
            'price_text' => $product->price_text,
            'sort_order' => $product->sort_order,
            'is_active' => $product->is_active,
        ])->values()->all();

        return view('directory.owner.edit-business', [
            'business' => $business,
            'categories' => $categories,
            'coverUrl' => $business->getFirstMediaUrl('cover'),
            'logoUrl' => $business->getFirstMediaUrl('logo'),
            'galleryMedia' => $galleryMedia,
            'teamMembersForm' => $teamMembersForm,
            'servicesForm' => $servicesForm,
            'productsForm' => $productsForm,
            'categoryIds' => $business->categories->pluck('id')->values()->all(),
            'businessStatus' => $business->status?->value ?? $business->status,
            'workingHours' => $business->working_hours ?? [],
        ]);
    }

    public function account(): View
    {
        return view('directory.account.index');
    }

    public function messages(): View
    {
        return view('directory.account.messages');
    }

    public function messageThread(string $threadId): View
    {
        return view('directory.account.message-thread', compact('threadId'));
    }

    public function favorites(): View
    {
        return view('directory.account.favorites');
    }

    public function myReviews(): View
    {
        return view('directory.account.reviews');
    }
}
