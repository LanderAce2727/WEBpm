<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FriendshipController;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $authUser = auth()->user();
    $contacts = User::whereKeyNot($authUser->id)->orderBy('name')->get();
    $friendships = Friendship::where(function ($query) use ($authUser) {
        $query->where('requester_id', $authUser->id)->orWhere('addressee_id', $authUser->id);
    })->get();

    $contacts->each(function (User $contact) use ($authUser, $friendships) {
        $friendship = $friendships->first(function (Friendship $friendship) use ($authUser, $contact) {
            return in_array($authUser->id, [$friendship->requester_id, $friendship->addressee_id], true)
                && in_array($contact->id, [$friendship->requester_id, $friendship->addressee_id], true);
        });

        $contact->friendship_status = $friendship?->status ?? 'none';
        $contact->friendship_direction = $friendship
            ? ($friendship->requester_id === $authUser->id ? 'outgoing' : 'incoming')
            : 'none';
        $contact->can_message = $contact->friendship_status === Friendship::STATUS_ACCEPTED;

        $latestMessage = null;
        if ($contact->can_message) {
            $latestMessage = Message::where(function ($query) use ($authUser, $contact) {
                $query->where('sender_id', $authUser->id)->where('receiver_id', $contact->id);
            })->orWhere(function ($query) use ($authUser, $contact) {
                $query->where('sender_id', $contact->id)->where('receiver_id', $authUser->id);
            })->latest()->first();
        }

        $contact->latest_message_preview = match (true) {
            $contact->can_message && $latestMessage?->body => $latestMessage->body,
            $contact->can_message && $latestMessage?->media_type => 'Sent a '.$latestMessage->media_type,
            $contact->friendship_status === Friendship::STATUS_PENDING && $contact->friendship_direction === 'incoming' => 'Wants to message you',
            $contact->friendship_status === Friendship::STATUS_PENDING => 'Invite pending',
            $contact->friendship_status === Friendship::STATUS_DECLINED => 'Invite declined',
            default => 'Send invite to start chatting',
        };
        $contact->latest_message_at = $latestMessage?->created_at;
        $contact->unread_count = $contact->can_message
            ? Message::where('sender_id', $contact->id)
                ->where('receiver_id', $authUser->id)
                ->whereNull('read_at')
                ->count()
            : 0;
    });

    $contacts = $contacts->sortByDesc(function (User $contact) {
        $latestTimestamp = optional($contact->latest_message_at)->timestamp ?? 0;

        return (($contact->unread_count > 0) ? 10_000_000_000 : 0) + $latestTimestamp;
    })->values();

    return view('messenger', ['contacts' => $contacts]);
})->middleware('auth');

Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth'])->name('dashboard');

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth')->name('profile');

Route::get('/settings', function () {
    return view('settings');
})->middleware('auth')->name('settings');

Route::middleware('auth')->group(function () {
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/friends/{user}/invite', [FriendshipController::class, 'store'])->name('friends.invite');
    Route::post('/friends/{user}/accept', [FriendshipController::class, 'accept'])->name('friends.accept');
    Route::post('/friends/{user}/decline', [FriendshipController::class, 'decline'])->name('friends.decline');
    Route::get('/friends/notifications', [FriendshipController::class, 'notifications'])->name('friends.notifications');
    Route::get('/messages/unread-summary', [MessageController::class, 'unreadSummary'])->name('messages.unread-summary');
    Route::post('/messages/{message}/reaction', [MessageController::class, 'react'])->name('messages.react');
    Route::post('/messages/{user}/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::get('/messages/{user}/typing', [MessageController::class, 'typingStatus'])->name('messages.typing-status');
    Route::get('/messages/{user}', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/gallery/{user}', [MessageController::class, 'gallery'])->name('messages.gallery');
});

require __DIR__.'/auth.php';
