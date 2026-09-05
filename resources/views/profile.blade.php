<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM Messenger | Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/theme.css">
    <script src="/js/script.js" defer></script>
  </head>
  <body data-page="profile">
    <div class="page-shell">
      <aside class="sidebar page-sidebar" aria-label="Messenger navigation">
        <div class="brand-row">
          <a class="brand-lockup" href="{{ url('/') }}" aria-label="PM Messenger home">
            <span class="brand-mark" aria-hidden="true">pm</span>
            <span><span class="eyebrow">private circle</span><strong>PM Messenger</strong></span>
          </a>
        </div>
        <nav class="nav-tabs" aria-label="Main sections">
          <a class="nav-tab" href="{{ url('/') }}"><span>o</span> Chats</a>
          <a class="nav-tab active" href="{{ route('profile') }}"><span>u</span> Profile</a>
          <a class="nav-tab" href="{{ route('settings') }}"><span>*</span> Settings</a>
        </nav>
        <div class="sidebar-footer"><span class="secure-icon">#</span> End-to-end private</div>
      </aside>

      <main class="profile-page page-main">
        <header class="page-topbar">
          <a class="back-link" href="{{ url('/') }}">Back to chats</a>
          <a class="pill-link" href="{{ route('settings') }}">Settings</a>
        </header>

        <section class="profile-hero">
          <img class="profile-cover" src="https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1400&q=80" alt="Mountain landscape">
          <div class="profile-identity">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=220&q=80" alt="Priya Mehta">
            <div>
              <p class="eyebrow">available now</p>
              <h1>Priya Mehta</h1>
              <span>Family organizer - Lemon tart specialist</span>
            </div>
          </div>
        </section>

        <section class="profile-grid">
          <article class="info-panel">
            <h2>About</h2>
            <p>Keeping the family loop calm, warm, and easy to follow. Favorite chats are Sunday plans, photo drops, and tiny updates that make everyone feel close.</p>
          </article>
          <article class="info-panel">
            <h2>Contact</h2>
            <dl class="profile-list">
              <div><dt>Email</dt><dd>priya@example.com</dd></div>
              <div><dt>Phone</dt><dd>+1 555 0188</dd></div>
              <div><dt>Status</dt><dd><i class="status-dot"></i> Available</dd></div>
            </dl>
          </article>
          <article class="info-panel wide">
            <h2>Shared Circles</h2>
            <div class="circle-row"><span>Mum & Dad</span><b>Online now</b></div>
            <div class="circle-row"><span>Family plans</span><b>4 members</b></div>
            <div class="circle-row"><span>Sofia</span><b>Photos shared</b></div>
          </article>
        </section>
      </main>
    </div>
    <div class="toast" id="toast" role="status"></div>
  </body>
</html>
