<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberEventResource;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EventController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * Upcoming events for the member's branch.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $registeredEventIds = EventRegistration::query()
            ->where('member_id', $member->id)
            ->pluck('event_id')
            ->all();

        $events = Event::query()
            ->published()
            ->upcoming()
            ->byBranch($member->branch_id)
            ->with('branch')
            ->withCount('registrations')
            ->orderBy('start_date')
            ->paginate((int) $request->integer('per_page', 20));

        $events->getCollection()->transform(function (Event $event) use ($registeredEventIds): Event {
            $event->is_registered = in_array($event->id, $registeredEventIds, true);

            return $event;
        });

        return response()->json([
            'success' => true,
            'data' => MemberEventResource::collection($events->items()),
            'meta' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage(),
            ],
        ]);
    }

    /**
     * Events the member has registered for.
     */
    public function registered(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $events = Event::query()
            ->whereHas('registrations', fn ($query) => $query->where('member_id', $member->id))
            ->with('branch')
            ->orderBy('start_date')
            ->get()
            ->each(fn (Event $event) => $event->is_registered = true);

        return response()->json([
            'success' => true,
            'data' => MemberEventResource::collection($events),
        ]);
    }

    /**
     * Register the authenticated member for an event.
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        $member = $this->currentMember($request);

        if ($event->branch_id !== $member->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This event is not available for your branch.',
            ], 403);
        }

        if ($event->registration_type === 'none') {
            return response()->json([
                'success' => false,
                'message' => 'This event does not accept registrations.',
            ], 422);
        }

        $existing = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->first();

        if ($existing !== null) {
            return response()->json([
                'success' => false,
                'message' => 'You are already registered for this event.',
            ], 409);
        }

        if ($this->isAtCapacity($event)) {
            return response()->json([
                'success' => false,
                'message' => 'This event has reached its capacity.',
            ], 422);
        }

        $validated = $request->validate(
            $this->customFieldRules($event),
        );

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
            'member_id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
            'custom_fields' => $validated['custom_fields'] ?? null,
            'registration_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You are registered for this event.',
            'data' => [
                'registration_id' => $registration->id,
                'event_id' => $event->id,
            ],
        ], 201);
    }

    /**
     * Cancel the authenticated member's registration.
     */
    public function cancel(Request $request, Event $event): JsonResponse
    {
        $member = $this->currentMember($request);

        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->first();

        if ($registration === null) {
            return response()->json([
                'success' => false,
                'message' => 'You are not registered for this event.',
            ], 404);
        }

        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your registration has been cancelled.',
        ]);
    }

    private function isAtCapacity(Event $event): bool
    {
        if ($event->max_capacity === null || $event->max_capacity <= 0) {
            return false;
        }

        return $event->registrations()->count() >= $event->max_capacity;
    }

    /**
     * Build validation rules from the pastor's form-builder definition so the
     * app's natively-rendered form is validated server-side too.
     *
     * @return array<string, mixed>
     */
    private function customFieldRules(Event $event): array
    {
        if ($event->registration_type !== 'form' || empty($event->custom_form_fields)) {
            return [];
        }

        $rules = ['custom_fields' => ['required', 'array']];

        foreach ($event->custom_form_fields as $field) {
            $name = $field['name'] ?? null;

            if ($name === null) {
                continue;
            }

            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];

            $fieldRules[] = match ($field['type'] ?? 'text') {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                default => 'string',
            };

            if (($field['type'] ?? null) === 'select' && ! empty($field['options'])) {
                $fieldRules[] = 'in:'.implode(',', $field['options']);
            }

            $rules['custom_fields.'.$name] = $fieldRules;
        }

        return $rules;
    }
}
