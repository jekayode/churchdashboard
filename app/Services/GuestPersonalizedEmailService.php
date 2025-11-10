<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class GuestPersonalizedEmailService
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Generate a personalized email message for a guest based on their registration data.
     */
    public function generatePersonalizedMessage(Member $member, Branch $branch): string
    {
        $name = $member->name;
        $prayerRequest = $member->prayer_request;
        $dateJoined = $member->date_joined;

        // Determine when they worshipped
        $worshipTime = $this->getWorshipTimePhrase($dateJoined);

        // Build the personalized message
        $message = "Dear {$name},\n\n";
        $message .= "Thank you for worshipping with us at {$branch->name} {$worshipTime}.\n\n";
        $message .= "I'm so glad that you fellowshipped with us. ";

        // Add prayer request if available
        if ($prayerRequest && trim($prayerRequest) !== '') {
            $message .= "I join my faith with yours concerning your request about {$prayerRequest}. ";
            $message .= "May the Lord show Himself mighty on your behalf and grant you peace and answers in Jesus' name.\n\n";
        } else {
            $message .= "May the Lord bless you richly, meet your heart desires, and cause this week to bring you divine favor and open doors.\n\n";
        }

        // Standard content
        $message .= "If you ever need further prayers or support, please don't hesitate to reach out. ";
        $message .= "If you belong to a LifeGroup, you can also share and pray along with your LifeGroup leader and members.\n\n";
        $message .= "Please don't forget that we are currently engaging in our 12 noon prayers every day this week. ";
        $message .= "The daily declarations have been added to this note for you—please take time to speak them over your life, even if you're not fasting. ";
        $message .= "Another declaration will be sent to you tomorrow. If you're not yet on our group, we'd love for you to join so that you can receive updates and prayer declarations.\n\n";
        $message .= "Wishing you the very best this week. May God bless and keep you.\n\n";
        $message .= "Warm regards,\n";
        $message .= "Emmanuel Joseph\n";
        $message .= $branch->name;

        return $message;
    }

    /**
     * Get a phrase describing when the guest worshipped.
     */
    private function getWorshipTimePhrase(?Carbon $dateJoined): string
    {
        if (! $dateJoined) {
            return 'recently';
        }

        $daysAgo = now()->diffInDays($dateJoined);

        if ($daysAgo === 0 || $daysAgo === 1) {
            return 'yesterday';
        } elseif ($daysAgo <= 7) {
            return 'last week';
        } else {
            return 'recently';
        }
    }

    /**
     * Send personalized emails to guests who registered in the previous week.
     */
    public function sendPersonalizedEmailsToRecentGuests(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        // Default to previous week (Monday to Sunday)
        if (! $startDate || ! $endDate) {
            $endDate = now()->startOfWeek()->subDay(); // Last Sunday
            $startDate = $endDate->copy()->startOfWeek(); // Last Monday
        }

        $results = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Get all guests who registered in the previous week
        $guests = Member::where('member_status', 'visitor')
            ->where('registration_source', 'guest-form')
            ->whereBetween('date_joined', [$startDate, $endDate])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->with('branch')
            ->get();

        $results['total'] = $guests->count();

        foreach ($guests as $guest) {
            try {
                if (! $guest->branch) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'guest' => $guest->name,
                        'email' => $guest->email,
                        'error' => 'Branch not found',
                    ];

                    continue;
                }

                // Check if branch has communication settings
                $branch = $guest->branch;
                if (! $branch->communicationSetting || ! $branch->communicationSetting->is_active) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'guest' => $guest->name,
                        'email' => $guest->email,
                        'error' => 'Communication not configured for branch',
                    ];

                    continue;
                }

                // Generate personalized message
                $message = $this->generatePersonalizedMessage($guest, $branch);
                $subject = "Thank you for worshipping with us at {$branch->name}";

                // Send email using CommunicationService
                $log = $this->communicationService->sendEmail(
                    $branch,
                    $guest->email,
                    $subject,
                    $message,
                    null, // No template
                    null, // No user (system generated)
                    [
                        'recipient_name' => $guest->name,
                        'recipient_email' => $guest->email,
                    ]
                );

                if ($log->status === 'sent') {
                    $results['sent']++;
                    Log::info('Personalized guest email sent', [
                        'guest_id' => $guest->id,
                        'email' => $guest->email,
                        'log_id' => $log->id,
                    ]);
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'guest' => $guest->name,
                        'email' => $guest->email,
                        'error' => $log->error_message ?? 'Unknown error',
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'guest' => $guest->name,
                    'email' => $guest->email ?? 'N/A',
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to send personalized guest email', [
                    'guest_id' => $guest->id,
                    'email' => $guest->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
