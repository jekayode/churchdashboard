<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\ChurchProject;
use App\Models\GivingAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GivingController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * Everything the app's Give tab needs: where to transfer, what the church is
     * currently raising for, and the declaration read when giving.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $accounts = GivingAccount::query()
            ->active()
            ->where('branch_id', $member->branch_id)
            ->orderBy('sort_order')
            ->get();

        $projects = ChurchProject::query()
            ->active()
            ->where('branch_id', $member->branch_id)
            ->with('givingAccount')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts->map(fn (GivingAccount $account): array => [
                    'id' => $account->id,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                    'bank_name' => $account->bank_name,
                    'purpose' => $account->purpose,
                    'brand_color' => $account->brand_color,
                ])->values(),

                'projects' => $projects->map(fn (ChurchProject $project): array => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'period' => $project->period,
                    'account' => $project->givingAccount === null ? null : [
                        'account_name' => $project->givingAccount->account_name,
                        'account_number' => $project->givingAccount->account_number,
                        'bank_name' => $project->givingAccount->bank_name,
                    ],
                ])->values(),

                // Members are asked to put the project name in the transfer
                // narration so the church can attribute gifts.
                'instructions' => $projects->isNotEmpty()
                    ? 'When giving towards a project, put the project name in the transfer description so it can be tracked.'
                    : null,

                'declaration' => $member->branch?->giving_declaration,
            ],
        ]);
    }
}
