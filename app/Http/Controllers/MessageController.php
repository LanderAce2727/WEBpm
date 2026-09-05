<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        $messages = Message::with('sender')
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
        ])->load('sender');

        return response()->json([
            'message' => $this->messagePayload($message, $request->user()->id),
        ], 201);
    }

    public function gallery(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        $items = Message::with('sender')
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

        return response()->json(['unread' => $unread]);
    }

    public function typing(Request $request, User $user): JsonResponse
    {
        Cache::put($this->typingCacheKey($request->user()->id, $user->id), true, now()->addSeconds(6));

        return response()->json(['typing' => true]);
    }

    public function typingStatus(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'typing' => Cache::has($this->typingCacheKey($user->id, $request->user()->id)),
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
        ];
    }

    private function typingCacheKey(int $senderId, int $receiverId): string
    {
        return "typing:{$senderId}:{$receiverId}";
    }
}
