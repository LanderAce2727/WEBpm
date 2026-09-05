<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureCanMessage($authUser, $user);

        $messages = Message::with(['sender', 'reactions'])
            ->where(function ($query) use ($authUser, $user) {
                $query->where('sender_id', $authUser->id)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($authUser, $user) {
                $query->where('sender_id', $user->id)->where('receiver_id', $authUser->id);
            })
            ->oldest()
            ->get();

        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => $this->messagePayload($message, $authUser->id)),
        ]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $this->ensureCanMessage($request->user(), $user);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'media' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime', 'max:51200'],
        ]);

        if (! $request->filled('body') && ! $request->hasFile('media')) {
            return response()->json(['message' => 'Write a message or attach a photo/video.'], 422);
        }

        $media = $request->file('media');
        $mediaPath = null;
        $mediaType = null;

        if ($media) {
            $mediaPath = $media->store('message-media', 'public');
            $mediaType = str_starts_with((string) $media->getMimeType(), 'video/') ? 'video' : 'photo';
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'body' => $data['body'] ?? null,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'media_original_name' => $media?->getClientOriginalName(),
            'media_mime' => $media?->getMimeType(),
            'media_size' => $media?->getSize(),
        ])->load(['sender', 'reactions']);

        return response()->json([
            'message' => $this->messagePayload($message, $request->user()->id),
        ], 201);
    }

    public function gallery(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();
        $this->ensureCanMessage($authUser, $user);

        $items = Message::with(['sender', 'reactions'])
            ->whereNotNull('media_path')
            ->where(function ($query) use ($authUser, $user) {
                $query->where(function ($inner) use ($authUser, $user) {
                    $inner->where('sender_id', $authUser->id)->where('receiver_id', $user->id);
                })->orWhere(function ($inner) use ($authUser, $user) {
                    $inner->where('sender_id', $user->id)->where('receiver_id', $authUser->id);
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'items' => $items->map(fn (Message $message) => $this->messagePayload($message, $authUser->id)),
        ]);
    }

    public function unreadSummary(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $unread = Message::query()
            ->select('sender_id', DB::raw('count(*) as unread_count'), DB::raw('max(created_at) as latest_at'))
            ->where('receiver_id', $authUser->id)
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        $senders = User::whereIn('id', $unread->keys())->pluck('name', 'id');

        $unread->each(function ($item) use ($senders) {
            $item->sender_name = $senders[$item->sender_id] ?? 'Someone';
        });

        return response()->json(['unread' => $unread]);
    }

    public function typing(Request $request, User $user): JsonResponse
    {
        $this->ensureCanMessage($request->user(), $user);

        Cache::put($this->typingCacheKey($request->user()->id, $user->id), true, now()->addSeconds(6));

        return response()->json(['typing' => true]);
    }

    public function typingStatus(Request $request, User $user): JsonResponse
    {
        $this->ensureCanMessage($request->user(), $user);

        return response()->json([
            'typing' => Cache::has($this->typingCacheKey($user->id, $request->user()->id)),
        ]);
    }

    public function react(Request $request, Message $message): JsonResponse
    {
        $data = $request->validate([
            'reaction' => ['nullable', 'string', 'in:👍,❤️,😂,😮,😢,🙏'],
        ]);

        $authUser = $request->user();
        $otherUserId = $message->sender_id === $authUser->id ? $message->receiver_id : $message->sender_id;

        abort_unless($message->sender_id === $authUser->id || $message->receiver_id === $authUser->id, 403);
        $this->ensureCanMessage($authUser, User::findOrFail($otherUserId));

        if (empty($data['reaction'])) {
            MessageReaction::where('message_id', $message->id)
                ->where('user_id', $authUser->id)
                ->delete();
        } else {
            MessageReaction::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $authUser->id],
                ['reaction' => $data['reaction']]
            );
        }

        return response()->json([
            'message' => $this->messagePayload($message->fresh(['sender', 'reactions']), $authUser->id),
        ]);
    }

    private function messagePayload(Message $message, int $viewerId): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'direction' => $message->sender_id === $viewerId ? 'sent' : 'received',
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'media_url' => $message->mediaUrl(),
            'media_type' => $message->media_type,
            'media_original_name' => $message->media_original_name,
            'media_size' => $message->media_size,
            'created_at' => $message->created_at->toIso8601String(),
            'created_at_display' => $message->created_at->format('M j, Y g:i A'),
            'read' => (bool) $message->read_at,
            'reactions' => $message->reactions
                ->groupBy('reaction')
                ->map(fn ($items) => $items->count())
                ->all(),
            'viewer_reaction' => $message->reactions->firstWhere('user_id', $viewerId)?->reaction,
        ];
    }

    private function ensureCanMessage(User $authUser, User $user): void
    {
        abort_unless(Friendship::where('status', Friendship::STATUS_ACCEPTED)
            ->where(function ($query) use ($authUser, $user) {
                $query->where(function ($inner) use ($authUser, $user) {
                    $inner->where('requester_id', $authUser->id)->where('addressee_id', $user->id);
                })->orWhere(function ($inner) use ($authUser, $user) {
                    $inner->where('requester_id', $user->id)->where('addressee_id', $authUser->id);
                });
            })
            ->exists(), 403);
    }

    private function typingCacheKey(int $senderId, int $receiverId): string
    {
        return "typing:{$senderId}:{$receiverId}";
    }
}
