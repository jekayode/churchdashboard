<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Models\Member;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Resolves the Member profile attached to the authenticated user.
 *
 * Every `me/*` endpoint is scoped to this member, so a user without a member
 * profile gets a clear 404 rather than a null-reference further down.
 */
trait ResolvesCurrentMember
{
    protected function currentMember(Request $request): Member
    {
        $member = $request->user()?->member;

        if ($member === null) {
            throw new HttpException(404, 'No member profile is linked to this account.');
        }

        return $member;
    }
}
