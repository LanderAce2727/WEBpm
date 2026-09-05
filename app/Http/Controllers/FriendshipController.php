<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if ($authUser->is($user)) {
            return response()->json(['message' => 'You cannot invite yourself.'], 422);
        }

        $friendship = $this->findFriendship($authUser->id, $user->id);

        if ($friendship?->status === Friendship::STATUS_ACCEPTED) {
            return response()->json(['friendship' => $this->payload($friendship, $authUser->id)]);
        }

        if ($friendship && $friendship->addressee_id === $authUser->id && $friendship->status === Friendship::STATUS_PENDING) {
            $friendship->update([
                'status' => Friendship::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            return response()->json(['friendship' => $this->payload($friendship, $authUser->id)]);
        }

        if ($friendship) {
            $friendship->update([
                'requester_id' => $authUser->id,
                'addressee_id' => $user->id,
                'status' => Friendship::STATUS_PENDING,
                'responded_at' => null,
            ]);
        } else {
            $friendship = Friendship::create([
                'requester_id' => $authUser->id,
                'addressee_id' => $user->id,
            ]);
        }

        return response()->json(['friendship' => $this->payload($friendship, $authUser->id)], 201);
    }

    public function accept(Request $request, User $user): JsonResponse
    {
        $friendship = Friendship::where('requester_id', $user->id)
            ->where('addressee_id', $request->user()->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->firstOrFail();

        $friendship->update([
            'status' => Friendship::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        return response()->json(['friendship' => $this->payload($friendship, $request->user()->id)]);
    }

    public function decline(Request $request, User $user): JsonResponse
    {
        $friendship = Friendship::where('requester_id', $user->id)
            ->where('addressee_id', $request->user()->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->firstOrFail();

        $friendship->update([
            'status' => Friendship::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        return response()->json(['friendship' => $this->payload($friendship, $request->user()->id)]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $invites = Friendship::with('requester')
            ->where('addressee_id', $request->user()->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->latest()
            ->get();

        return response()->json([
            'invites' => $invites->map(fn (Friendship $friendship) => [
                'id' => $friendship->id,
                'requester_id' => $friendship->requester_id,
                'requester_name' => $friendship->requester->name,
                'created_at' => $friendship->created_at->toIso8601String(),
            ]),
        ]);
    }

    private function findFriendship(int $firstUserId, int $secondUserId): ?Friendship
    {
        return Friendship::where(function ($query) use ($firstUserId, $secondUserId) {
            $query->where('requester_id', $firstUserId)->where('addressee_id', $secondUserId);
        })->orWhere(function ($query) use ($firstUserId, $secondUserId) {
            $query->where('requester_id', $secondUserId)->where('addressee_id', $firstUserId);
        })->first();
    }

    private function payload(Friendship $friendship, int $viewerId): array
    {
        return [
            'status' => $friendship->status,
            'direction' => $friendship->requester_id === $viewerId ? 'outgoing' : 'incoming',
        ];
    }
}
