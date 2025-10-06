<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ministry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class MinisterDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ensure only ministry leaders access this
        if (! $user || ! $user->isMinistryLeader()) {
            abort(403);
        }

        $branchId = $user->getActiveBranchId();
        $ministry = null;

        if ($branchId && $user->member) {
            $ministry = Ministry::where('branch_id', $branchId)
                ->where('leader_id', $user->member->id)
                ->with(['departments' => function ($q) {
                    $q->withCount('members');
                }])
                ->first();
        }

        return view('minister.dashboard', [
            'ministry' => $ministry,
        ]);
    }
}
