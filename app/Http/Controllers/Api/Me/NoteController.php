<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\ReadingDay;
use App\Models\Sermon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NoteController extends Controller
{
    use ResolvesCurrentMember;

    /**
     * The member's notes across the whole app — the "My Notes" hub.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $validated = $request->validate([
            'type' => ['nullable', 'in:sermon,reading,personal'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $notes = Note::query()
            ->forMember($member->id)
            ->with('notable')
            ->when($validated['type'] ?? null, function ($query, string $type): void {
                $type === 'personal'
                    ? $query->whereNull('notable_type')
                    : $query->where('notable_type', Note::TYPES[$type]);
            })
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->where(
                fn ($inner) => $inner->where('body', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%'),
            ))
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json([
            'success' => true,
            'data' => collect($notes->items())->map(fn (Note $note): array => $this->payload($note))->values(),
            'meta' => [
                'current_page' => $notes->currentPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'last_page' => $notes->lastPage(),
                'counts' => $this->counts($member->id),
            ],
        ]);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        $member = $this->currentMember($request);

        if ($note->member_id !== $member->id) {
            return $this->notFound();
        }

        return response()->json(['success' => true, 'data' => $this->payload($note->load('notable'))]);
    }

    /**
     * Create a note, optionally attached to a sermon or a reading day.
     */
    public function store(Request $request): JsonResponse
    {
        $member = $this->currentMember($request);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:50000'],
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:sermon,reading'],
            'notable_id' => ['nullable', 'integer', 'required_with:type'],
        ]);

        $notableType = null;
        $notableId = null;

        if (filled($validated['type'] ?? null)) {
            $notableType = Note::TYPES[$validated['type']];

            // Confirm the target exists before attaching to it.
            if ($notableType::query()->whereKey($validated['notable_id'])->doesntExist()) {
                return response()->json([
                    'success' => false,
                    'message' => 'That '.$validated['type'].' could not be found.',
                ], 422);
            }

            $notableId = (int) $validated['notable_id'];
        }

        $note = Note::create([
            'member_id' => $member->id,
            'notable_type' => $notableType,
            'notable_id' => $notableId,
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note saved.',
            'data' => $this->payload($note->load('notable')),
        ], 201);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $member = $this->currentMember($request);

        if ($note->member_id !== $member->id) {
            return $this->notFound();
        }

        $validated = $request->validate([
            'body' => ['sometimes', 'required', 'string', 'max:50000'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $note->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Note updated.',
            'data' => $this->payload($note->fresh()->load('notable')),
        ]);
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        $member = $this->currentMember($request);

        if ($note->member_id !== $member->id) {
            return $this->notFound();
        }

        $note->delete();

        return response()->json(['success' => true, 'message' => 'Note deleted.']);
    }

    /**
     * Notes attached to one sermon or reading day, for that screen's note panel.
     */
    public function forItem(Request $request, string $type, int $id): JsonResponse
    {
        $member = $this->currentMember($request);

        if (! array_key_exists($type, Note::TYPES)) {
            return response()->json(['success' => false, 'message' => 'Unknown note type.'], 422);
        }

        $notes = Note::query()
            ->forMember($member->id)
            ->where('notable_type', Note::TYPES[$type])
            ->where('notable_id', $id)
            ->with('notable')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes->map(fn (Note $note): array => $this->payload($note))->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Note $note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'body' => $note->body,
            'kind' => $note->kind(),
            'context' => $note->context(),
            'created_at' => $note->created_at?->toIso8601String(),
            'updated_at' => $note->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function counts(int $memberId): array
    {
        return [
            'all' => Note::forMember($memberId)->count(),
            'sermon' => Note::forMember($memberId)->where('notable_type', Sermon::class)->count(),
            'reading' => Note::forMember($memberId)->where('notable_type', ReadingDay::class)->count(),
            'personal' => Note::forMember($memberId)->whereNull('notable_type')->count(),
        ];
    }

    /**
     * Another member's note is reported as missing rather than forbidden, so
     * the API never confirms that someone else's note exists.
     */
    private function notFound(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Note not found.'], 404);
    }
}
