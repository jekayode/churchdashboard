<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

final class PublicEventController extends Controller
{
    /**
     * Public event detail page (HTML).
     */
    public function show(string $branchCode, string $eventSlug): View
    {
        $event = Event::findPubliclyVisibleByBranchCodeAndSlug($branchCode, $eventSlug);

        return view('public.events.show', [
            'event' => $event,
        ]);
    }
}
