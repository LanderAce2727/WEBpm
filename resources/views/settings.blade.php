<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM Messenger | Settings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/theme.css">
    <script src="/js/script.js" defer></script>
  </head>
  <body data-page="settings">
    <div class="page-shell">
      <aside class="sidebar page-sidebar" aria-label="Messenger navigation">
        <div class="brand-row">
          <a class="brand-lockup" href="{{ url('/') }}" aria-label="PM Messenger home">
            <img class="brand-mark" src="/images/pm-logo.png" alt="" aria-hidden="true">
            <span class="brand-copy"><span class="eyebrow">private circle</span><strong>PM Messenger</strong></span>
          </a>
          <button class="icon-button burger-button" id="toggle-sidebar" type="button" aria-label="Toggle menu"><span></span><span></span><span></span></button>
        </div>
        <nav class="nav-tabs" aria-label="Main sections">
          <a class="nav-tab" href="{{ url('/') }}"><span>o</span> Chats</a>
          <a class="nav-tab" href="{{ route('profile') }}"><span>u</span> Profile</a>
          <a class="nav-tab active" href="{{ route('settings') }}"><span>*</span> Settings</a>
        </nav>
        <form class="sidebar-footer logout-form" method="POST" action="{{ route('logout') }}">
          @csrf
          <span><span class="secure-icon">#</span> Signed in</span>
          <button class="text-button" type="submit">Log out</button>
        </form>
      </aside>

      <main class="settings-page page-main">
        <header class="page-topbar">
          <a class="back-link" href="{{ url('/') }}">Back to chats</a>
          <a class="pill-link" href="{{ route('profile') }}">Profile</a>
        </header>

        <section class="settings-header">
          <p class="eyebrow">preferences</p>
          <h1>Settings</h1>
          <p>Make PM Messenger feel quiet, clear, and comfortable.</p>
        </section>

        <section class="settings-list">
          <div class="setting-row">
            <div><strong>Appearance</strong><span>Choose how the whole app looks</span></div>
            <div class="theme-switch" role="group" aria-label="Theme">
              <button data-theme="light">Light</button>
              <button data-theme="dark-default">Default Dark</button>
              <button data-theme="dark-purple">Black Purple</button>
            </div>
          </div>
          <div class="setting-row">
            <div><strong>Notifications</strong><span>Message alerts on this device</span></div>
            <button class="toggle active" id="notification-toggle" aria-label="Toggle notifications"><i></i></button>
          </div>
          <div class="setting-row">
            <div><strong>Privacy</strong><span>Only family members can access chats</span></div>
            <span class="privacy-badge">Private</span>
          </div>
          <div class="setting-row">
            <div><strong>Read receipts</strong><span>Show when messages have been seen</span></div>
            <button class="toggle active" aria-label="Toggle read receipts"><i></i></button>
          </div>
        </section>
      </main>
    </div>
    <div class="toast" id="toast" role="status"></div>
  </body>
</html>
