<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $authUser = auth()->user();
    $contacts = User::whereKeyNot($authUser->id)->orderBy('name')->get();

    $contacts->each(function (User $contact) use ($authUser) {
        $latestMessage = Message::where(function ($query) use ($authUser, $contact) {
            $query->where('sender_id', $authUser->id)->where('receiver_id', $contact->id);
        })->orWhere(function ($query) use ($authUser, $contact) {
            $query->where('sender_id', $contact->id)->where('receiver_id', $authUser->id);
        })->latest()->first();

        $contact->latest_message_preview = $latestMessage?->body
            ?: ($latestMessage?->media_type ? 'Sent a '.$latestMessage->media_type : $contact->email);
        $contact->latest_message_at = $latestMessage?->created_at;
        $contact->unread_count = Message::where('sender_id', $contact->id)
            ->where('receiver_id', $authUser->id)
            ->whereNull('read_at')
            ->count();
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
    Route::get('/messages/unread-summary', [MessageController::class, 'unreadSummary'])->name('messages.unread-summary');
    Route::post('/messages/{user}/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::get('/messages/{user}/typing', [MessageController::class, 'typingStatus'])->name('messages.typing-status');
    Route::get('/messages/{user}', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/gallery/{user}', [MessageController::class, 'gallery'])->name('messages.gallery');
});

require __DIR__.'/auth.php';
