<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Branch;
use App\Services\ChurchServiceManager;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Member;

final class EventController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ChurchServiceManager $serviceManager
    ) {}

    /**
     * Display a listing of events for public access (no authentication required).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $query = Event::with(['branch:id,name'])
                ->withCount(['registrations', 'reports'])
                ->where('is_published', true)
                ->where('start_date', '>=', now());

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'start_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $events = $query->paginate($perPage);

            // Add computed fields
            $events->getCollection()->transform(function ($event) {
                $event->is_upcoming = $event->isUpcoming();
                $event->is_published = $event->isPublished();
                $event->total_registrations = $event->registrations_count;
                $event->checked_in_count = $event->getCheckedInCountAttribute();
                return $event;
            });

            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving public events: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve events.',
            ], 500);
        }
    }

    /**
     * Display a listing of events (authenticated users).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Event::with(['branch:id,name'])
                ->withCount(['registrations', 'reports']);

            // Apply role-based filtering
            $user = Auth::user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'start_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $events = $query->paginate($perPage);

            // Add computed fields
            $events->getCollection()->transform(function ($event) {
                $event->is_upcoming = $event->isUpcoming();
                $event->is_published = $event->isPublished();
                $event->total_registrations = $event->registrations_count;
                $event->checked_in_count = $event->getCheckedInCountAttribute();
                return $event;
            });

            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Events retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving events: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve events.',
            ], 500);
        }
    }

    /**
     * Store a newly created event.
     */
    public function store(EventRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Transform validated data to match database schema
            $validatedData = $request->validated();
            $eventData = $this->transformEventData($validatedData);

            $event = Event::create($eventData);

            // Generate recurring instances if this is a recurring event
            if (!empty($validatedData['is_recurring'])) {
                $this->generateRecurringInstancesForEvent($event, $validatedData);
            }

            DB::commit();

            // Load relationships for response
            $event->load(['branch:id,name']);
            $event->loadCount(['registrations', 'reports']);

            Log::info('Event created successfully', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event created successfully.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating event: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create event.',
            ], 500);
        }
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        try {
            $event->load([
                'branch:id,name,venue',
                'registrations' => function ($query) {
                    $query->with(['user:id,name', 'member:id,name'])
                          ->orderBy('registration_date', 'desc');
                },
                'reports' => function ($query) {
                    $query->with('reporter:id,name')
                          ->orderBy('report_date', 'desc');
                }
            ]);

            // Add computed fields
            $event->is_upcoming = $event->isUpcoming();
            $event->is_published = $event->isPublished();
            
            // Registration statistics
            $totalRegistrations = $event->registrations->count();
            $checkedInCount = $event->registrations->where('checked_in', true)->count();
            $attendanceRate = $totalRegistrations > 0 ? round(($checkedInCount / $totalRegistrations) * 100, 2) : 0;
            
            $event->total_registrations = $totalRegistrations;
            $event->checked_in_count = $checkedInCount;
            $event->registrations_count = $totalRegistrations;
            $event->checkins_count = $checkedInCount;
            $event->attendance_rate = $attendanceRate . '%';

            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving event: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event.',
            ], 500);
        }
    }

    /**
     * Get event details for report forms.
     */
    public function getEventDetails(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        try {
            $eventDetails = [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'service_type' => $event->service_type,
                'event_type' => $event->type === 'service' ? 'Service' : ucfirst($event->type),
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'service_time' => $event->service_time,
                'service_end_time' => $event->service_end_time,
                'location' => $event->location,
                'branch_id' => $event->branch_id,
                'has_multiple_services' => $event->has_multiple_services,
                'second_service_time' => $event->second_service_time,
                'second_service_end_time' => $event->second_service_end_time,
            ];

            return response()->json([
                'success' => true,
                'data' => $eventDetails,
                'message' => 'Event details retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving event details: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event details.',
            ], 500);
        }
    }

    /**
     * Update the specified event.
     */
    public function update(EventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        try {
            DB::beginTransaction();

            // Transform validated data to match database schema
            $validatedData = $request->validated();
            $eventData = $this->transformEventData($validatedData);

            // Debug logging
            Log::info('Event update data transformation', [
                'event_id' => $event->id,
                'validated_data' => $validatedData,
                'transformed_data' => $eventData,
                'service_type_raw' => $validatedData['service_type'] ?? 'not_set',
                'service_type_transformed' => $eventData['service_type'] ?? 'not_set',
            ]);

            $event->update($eventData);

            DB::commit();

            // Load relationships for response
            $event->load(['branch:id,name']);
            $event->loadCount(['registrations', 'reports']);

            Log::info('Event updated successfully', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating event: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'request_data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update event.',
            ], 500);
        }
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        try {
            DB::beginTransaction();

            $event->delete();

            DB::commit();

            Log::info('Event deleted successfully', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting event: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event.',
            ], 500);
        }
    }

    /**
     * Register for an event.
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        $this->authorize('register', $event);

        try {
            // Validate the event is available for registration
            if (!$event->isPublished()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event is not available for registration.',
                ], 400);
            }

            // Basic validation - for authenticated users doing simple registration, use their profile info
            $user = Auth::user();
            $isSimpleRegistration = $event->registration_type === 'simple' && $user;
            
            if ($isSimpleRegistration) {
                // For simple registration by authenticated users, use their profile data
                $validator = Validator::make($request->all(), [
                    'custom_fields' => 'nullable|array',
                ]);
            } else {
                // For form registration or non-authenticated users, require name and email
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'nullable|string|max:20',
                    'custom_fields' => 'nullable|array',
                ]);
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check for duplicate registration
            if ($isSimpleRegistration) {
                // For simple registration, check by user_id
                $existingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->first();
            } else {
                // For form registration, check by email
                $existingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('email', $request->email)
                    ->first();
            }

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already registered for this event.',
                ], 409);
            }

            DB::beginTransaction();

            // Create registration
            if ($isSimpleRegistration) {
                // Use authenticated user's profile information
                $registration = EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'member_id' => $user->member?->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? $request->phone,
                    'custom_fields' => $request->custom_fields,
                    'registration_date' => now(),
                ]);
            } else {
                // Use form data
                $registration = EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'member_id' => Auth::user()?->member?->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'custom_fields' => $request->custom_fields,
                    'registration_date' => now(),
                ]);
            }

            DB::commit();

            Log::info('Event registration created successfully', [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $registration,
                'message' => 'Successfully registered for the event.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error registering for event: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register for event.',
            ], 500);
        }
    }

    /**
     * Get event registrations.
     */
    public function getRegistrations(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        try {
            $query = $event->registrations()
                ->with(['user:id,name,email,phone', 'member:id,name']);

            // Apply filters
            if ($request->has('status') && !empty($request->status)) {
                if ($request->status === 'checked_in') {
                    $query->where('checked_in', true);
                } elseif ($request->status === 'registered') {
                    $query->where('checked_in', false);
                }
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Apply date filters
            if ($request->has('date_filter') && !empty($request->date_filter)) {
                $now = now();
                switch ($request->date_filter) {
                    case 'today':
                        $query->whereDate('registration_date', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('registration_date', [
                            $now->startOfWeek()->toDateString(),
                            $now->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'month':
                        $query->whereMonth('registration_date', $now->month)
                              ->whereYear('registration_date', $now->year);
                        break;
                }
            }

            // Handle CSV export
            if ($request->has('export') && $request->export === 'csv') {
                $registrations = $query->get();
                return $this->exportRegistrationsCSV($registrations, $event);
            }

            // Calculate statistics before pagination
            $totalRegistrations = $query->count();
            $checkedInCount = (clone $query)->where('checked_in', true)->count();
            $pendingCount = $totalRegistrations - $checkedInCount;

            $statistics = [
                'total' => $totalRegistrations,
                'checked_in' => $checkedInCount,
                'pending' => $pendingCount,
                'attendance_rate' => $totalRegistrations > 0 ? round(($checkedInCount / $totalRegistrations) * 100, 2) : 0
            ];

            // Apply sorting
            $sortBy = $request->get('sort_by', 'registration_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $registrations = $query->paginate($perPage);

            // Add statistics to the response
            $response = $registrations->toArray();
            $response['statistics'] = $statistics;

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Event registrations retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving event registrations: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event registrations.',
            ], 500);
        }
    }

    /**
     * Check in a registration.
     */
    public function checkIn(Request $request, Event $event, EventRegistration $registration): JsonResponse
    {
        $this->authorize('update', $event);

        try {
            // Verify registration belongs to this event
            if ($registration->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration does not belong to this event.',
                ], 400);
            }

            if ($registration->checked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is already checked in.',
                ], 400);
            }

            $registration->checkIn();

            Log::info('Registration checked in successfully', [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $registration->fresh(),
                'message' => 'Registration checked in successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking in registration: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check in registration.',
            ], 500);
        }
    }

    /**
     * Get current user's event registrations.
     */
    public function getMyRegistrations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = EventRegistration::with(['event:id,name,start_date,end_date,location,status'])
                ->where('user_id', $user->id);

            // Apply filters
            if ($request->has('status') && !empty($request->status)) {
                $query->whereHas('event', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            if ($request->has('upcoming') && $request->upcoming !== '') {
                if ($request->boolean('upcoming')) {
                    $query->whereHas('event', function ($q) {
                        $q->where('start_date', '>', now());
                    });
                } else {
                    $query->whereHas('event', function ($q) {
                        $q->where('start_date', '<=', now());
                    });
                }
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'registration_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $registrations = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $registrations,
                'message' => 'User registrations retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving user registrations: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user registrations.',
            ], 500);
        }
    }

    /**
     * Get event statistics.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $query = Event::query();

            // Apply role-based filtering
            $user = Auth::user();
            if (!$user->isSuperAdmin()) {
                $userBranch = $user->getPrimaryBranch();
                if ($userBranch) {
                    $query->where('branch_id', $userBranch->id);
                }
            }

            // Apply filters (same as index method)
            $this->applyFilters($query, $request);

            $statistics = [
                'total_events' => $query->count(),
                'active_events' => (clone $query)->where('status', 'active')->count(),
                'upcoming_events' => (clone $query)->where('start_date', '>', now())->count(),
                'past_events' => (clone $query)->where('start_date', '<=', now())->count(),
                'total_registrations' => EventRegistration::whereIn('event_id', 
                    (clone $query)->pluck('id')
                )->count(),
                'total_checked_in' => EventRegistration::whereIn('event_id', 
                    (clone $query)->pluck('id')
                )->where('checked_in', true)->count(),
                'average_attendance' => $this->calculateAverageAttendance($query),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Event statistics retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving event statistics: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event statistics.',
            ], 500);
        }
    }

    /**
     * Apply filters to the events query.
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by branch
        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by type (service or event)
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Filter by service type (for church services)
        if ($request->has('service_type') && !empty($request->service_type)) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by date range (specific dates)
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('start_date', '<=', $request->end_date);
        }

        // Filter by date range (predefined periods)
        if ($request->has('date_filter') && !empty($request->date_filter)) {
            $dateFilter = $request->date_filter;
            $now = now();

            switch ($dateFilter) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'this_week':
                    $startOfWeek = $now->copy()->startOfWeek();
                    $endOfWeek = $now->copy()->endOfWeek();
                    $query->whereBetween('start_date', [$startOfWeek, $endOfWeek]);
                    break;
                case 'this_month':
                    $startOfMonth = $now->copy()->startOfMonth();
                    $endOfMonth = $now->copy()->endOfMonth();
                    $query->whereBetween('start_date', [$startOfMonth, $endOfMonth]);
                    break;
                case 'past':
                    $query->where('start_date', '<=', $now);
                    break;
            }
        }

        // Filter by recurring events
        if ($request->has('is_recurring')) {
            $query->where('is_recurring', $request->boolean('is_recurring'));
        }

        // Filter by public events
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }
    }

    /**
     * Transform frontend form data to database schema.
     */
    private function transformEventData(array $validatedData): array
    {
        $eventData = $validatedData;

        // Transform field names to match database schema
        if (isset($validatedData['start_date_time'])) {
            $eventData['start_date'] = $validatedData['start_date_time'];
            unset($eventData['start_date_time']);
        }

        if (isset($validatedData['end_date_time'])) {
            $eventData['end_date'] = $validatedData['end_date_time'];
            unset($eventData['end_date_time']);
        }

        // Handle recurring events frequency
        if (!empty($validatedData['is_recurring']) && !empty($validatedData['frequency'])) {
            $eventData['frequency'] = $validatedData['frequency'];
        } else {
            $eventData['frequency'] = 'once';
        }

        // Handle custom form fields JSON
        if (isset($validatedData['custom_form_fields']) && is_string($validatedData['custom_form_fields'])) {
            $eventData['custom_form_fields'] = json_decode($validatedData['custom_form_fields'], true);
        }

        // Handle boolean fields
        $eventData['is_public'] = $validatedData['is_public'] ?? false;
        $eventData['is_recurring'] = $validatedData['is_recurring'] ?? false;
        $eventData['has_multiple_services'] = $validatedData['has_multiple_services'] ?? false;

        return $eventData;
    }

    /**
     * Generate recurring instances for a recurring event.
     */
    private function generateRecurringInstancesForEvent(Event $parentEvent, array $validatedData): void
    {
        if (empty($validatedData['is_recurring']) || empty($validatedData['frequency'])) {
            return;
        }

        $frequency = $validatedData['frequency'];
        $dayOfWeek = $validatedData['day_of_week'] ?? null;
        $endDate = isset($validatedData['recurrence_end_date']) ? Carbon::parse($validatedData['recurrence_end_date']) : null;
        $maxOccurrences = $validatedData['max_occurrences'] ?? 52; // Default to 1 year

        $startDate = Carbon::parse($parentEvent->start_date);
        $endDateTime = $parentEvent->end_date ? Carbon::parse($parentEvent->end_date) : null;
        $duration = $endDateTime ? $startDate->diffInMinutes($endDateTime) : 60; // Default 1 hour

        $occurrenceCount = 0;
        $currentDate = $startDate->copy();

        // Move to the next occurrence based on frequency
        switch ($frequency) {
            case 'weekly':
                $currentDate->addWeek();
                break;
            case 'bi-weekly':
                $currentDate->addWeeks(2);
                break;
            case 'monthly':
                $currentDate->addMonth();
                break;
        }

        while ($occurrenceCount < $maxOccurrences) {
            // Check if we've reached the end date
            if ($endDate && $currentDate->isAfter($endDate)) {
                break;
            }

            // Adjust to the correct day of week if specified
            if ($dayOfWeek !== null && $currentDate->dayOfWeek !== $dayOfWeek) {
                $currentDate->next($dayOfWeek);
            }

            // Create the recurring instance
            $instanceData = $parentEvent->toArray();
            unset($instanceData['id'], $instanceData['created_at'], $instanceData['updated_at']);
            
            $instanceData['start_date'] = $currentDate->format('Y-m-d H:i:s');
            $instanceData['end_date'] = $endDateTime ? $currentDate->copy()->addMinutes($duration)->format('Y-m-d H:i:s') : null;
            $instanceData['parent_event_id'] = $parentEvent->id;
            $instanceData['is_recurring'] = false; // Instances are not recurring themselves

            Event::create($instanceData);

            $occurrenceCount++;

            // Move to next occurrence
            switch ($frequency) {
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'bi-weekly':
                    $currentDate->addWeeks(2);
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
            }
        }
    }

    /**
     * Calculate average attendance rate for events.
     */
    private function calculateAverageAttendance($query): string
    {
        $eventIds = (clone $query)->pluck('id');
        
        if ($eventIds->isEmpty()) {
            return '0%';
        }

        $totalRegistrations = EventRegistration::whereIn('event_id', $eventIds)->count();
        $totalCheckedIn = EventRegistration::whereIn('event_id', $eventIds)->where('checked_in', true)->count();

        if ($totalRegistrations === 0) {
            return '0%';
        }

        $percentage = round(($totalCheckedIn / $totalRegistrations) * 100, 1);
        return $percentage . '%';
    }

    /**
     * Export registrations as CSV.
     */
    private function exportRegistrationsCSV($registrations, Event $event)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="event-' . $event->id . '-registrations.csv"',
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email', 
                'Phone',
                'Registration Date',
                'Status',
                'Check-in Time'
            ]);

            // Add data rows
            foreach ($registrations as $registration) {
                fputcsv($file, [
                    $registration->id,
                    $registration->user?->name ?? $registration->name ?? 'N/A',
                    $registration->user?->email ?? $registration->email ?? 'N/A',
                    $registration->user?->phone ?? $registration->phone ?? 'N/A',
                    $registration->registration_date ? $registration->registration_date->format('Y-m-d H:i:s') : 'N/A',
                    $registration->checked_in ? 'Checked In' : 'Registered',
                    $registration->checkin_time ? $registration->checkin_time->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Create a Sunday service for a branch.
     */
    public function createSundayService(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_time' => 'required|date_format:H:i',
            'service_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'max_capacity' => 'nullable|integer|min:1',
            'registration_type' => 'in:none,simple,form,link',
            'registration_link' => 'nullable|url',
            'custom_form_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $branch = Branch::findOrFail($request->branch_id);
            
            // Check authorization for this branch
            if (!Auth::user()->isSuperAdmin() && Auth::user()->getPrimaryBranch()?->id !== $branch->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create services for this branch.',
                ], 403);
            }

            $serviceData = $validator->validated();
            unset($serviceData['branch_id']); // Remove branch_id as it's handled by the service

            $event = $this->serviceManager->createSundayService($branch, $serviceData);
            $event->load(['branch:id,name']);

            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Sunday service created successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating Sunday service: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Sunday service.',
            ], 500);
        }
    }

    /**
     * Create a midweek service for a branch.
     */
    public function createMidweekService(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'required|integer|min:0|max:6', // 0=Sunday, 6=Saturday
            'service_time' => 'required|date_format:H:i',
            'service_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'max_capacity' => 'nullable|integer|min:1',
            'registration_type' => 'in:none,simple,form,link',
            'registration_link' => 'nullable|url',
            'custom_form_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $branch = Branch::findOrFail($request->branch_id);
            
            // Check authorization for this branch
            if (!Auth::user()->isSuperAdmin() && Auth::user()->getPrimaryBranch()?->id !== $branch->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create services for this branch.',
                ], 403);
            }

            $serviceData = $validator->validated();
            unset($serviceData['branch_id']); // Remove branch_id as it's handled by the service

            $event = $this->serviceManager->createMidweekService($branch, $serviceData);
            $event->load(['branch:id,name']);

            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Midweek service created successfully.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating midweek service: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create midweek service.',
            ], 500);
        }
    }

    /**
     * Get all services for a branch.
     */
    public function getBranchServices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'include_instances' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $branch = Branch::findOrFail($request->branch_id);
            
            // Check authorization for this branch
            if (!Auth::user()->isSuperAdmin() && Auth::user()->getPrimaryBranch()?->id !== $branch->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view services for this branch.',
                ], 403);
            }

            $includeInstances = $request->boolean('include_instances', false);
            $services = $this->serviceManager->getBranchServices($branch, $includeInstances);

            return response()->json([
                'success' => true,
                'data' => $services,
                'message' => 'Branch services retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branch services: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branch services.',
            ], 500);
        }
    }

    /**
     * Generate recurring instances for all services.
     */
    public function generateRecurringInstances(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validator = Validator::make($request->all(), [
            'weeks_ahead' => 'integer|min:1|max:52',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $weeksAhead = $request->get('weeks_ahead', 12);
            $created = $this->serviceManager->generateAllRecurringInstances($weeksAhead);

            return response()->json([
                'success' => true,
                'data' => ['instances_created' => $created],
                'message' => "Generated {$created} recurring service instances.",
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating recurring instances: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recurring instances.',
            ], 500);
        }
    }

    /**
     * Get available service types for church services.
     */
    public function getServiceTypes(): JsonResponse
    {
        try {
            $serviceTypes = [
                'Sunday Service',
                'MidWeek',
                'Conferences',
                'Outreach',
                'Evangelism (Beautiful Feet)',
                'Water Baptism',
                'TECi',
                'Membership Class',
                'LifeGroup Meeting',
                'other'
            ];

            return response()->json([
                'success' => true,
                'data' => $serviceTypes,
                'message' => 'Service types retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving service types: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service types.',
            ], 500);
        }
    }

    /**
     * Get branches for dropdown (Super Admin only).
     */
    public function getBranches(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($user->isSuperAdmin()) {
                $branches = Branch::select('id', 'name', 'venue')
                    ->orderBy('name')
                    ->get();
            } else {
                // For non-super admins, return only their branch
                $userBranch = $user->getPrimaryBranch();
                $branches = $userBranch ? collect([$userBranch->only(['id', 'name', 'venue'])]) : collect([]);
            }

            return response()->json([
                'success' => true,
                'data' => $branches,
                'message' => 'Branches retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branches: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branches.',
            ], 500);
        }
    }

    /**
     * Cancel/delete an event registration.
     */
    public function unregister(Event $event, EventRegistration $registration): JsonResponse
    {
        try {
            // Check if the user owns this registration or has permission to delete it
            $user = Auth::user();
            
            if ($registration->user_id !== $user->id && !$user->can('view', $event)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cancel this registration.',
                ], 403);
            }

            // Check if the registration belongs to this event
            if ($registration->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration does not belong to this event.',
                ], 400);
            }

            DB::beginTransaction();

            $registration->delete();

            DB::commit();

            Log::info('Event registration cancelled successfully', [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration cancelled successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error cancelling event registration: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel registration.',
            ], 500);
        }
    }

    /**
     * Public event registration for non-authenticated users.
     * Creates user account and member record automatically.
     */
    public function publicRegister(Request $request, Event $event): JsonResponse
    {
        try {
            // Validate the event is available for registration
            if (!$event->isPublished()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event is not available for registration.',
                ], 400);
            }

            // Validate the event is upcoming
            if (!$event->isUpcoming()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is no longer available for this event.',
                ], 400);
            }

            // Validation for public registration - always require name and email
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'custom_fields' => 'nullable|array',
                'password' => 'nullable|string|min:8|confirmed', // Optional password for account creation
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check for duplicate registration by email
            $existingRegistration = EventRegistration::where('event_id', $event->id)
                ->where('email', $request->email)
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered for this event.',
                ], 409);
            }

            DB::beginTransaction();

            // Check if user already exists
            $user = User::where('email', $request->email)->first();
            $member = null;

            if (!$user) {
                // Create new user account
                $password = $request->password ?? Str::random(12); // Generate random password if not provided
                
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(), // Auto-verify for public registrations
                ]);

                // Create member record with "visitor" status
                $member = Member::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'branch_id' => $event->branch_id, // Associate with event's branch
                    'status' => 'visitor',
                    'date_joined' => now(),
                ]);

                Log::info('Public user account created for event registration', [
                    'user_id' => $user->id,
                    'member_id' => $member->id,
                    'event_id' => $event->id,
                    'email' => $request->email,
                ]);
            } else {
                // User exists, get their member record or create one
                $member = $user->member;
                
                if (!$member) {
                    $member = Member::create([
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? $request->phone,
                        'branch_id' => $event->branch_id,
                        'status' => 'visitor',
                        'date_joined' => now(),
                    ]);
                }
            }

            // Create event registration
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'member_id' => $member->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'custom_fields' => $request->custom_fields,
                'registration_date' => now(),
            ]);

            DB::commit();

            Log::info('Public event registration created successfully', [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'member_id' => $member->id,
            ]);

            // Check if user was just created
            $userWasCreated = $user->wasRecentlyCreated;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'registration' => $registration,
                    'user_created' => $userWasCreated,
                    'login_info' => [
                        'email' => $user->email,
                        'message' => $userWasCreated ? 'An account has been created for you. You can log in using your email address.' : 'Registration completed successfully.',
                    ]
                ],
                'message' => $userWasCreated ? 'Successfully registered for the event! An account has been created for you.' : 'Successfully registered for the event!',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in public event registration: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register for event. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate QR code for event registration.
     */
    public function generateQrCode(Event $event, EventRegistration $registration): JsonResponse
    {
        try {
            $this->authorize('view', $event);

            // Verify registration belongs to this event
            if ($registration->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found for this event.',
                ], 404);
            }

            // Generate secure QR code
            $qrCode = $registration->generateSecureQrCode(200);
            $checkInUrl = $registration->getSecureCheckInUrl();

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code' => base64_encode($qrCode),
                    'check_in_url' => $checkInUrl,
                    'registration_id' => $registration->id,
                    'attendee_name' => $registration->name,
                    'event_name' => $event->name,
                ],
                'message' => 'QR code generated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating QR code: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code.',
            ], 500);
        }
    }

    /**
     * Download QR code as PNG image.
     */
    public function downloadQrCode(Event $event, EventRegistration $registration): \Illuminate\Http\Response
    {
        try {
            $this->authorize('view', $event);

            // Verify registration belongs to this event
            if ($registration->event_id !== $event->id) {
                abort(404, 'Registration not found for this event.');
            }

            // Generate QR code as PNG
            $qrCode = $registration->generateSecureQrCode(300);
            
            $fileName = "ticket-{$event->id}-{$registration->id}.png";
            
            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } catch (\Exception $e) {
            Log::error('Error downloading QR code: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
            ]);

            abort(500, 'Failed to generate QR code.');
        }
    }

    /**
     * Public check-in endpoint for QR code scanning.
     */
    public function publicCheckIn(Request $request, EventRegistration $registration): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            // Validate the token if provided
            $token = $request->query('token');
            if ($token && !$registration->verifyCheckInToken($token)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid check-in token.',
                    ], 403);
                }
                
                return redirect()->route('public.events')->with('error', 'Invalid check-in token.');
            }

            // Check if already checked in
            if ($registration->checked_in) {
                $message = "Welcome back! You were already checked in at " . $registration->checked_in_at->format('Y-m-d H:i:s');
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'registration' => $registration,
                            'event' => $registration->event,
                            'already_checked_in' => true,
                        ],
                    ]);
                }
                
                return redirect()->route('public.events')->with('success', $message);
            }

            // Perform check-in
            $registration->checkIn();
            $registration->load('event');

            $message = "Successfully checked in to {$registration->event->name}!";
            
            Log::info('Public check-in successful', [
                'registration_id' => $registration->id,
                'event_id' => $registration->event_id,
                'attendee_name' => $registration->name,
                'checked_in_at' => $registration->checked_in_at,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'registration' => $registration,
                        'event' => $registration->event,
                        'checked_in_at' => $registration->checked_in_at,
                    ],
                ]);
            }
            
            return redirect()->route('public.events')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error in public check-in: ' . $e->getMessage(), [
                'registration_id' => $registration->id,
                'request_data' => $request->all(),
            ]);

            $message = 'Failed to check in. Please try again or contact support.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }
            
            return redirect()->route('public.events')->with('error', $message);
        }
    }

    /**
     * Show the QR code scanner interface.
     */
    public function showScanner(): \Illuminate\View\View
    {
        return view('public.check-in');
    }
}
