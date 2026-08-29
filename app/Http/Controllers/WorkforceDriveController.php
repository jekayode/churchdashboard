<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkforceDriveSignupRequest;
use App\Mail\WorkforceDriveThankYouMail;
use App\Models\WorkforceDriveSignup;
use App\Models\WorkforceDriveTeam;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * The Workforce Drive.
 *
 * Three public surfaces, none behind auth: a join form people open on their
 * phones, the projector leaderboard that watches the counts climb, and the
 * JSON state the leaderboard polls. Nothing here is secret — the whole point
 * is that anyone in the room can join and everyone can watch.
 */
final class WorkforceDriveController extends Controller
{
    /**
     * The join form.
     */
    public function join(): View
    {
        return view('workforce-drive.join', [
            'teams' => WorkforceDriveTeam::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Record a sign-up, email a thank-you, then send them to the WhatsApp page.
     */
    public function store(StoreWorkforceDriveSignupRequest $request): RedirectResponse
    {
        $signup = WorkforceDriveSignup::create($request->validated());
        $teamName = $signup->team->name;

        // Sent inline (onConnection('sync')) rather than queued, so it goes out
        // even when no queue worker is running — and wrapped so a mail failure
        // never breaks the public response (same guard the event flow uses).
        try {
            $mail = (new WorkforceDriveThankYouMail(
                recipientName: $signup->name,
                teamName: $teamName,
                whatsappUrl: config('services.workforce_drive.whatsapp_url'),
            ))->onConnection('sync');

            Mail::to($signup->email)->send($mail);
        } catch (\Throwable $e) {
            Log::warning('Workforce Drive thank-you email failed: '.$e->getMessage(), [
                'signup_id' => $signup->id,
            ]);
        }

        return redirect()
            ->route('workforce-drive.joined')
            ->with('joined_team', $teamName);
    }

    /**
     * The thank-you page, with the WhatsApp group invite. Reached only right
     * after a submit — a direct hit with no flash goes back to the form.
     */
    public function joined(): View|RedirectResponse
    {
        $teamName = session('joined_team');

        if ($teamName === null) {
            return redirect()->route('workforce-drive.join');
        }

        return view('workforce-drive.joined', [
            'teamName' => $teamName,
            'whatsappUrl' => config('services.workforce_drive.whatsapp_url'),
        ]);
    }

    /**
     * The projector slide. The QR is rendered once into the page so the way in
     * never depends on a poll succeeding; only the counts refresh after this.
     */
    public function leaderboard(): View
    {
        $joinUrl = route('workforce-drive.join');

        return view('workforce-drive.leaderboard', [
            'joinUrl' => $joinUrl,
            'readableJoinUrl' => preg_replace('~^https?://~', '', $joinUrl),
            'qr' => (string) QrCode::format('svg')
                ->size(320)
                ->margin(1)
                ->errorCorrection('M')
                ->style('square')
                ->backgroundColor(255, 255, 255)
                ->color(17, 17, 17)
                ->generate($joinUrl),
        ]);
    }

    /**
     * The polled state: teams ranked by joinee count, plus the running total.
     */
    public function leaderboardState(): JsonResponse
    {
        $teams = WorkforceDriveTeam::active()
            ->withCount('signups')
            ->orderByDesc('signups_count')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (WorkforceDriveTeam $team): array => [
                'id' => $team->id,
                'name' => $team->name,
                'core_value' => $team->core_value,
                'sponsor_name' => $team->sponsor_name,
                'color' => $team->color,
                'count' => $team->signups_count,
            ]);

        return response()->json([
            'teams' => $teams,
            'total' => $teams->sum('count'),
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
