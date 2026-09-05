<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <img class="brand-mark" src="/images/pm-logo.png" alt="" aria-hidden="true">
            <span class="brand-copy"><span class="eyebrow">private circle</span><strong>PM Messenger</strong></span>
          </a>
          <button class="icon-button burger-button" id="toggle-sidebar" type="button" aria-label="Toggle menu"><span></span><span></span><span></span></button>
        </div>
        <nav class="nav-tabs" aria-label="Main sections">
          <a class="nav-tab" href="{{ url('/') }}"><span>o</span> Chats</a>
          <a class="nav-tab active" href="{{ route('profile') }}"><span>u</span> Profile</a>
          <a class="nav-tab" href="{{ route('settings') }}"><span>*</span> Settings</a>
        </nav>
        <form class="sidebar-footer logout-form" method="POST" action="{{ route('logout') }}">
          @csrf
          <span><span class="secure-icon">#</span> Signed in</span>
          <button class="text-button" type="submit">Log out</button>
        </form>
      </aside>

      <main class="profile-page page-main">
        <header class="page-topbar">
          <a class="back-link" href="{{ url('/') }}">Back to chats</a>
          <a class="pill-link" href="{{ route('settings') }}">Settings</a>
        </header>

        <form class="profile-hero facebook-style-profile" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
          @csrf
          @method('PATCH')

          <input type="hidden" name="name" value="{{ auth()->user()->name }}">
          <input type="hidden" name="email" value="{{ auth()->user()->email }}">
          <input type="hidden" name="status_message" value="{{ auth()->user()->status_message }}">

          <div class="profile-cover-wrap">
            <img class="profile-cover" src="{{ auth()->user()->coverPhotoUrl() ?? 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1400&q=80' }}" alt="Profile background photo">
            <label class="photo-edit-button cover-edit">
              <input type="file" name="cover_photo" accept="image/*" onchange="this.form.submit()">
              <span>Change cover photo</span>
            </label>
          </div>
          <div class="profile-identity">
            <div class="profile-photo-frame">
              @if (auth()->user()->profilePhotoUrl())
                <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}">
              @else
                <span class="avatar-initials xl" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
              @endif
              <label class="photo-camera-button" aria-label="Change profile photo">
                <input type="file" name="profile_photo" accept="image/*" onchange="this.form.submit()">
                <span aria-hidden="true"></span>
              </label>
            </div>
            <div>
              <p class="eyebrow">profile</p>
              <h1>{{ auth()->user()->name }}</h1>
              <span>{{ auth()->user()->status_message ?: 'Available' }}</span>
            </div>
          </div>
        </form>

        <section class="profile-grid">
          <article class="info-panel wide">
            <h2>Edit Profile</h2>
            @if (session('status') === 'profile-updated')
              <p class="success-note">Profile updated.</p>
            @endif
            <form class="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
              @csrf
              @method('PATCH')

              <label>
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                @error('name')<small>{{ $message }}</small>@enderror
              </label>

              <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                @error('email')<small>{{ $message }}</small>@enderror
              </label>

              <label>
                <span>Status</span>
                <input type="text" name="status_message" maxlength="120" value="{{ old('status_message', auth()->user()->status_message) }}" placeholder="Available">
                @error('status_message')<small>{{ $message }}</small>@enderror
              </label>

              <button class="pill-link profile-save" type="submit">Save profile</button>
            </form>
          </article>
          <article class="info-panel">
            <h2>Contact</h2>
            <dl class="profile-list">
              <div><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></div>
              <div><dt>Status</dt><dd><i class="status-dot"></i> {{ auth()->user()->status_message ?: 'Available' }}</dd></div>
              <div><dt>Joined</dt><dd>{{ auth()->user()->created_at->format('M j, Y') }}</dd></div>
            </dl>
          </article>
          <article class="info-panel">
            <h2>Photo</h2>
            <p>Your profile photo is shown in the chat list, conversation header, and message details.</p>
          </article>
        </section>
      </main>
    </div>
    <div class="toast" id="toast" role="status"></div>
  </body>
</html>
