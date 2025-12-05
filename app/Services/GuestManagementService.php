<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\GuestFollowUp;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class GuestManagementService
{
    /**
     * Get paginated guest list with filtering.
     */
    public function getGuests(?int $branchId = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Member::guests()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filtering
        $this->applyDateFilters($query, $filters);

        // Filter by staying intention
        if (!empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        // Filter by discovery source
        if (!empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        // Filter by gender
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Search by name, email, or phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated member list with filtering (includes all members, not just guests).
     */
    public function getMembers(?int $branchId = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Member::query()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filtering
        $this->applyDateFilters($query, $filters);

        // Filter by staying intention
        if (!empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        // Filter by discovery source
        if (!empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        // Filter by gender
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Filter by member status
        if (!empty($filters['member_status'])) {
            $query->where('member_status', $filters['member_status']);
        }

        // Search by name, email, or phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Apply date filters to query based on filter type.
     */
    protected function applyDateFilters($query, array $filters): void
    {
        // Check for predefined date ranges first
        if (!empty($filters['date_range'])) {
            $this->applyPredefinedDateRange($query, $filters['date_range']);
            return;
        }

        // Fall back to custom date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }

    /**
     * Apply predefined date range filters.
     */
    protected function applyPredefinedDateRange($query, string $range): void
    {
        $now = Carbon::now();

        match ($range) {
            'last_week' => $query->where('created_at', '>=', $now->copy()->subWeek()->startOfWeek())
                ->where('created_at', '<=', $now->copy()->endOfWeek()),
            'last_month' => $query->where('created_at', '>=', $now->copy()->subMonth()->startOfMonth())
                ->where('created_at', '<=', $now->copy()->endOfMonth()),
            'last_quarter' => $query->where('created_at', '>=', $now->copy()->subQuarter()->startOfQuarter())
                ->where('created_at', '<=', $now->copy()->endOfQuarter()),
            default => null,
        };
    }

    /**
     * Get full guest details with relationships.
     */
    public function getGuestDetails(int $memberId): ?Member
    {
        return Member::guests()
            ->with([
                'branch:id,name',
                'user:id,name,email,phone',
                'followUps' => function ($query) {
                    $query->orderBy('contact_date', 'desc')
                        ->with(['createdBy:id,name', 'assignedTo:id,name']);
                },
                'statusHistory' => function ($query) {
                    $query->orderBy('changed_at', 'desc')
                        ->with(['changedBy:id,name']);
                },
                'prayerRequests' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ])
            ->find($memberId);
    }

    /**
     * Update guest status using the existing changeStatus method.
     */
    public function updateGuestStatus(Member $member, string $newStatus, ?string $reason = null, ?string $notes = null): bool
    {
        // Validate that member is a guest
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            return false;
        }

        // Validate that new status is not 'visitor'
        if ($newStatus === 'visitor') {
            return false;
        }

        // Use the existing changeStatus method
        return $member->changeStatus(
            $newStatus,
            $reason,
            $notes,
            auth()->id()
        );
    }

    /**
     * Add a follow-up note for a guest.
     */
    public function addFollowUp(Member $member, array $data): GuestFollowUp
    {
        // Validate that member is a guest
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            throw new \InvalidArgumentException('Member is not a guest');
        }

        // Set created_by to current user
        $data['member_id'] = $member->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        // Parse dates if provided
        if (!empty($data['contact_date']) && is_string($data['contact_date'])) {
            $data['contact_date'] = Carbon::parse($data['contact_date']);
        }

        if (!empty($data['next_follow_up_date']) && is_string($data['next_follow_up_date'])) {
            $data['next_follow_up_date'] = Carbon::parse($data['next_follow_up_date']);
        }

        return GuestFollowUp::create($data);
    }

    /**
     * Export guests data to CSV or Excel.
     */
    public function exportGuests(?int $branchId, array $filters, string $format = 'csv'): Collection
    {
        $query = Member::guests()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->withCount('followUps')
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filtering
        $this->applyDateFilters($query, $filters);

        if (!empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        if (!empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Export members data to CSV or Excel.
     */
    public function exportMembers(?int $branchId, array $filters, string $format = 'csv'): Collection
    {
        $query = Member::query()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->withCount('followUps')
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filtering
        $this->applyDateFilters($query, $filters);

        if (!empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        if (!empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['member_status'])) {
            $query->where('member_status', $filters['member_status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get export data formatted for CSV/Excel.
     */
    public function formatExportData(Collection $guests): array
    {
        return $guests->map(function ($guest) {
            return [
                'Name' => $guest->name,
                'Email' => $guest->email ?? '',
                'Phone' => $guest->phone ?? '',
                'Branch' => $guest->branch?->name ?? 'Unknown',
                'Registration Date' => $guest->created_at?->format('Y-m-d H:i:s') ?? '',
                'Gender' => $guest->gender ? ucfirst($guest->gender) : '',
                'Age Group' => $guest->age_group ?? '',
                'Marital Status' => $guest->marital_status ? ucfirst(str_replace('_', ' ', $guest->marital_status)) : '',
                'Discovery Source' => $guest->discovery_source ?? '',
                'Staying Intention' => $guest->staying_intention ?? '',
                'Prayer Request' => $guest->prayer_request ?? '',
                'Status' => ucfirst($guest->member_status),
                'Follow-up Count' => $guest->follow_ups_count ?? 0,
                'Home Address' => $guest->home_address ?? '',
                'Preferred Call Time' => $guest->preferred_call_time ?? '',
                'Closest Location' => $guest->closest_location ?? '',
                'Additional Info' => $guest->additional_info ?? '',
            ];
        })->toArray();
    }
}

