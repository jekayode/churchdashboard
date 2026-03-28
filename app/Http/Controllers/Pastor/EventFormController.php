<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pastor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

final class EventFormController extends Controller
{
    use AuthorizesRequests;

    public function create(): View
    {
        $this->authorize('create', Event::class);

        $user = Auth::user();
        $isSuperAdmin = $user !== null && $user->isSuperAdmin();

        return view('pastor.events.form', [
            'isSuperAdmin' => $isSuperAdmin,
            'event' => null,
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $user = Auth::user();
        $isSuperAdmin = $user !== null && $user->isSuperAdmin();

        return view('pastor.events.form', [
            'isSuperAdmin' => $isSuperAdmin,
            'event' => $event,
        ]);
    }
}
