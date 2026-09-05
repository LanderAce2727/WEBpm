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
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <script src="{{ asset('js/script.js') }}" defer></script>
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
          <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=160&q=80" alt="Priya Mehta">
          <span>
            <strong>Priya Mehta</strong>
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
          <a class="nav-tab active" href="{{ url('/') }}"><span>o</span> Chats <b>4</b></a>
          <a class="nav-tab" href="{{ route('profile') }}"><span>u</span> Profile</a>
          <a class="nav-tab" href="{{ route('settings') }}"><span>*</span> Settings</a>
        </nav>

        <div class="section-heading">
          <span>Family circle</span>
          <button class="text-button" id="new-chat">+ New chat</button>
        </div>
        <div class="chat-list" id="chat-list">
          <button class="chat-item active" data-chat="Mum & Dad" data-status="Online now" data-avatar="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80">
            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80" alt="Mum and Dad">
            <span class="chat-presence"></span>
            <span class="chat-copy"><strong>Mum & Dad</strong><small>Don't forget Sunday lunch</small></span><time>10:42</time>
          </button>
          <button class="chat-item" data-chat="Sofia" data-status="Online now" data-avatar="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=160&q=80">
            <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=160&q=80" alt="Sofia">
            <span class="chat-presence"></span>
            <span class="chat-copy"><strong>Sofia</strong><small>Sent a photo</small></span><time>Yesterday</time>
          </button>
          <button class="chat-item" data-chat="Leo" data-status="Away" data-avatar="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=160&q=80">
            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=160&q=80" alt="Leo">
            <span class="chat-copy"><strong>Leo</strong><small>That view is unreal</small></span><time>Tue</time>
          </button>
          <button class="chat-item" data-chat="Family plans" data-status="4 members" data-avatar="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=160&q=80">
            <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=160&q=80" alt="Family plans">
            <span class="chat-copy"><strong>Family plans</strong><small>Alex: I can bring dessert</small></span><time>Mon</time>
          </button>
        </div>

        <div class="sidebar-footer"><span class="secure-icon">#</span> End-to-end private</div>
      </aside>

      <main class="main-stage">
        <header class="topbar">
          <button class="icon-button menu-button" id="open-sidebar" aria-label="Open menu">=</button>
          <div class="active-title">
            <img id="header-avatar" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80" alt="Mum and Dad">
            <div><h2 id="chat-title">Mum & Dad</h2><span id="chat-status"><i class="status-dot"></i> Online now</span></div>
          </div>
          <div class="top-actions">
            <button class="icon-button" id="call-button" aria-label="Start voice call">C</button>
            <button class="icon-button" id="video-button" aria-label="Start video call">V</button>
            <button class="icon-button" id="gallery-button" aria-label="Open shared gallery">G</button>
          </div>
        </header>

        <section class="conversation" id="conversation" aria-label="Conversation with Mum and Dad">
          <div class="conversation-intro"><div class="intro-avatar"><img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80" alt="Mum and Dad"></div><h3>Mum & Dad</h3><p>Your private family space. Messages and media stay between you.</p></div>
          <div class="date-divider"><span>Today</span></div>
          <div class="typing" id="typing"><span></span><span></span><span></span> Mum is typing</div>
        </section>

        <form class="composer" id="composer">
          <button type="button" class="icon-button attachment-button" id="attach-button" aria-label="Attach media" title="Attach photo or video">+</button>
          <input id="message-input" autocomplete="off" placeholder="Write a message..." aria-label="Write a message">
          <button type="button" class="icon-button emoji-button" aria-label="Add emoji">:</button>
          <button type="submit" class="send-button" aria-label="Send message">-&gt;</button>
        </form>
      </main>

      <aside class="details-panel" id="details-panel" aria-label="Conversation details">
        <div class="details-head"><h2>Shared moments</h2><button class="icon-button" id="close-details" aria-label="Close details">x</button></div>
        <div class="details-profile"><img id="details-avatar" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=240&q=80" alt="Mum and Dad"><h3 id="details-name">Mum & Dad</h3><span id="details-members">2 members - Private chat</span></div>
        <div class="details-section"><div class="details-label"><span>Media, links & docs</span><strong>12</strong></div><div class="media-grid"><img src="https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=240&q=80" alt="Mountain view"><img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=240&q=80" alt="Lunch"><img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=240&q=80" alt="Hiking trail"><button class="more-media">+9</button></div></div>
        <div class="details-section"><div class="details-label"><span>Conversation</span></div><button class="detail-action"><span>/</span> Search in conversation <b>&gt;</b></button><button class="detail-action"><span>z</span> Mute notifications <b>&gt;</b></button></div>
        <div class="details-section"><div class="details-label"><span>Privacy</span></div><div class="privacy-note"><span>#</span><p><strong>Private by design</strong><br>Only members of this conversation can see its messages and media.</p></div></div>
      </aside>
    </div>
    <input type="file" id="file-input" accept="image/*,video/*" hidden>
    <div class="toast" id="toast" role="status"></div>
  </body>
</html>
