<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PM Messenger, a private family chat space.">
    <title>PM Messenger | Chats</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/theme.css">
    <script src="/js/script.js" defer></script>
  </head>
  <body data-page="chat">
    <div class="app-shell">
      <aside class="sidebar" aria-label="Messenger navigation">
        <div class="brand-row">
          <a class="brand-lockup" href="{{ url('/') }}" aria-label="PM Messenger home">
            <span class="brand-mark" aria-hidden="true">pm</span>
            <span>
              <span class="eyebrow">private circle</span>
              <strong>PM Messenger</strong>
            </span>
          </a>
          <button class="icon-button mobile-close" id="close-sidebar" aria-label="Close menu">x</button>
        </div>

        <a class="profile-mini" href="{{ route('profile') }}">
          <span class="avatar-initials" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
          <span>
            <strong>{{ auth()->user()->name }}</strong>
            <small><i class="status-dot"></i> Available</small>
          </span>
          <b aria-hidden="true">...</b>
        </a>

        <label class="search-box">
          <span aria-hidden="true">/</span>
          <input id="search-input" type="search" placeholder="Search family">
          <kbd>Ctrl K</kbd>
        </label>

        <nav class="nav-tabs" aria-label="Main sections">
          <a class="nav-tab active" href="{{ url('/') }}"><span>o</span> Chats <b>{{ $contacts->count() }}</b></a>
          <a class="nav-tab" href="{{ route('profile') }}"><span>u</span> Profile</a>
          <a class="nav-tab" href="{{ route('settings') }}"><span>*</span> Settings</a>
        </nav>

        <div class="section-heading">
          <span>Registered users</span>
          <a class="text-button" href="{{ route('register') }}">+ Invite</a>
        </div>
        <div class="chat-list" id="chat-list">
          @forelse ($contacts as $contact)
            <button class="chat-item @if ($loop->first) active @endif" data-chat="{{ $contact->name }}" data-status="Registered user" data-initial="{{ strtoupper(substr($contact->name, 0, 1)) }}">
              <span class="avatar-initials" aria-hidden="true">{{ strtoupper(substr($contact->name, 0, 1)) }}</span>
              <span class="chat-presence"></span>
              <span class="chat-copy"><strong>{{ $contact->name }}</strong><small>{{ $contact->email }}</small></span><time>New</time>
            </button>
          @empty
            <div class="empty-state">
              <strong>No other users yet</strong>
              <span>Ask a friend to register, then they will appear here.</span>
            </div>
          @endforelse
        </div>

        <form class="sidebar-footer logout-form" method="POST" action="{{ route('logout') }}">
          @csrf
          <span><span class="secure-icon">#</span> Signed in as {{ auth()->user()->name }}</span>
          <button class="text-button" type="submit">Log out</button>
        </form>
      </aside>

      <main class="main-stage">
        <header class="topbar">
          <button class="icon-button menu-button" id="open-sidebar" aria-label="Open menu">=</button>
          <div class="active-title">
            <span class="avatar-initials large" id="header-avatar" aria-hidden="true">{{ $contacts->first() ? strtoupper(substr($contacts->first()->name, 0, 1)) : 'PM' }}</span>
            <div><h2 id="chat-title">{{ $contacts->first()->name ?? 'No users yet' }}</h2><span id="chat-status"><i class="status-dot"></i> {{ $contacts->first() ? 'Registered user' : 'Waiting for registrations' }}</span></div>
          </div>
          <div class="top-actions">
            <button class="icon-button" id="call-button" aria-label="Start voice call">C</button>
            <button class="icon-button" id="video-button" aria-label="Start video call">V</button>
            <button class="icon-button" id="gallery-button" aria-label="Open shared gallery">G</button>
          </div>
        </header>

        <section class="conversation" id="conversation" aria-label="Conversation">
          <div class="conversation-intro">
            <div class="intro-avatar"><span class="avatar-initials large" id="intro-avatar" aria-hidden="true">{{ $contacts->first() ? strtoupper(substr($contacts->first()->name, 0, 1)) : 'PM' }}</span></div>
            <h3>{{ $contacts->first()->name ?? 'Invite someone to start' }}</h3>
            <p>{{ $contacts->first() ? 'Start a test message with this registered user.' : 'When another person registers, they will appear in your chat list.' }}</p>
          </div>
          <div class="date-divider"><span>No messages yet</span></div>
          <div class="typing" id="typing" hidden><span></span><span></span><span></span> typing</div>
        </section>

        <form class="composer" id="composer">
          <button type="button" class="icon-button attachment-button" id="attach-button" aria-label="Attach media" title="Attach photo or video">+</button>
          <input id="message-input" autocomplete="off" placeholder="Write a message..." aria-label="Write a message">
          <button type="button" class="icon-button emoji-button" aria-label="Add emoji">:</button>
          <button type="submit" class="send-button" aria-label="Send message">-&gt;</button>
        </form>
      </main>

      <aside class="details-panel" id="details-panel" aria-label="Conversation details">
        <div class="details-head"><h2>User details</h2><button class="icon-button" id="close-details" aria-label="Close details">x</button></div>
        <div class="details-profile"><span class="avatar-initials xl" id="details-avatar" aria-hidden="true">{{ $contacts->first() ? strtoupper(substr($contacts->first()->name, 0, 1)) : 'PM' }}</span><h3 id="details-name">{{ $contacts->first()->name ?? 'No user selected' }}</h3><span id="details-members">{{ $contacts->first() ? 'Registered user' : 'Waiting for registrations' }}</span></div>
        <div class="details-section"><div class="details-label"><span>Media, links & docs</span><strong>0</strong></div><div class="empty-state compact"><strong>No shared files yet</strong><span>New uploads will show here later.</span></div></div>
        <div class="details-section"><div class="details-label"><span>Conversation</span></div><button class="detail-action"><span>/</span> Search in conversation <b>&gt;</b></button><button class="detail-action"><span>z</span> Mute notifications <b>&gt;</b></button></div>
        <div class="details-section"><div class="details-label"><span>Privacy</span></div><div class="privacy-note"><span>#</span><p><strong>Private by design</strong><br>Only members of this conversation can see its messages and media.</p></div></div>
      </aside>
    </div>
    <input type="file" id="file-input" accept="image/*,video/*" hidden>
    <div class="toast" id="toast" role="status"></div>
  </body>
</html>
