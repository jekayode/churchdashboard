<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

/**
 * The branch list for the app's sign-up screen.
 *
 * Open, because it is needed before anyone has an account — the same list the
 * public web form already renders, which is why nothing here is sensitive. It
 * returns id and name only: a branch row also carries a venue, contact details
 * and its pastor, none of which a sign-up screen needs and none of which should
 * be handed to anyone who asks.
 */
final class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Branch $branch): array => [
                'id' => $branch->id,
                'name' => $branch->name,
            ]);

        return response()->json(['data' => $branches]);
    }
}
