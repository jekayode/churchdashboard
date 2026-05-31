<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMessage;
use App\Notifications\BusinessMessageReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class BusinessMessageController extends Controller
{
    public function threads(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->ownsBusinesses()) {
            $threads = BusinessMessage::query()
                ->whereIn('business_id', $user->businesses()->pluck('id'))
                ->selectRaw('thread_id, business_id, customer_user_id, MAX(created_at) as last_message_at')
                ->groupBy('thread_id', 'business_id', 'customer_user_id')
                ->with(['business:id,name,slug', 'customer:id,name'])
                ->orderByDesc('last_message_at')
                ->paginate(20);
        } else {
            $threads = BusinessMessage::query()
                ->where('customer_user_id', $user->id)
                ->selectRaw('thread_id, business_id, customer_user_id, MAX(created_at) as last_message_at')
                ->groupBy('thread_id', 'business_id', 'customer_user_id')
                ->with(['business:id,name,slug'])
                ->orderByDesc('last_message_at')
                ->paginate(20);
        }

        return response()->json(['success' => true, 'data' => $threads]);
    }

    public function showThread(Request $request, string $threadId): JsonResponse
    {
        Gate::authorize('viewBusinessMessageThread', $threadId);

        $messages = BusinessMessage::query()
            ->where('thread_id', $threadId)
            ->with(['sender:id,name', 'business:id,name,owner_user_id'])
            ->orderBy('created_at')
            ->get();

        BusinessMessage::query()
            ->where('thread_id', $threadId)
            ->where('sender_user_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function store(Request $request, Business $business): JsonResponse
    {
        Gate::authorize('sendBusinessMessage', $business);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $user = $request->user();

        $existingThreadId = BusinessMessage::query()
            ->where('business_id', $business->id)
            ->where('customer_user_id', $user->id)
            ->value('thread_id');

        $threadId = $existingThreadId ?? BusinessMessage::newThreadId();

        $message = BusinessMessage::query()->create([
            'thread_id' => $threadId,
            'business_id' => $business->id,
            'customer_user_id' => $user->id,
            'sender_user_id' => $user->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        $business->owner->notify(new BusinessMessageReceived($message));

        return response()->json(['success' => true, 'data' => $message, 'message' => 'Message sent.'], 201);
    }

    public function reply(Request $request, string $threadId): JsonResponse
    {
        $first = BusinessMessage::query()->where('thread_id', $threadId)->firstOrFail();
        Gate::authorize('replyToBusinessMessage', [$first->business, $threadId]);

        $data = $request->validate(['body' => ['required', 'string']]);
        $user = $request->user();

        $message = BusinessMessage::query()->create([
            'thread_id' => $threadId,
            'business_id' => $first->business_id,
            'customer_user_id' => $first->customer_user_id,
            'sender_user_id' => $user->id,
            'body' => $data['body'],
        ]);

        $recipient = $message->sender_user_id === $first->customer_user_id
            ? $first->business->owner
            : $first->customer;

        $recipient->notify(new BusinessMessageReceived($message));

        return response()->json(['success' => true, 'data' => $message, 'message' => 'Reply sent.'], 201);
    }
}
