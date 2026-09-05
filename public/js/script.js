const chatList = document.querySelector('#chat-list');
const chatItems = () => [...document.querySelectorAll('.chat-item')];
const title = document.querySelector('#chat-title');
const status = document.querySelector('#chat-status');
const headerAvatar = document.querySelector('#header-avatar');
const introAvatar = document.querySelector('#intro-avatar');
const detailsAvatar = document.querySelector('#details-avatar');
const detailsName = document.querySelector('#details-name');
const detailsPanel = document.querySelector('#details-panel');
const conversation = document.querySelector('#conversation');
const input = document.querySelector('#message-input');
const toast = document.querySelector('#toast');
const sidebar = document.querySelector('.sidebar');
const settingsPanel = document.querySelector('#settings-panel');
const fileInput = document.querySelector('#file-input');
const detailsButton = document.querySelector('#details-button');
const selectedFile = document.querySelector('#selected-file');
const mediaGrid = document.querySelector('#media-grid');
const galleryEmpty = document.querySelector('#gallery-empty');
const galleryCount = document.querySelector('#gallery-count');
const gallerySearch = document.querySelector('#gallery-search');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
let activeChat = document.querySelector('.chat-item.active');
let selectedMedia = null;
let latestUnreadTotal = 0;
let notificationReady = false;
let lastTypingPing = 0;
let isSyncingMessages = false;
let tooltipTimer = null;
let activeTooltipTarget = null;

function setNavCollapsed(collapsed) {
  document.body.classList.toggle('nav-collapsed', collapsed);
  localStorage.setItem('pm-nav-collapsed', collapsed ? 'yes' : 'no');
  document.querySelector('#toggle-sidebar')?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
}

setNavCollapsed(localStorage.getItem('pm-nav-collapsed') === 'yes');

function showToast(message) {
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => toast.classList.remove('show'), 2200);
}

function setDetailsOpen(open) {
  if (!detailsPanel) return;

  detailsPanel.classList.toggle('collapsed', !open);
  detailsPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
  detailsButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
  document.body.classList.toggle('details-open', open);
}

function avatarMarkup(name, initial, avatarUrl, size = '') {
  if (avatarUrl) return `<img class="${size}" src="${avatarUrl}" alt="${name}">`;
  return `<span class="avatar-initials ${size}" aria-hidden="true">${initial || name.slice(0, 1).toUpperCase()}</span>`;
}

function setAvatar(el, item, size = '') {
  if (!el || !item) return;
  const wrapper = document.createElement('div');
  wrapper.innerHTML = avatarMarkup(item.dataset.chat, item.dataset.initial, item.dataset.avatar, size);
  const newAvatar = wrapper.firstElementChild;
  newAvatar.id = el.id;
  el.replaceWith(newAvatar);
}

function formatBytes(bytes) {
  if (!bytes) return '';
  const units = ['B', 'KB', 'MB', 'GB'];
  let size = bytes;
  let unit = 0;
  while (size >= 1024 && unit < units.length - 1) {
    size /= 1024;
    unit += 1;
  }
  return `${size.toFixed(size >= 10 || unit === 0 ? 0 : 1)} ${units[unit]}`;
}

function playNotification() {
  if (!notificationReady) return;
  const AudioContext = window.AudioContext || window.webkitAudioContext;
  if (!AudioContext) return;
  const audio = new AudioContext();
  const gain = audio.createGain();
  gain.gain.setValueAtTime(0.0001, audio.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.18, audio.currentTime + 0.02);
  gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.32);
  gain.connect(audio.destination);

  [660, 880].forEach((frequency, index) => {
    const oscillator = audio.createOscillator();
    oscillator.type = 'sine';
    oscillator.frequency.value = frequency;
    oscillator.connect(gain);
    oscillator.start(audio.currentTime + index * 0.09);
    oscillator.stop(audio.currentTime + 0.22 + index * 0.09);
  });
}

document.addEventListener('pointerdown', () => {
  notificationReady = true;
}, { once: true });

function tooltipTextFor(target) {
  return target.dataset.tooltip || target.getAttribute('aria-label') || target.getAttribute('title') || '';
}

function hideTooltip() {
  window.clearTimeout(tooltipTimer);
  activeTooltipTarget = null;
  document.querySelector('#hover-tooltip')?.remove();
}

function showTooltip(target) {
  const text = tooltipTextFor(target);
  if (!text) return;

  document.querySelector('#hover-tooltip')?.remove();
  const tooltip = document.createElement('div');
  tooltip.id = 'hover-tooltip';
  tooltip.className = 'hover-tooltip';
  tooltip.textContent = text;
  document.body.append(tooltip);

  const rect = target.getBoundingClientRect();
  const tooltipRect = tooltip.getBoundingClientRect();
  const top = rect.top - tooltipRect.height - 9;
  const left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

  tooltip.style.top = `${Math.max(8, top)}px`;
  tooltip.style.left = `${Math.min(window.innerWidth - tooltipRect.width - 8, Math.max(8, left))}px`;
  tooltip.classList.add('show');
}

document.addEventListener('pointerover', (event) => {
  const target = event.target.closest('button, a, label.photo-edit-button, label.photo-camera-button, .chat-item');
  if (!target || !tooltipTextFor(target)) return;

  activeTooltipTarget = target;
  window.clearTimeout(tooltipTimer);
  tooltipTimer = window.setTimeout(() => {
    if (activeTooltipTarget === target && target.matches(':hover')) showTooltip(target);
  }, 1300);
});

document.addEventListener('pointerout', (event) => {
  const target = event.target.closest('button, a, label.photo-edit-button, label.photo-camera-button, .chat-item');
  if (!target || !event.relatedTarget || target.contains(event.relatedTarget)) return;
  hideTooltip();
});

document.addEventListener('scroll', hideTooltip, true);

function renderMessage(message) {
  const row = document.createElement('div');
  row.className = `message-row ${message.direction}`;
  row.dataset.messageId = message.id;
  const media = message.media_url && message.media_type === 'photo'
    ? `<a class="message-media" href="${message.media_url}" target="_blank" rel="noreferrer"><img src="${message.media_url}" alt="${message.media_original_name || 'Photo'}"></a>`
    : message.media_url
      ? `<a class="message-media video" href="${message.media_url}" target="_blank" rel="noreferrer"><video src="${message.media_url}" controls></video></a>`
      : '';
  const body = message.body ? `<div class="bubble"></div>` : '';
  const checks = message.direction === 'sent' ? ' <span class="read-mark">✓✓</span>' : '';

  row.innerHTML = `
    <div>
      <span class="sender-label">${message.sender_name}</span>
      ${media}
      ${body}
      <time title="${message.created_at_display}">${message.created_at_display}${checks}</time>
    </div>
  `;

  if (message.body) row.querySelector('.bubble').textContent = message.body;
  return row;
}

async function loadMessages(item) {
  if (!item || !conversation) return;
  const typing = document.querySelector('#typing');
  if (typing) typing.hidden = true;
  document.querySelectorAll('.message-row').forEach((message) => message.remove());

  const response = await fetch(item.dataset.messagesUrl, { headers: { Accept: 'application/json' } });
  const data = await response.json();
  const divider = document.querySelector('.date-divider span');
  if (divider) divider.textContent = data.messages.length ? 'Conversation' : 'No messages yet';

  data.messages.forEach((message) => typing?.before(renderMessage(message)));
  item.querySelector('.unread-badge')?.remove();
  conversation.scrollTop = conversation.scrollHeight;
}

function updateChatPreview(item, message) {
  if (!item || !message) return;

  const preview = item.querySelector('.chat-copy small');
  const timestamp = item.querySelector('time');
  const previewText = message.body || (['photo', 'video'].includes(message.media_type) ? `Sent a ${message.media_type}` : 'Sent media');

  if (preview) preview.textContent = previewText;
  if (timestamp) {
    timestamp.textContent = new Intl.DateTimeFormat(undefined, { timeStyle: 'short' }).format(new Date(message.created_at));
  }
}

async function syncActiveMessages() {
  if (!activeChat || !conversation || isSyncingMessages) return;
  isSyncingMessages = true;

  try {
    const response = await fetch(activeChat.dataset.messagesUrl, { headers: { Accept: 'application/json' } });
    if (!response.ok) return;

    const data = await response.json();
    const messages = data.messages || [];
    const existingIds = new Set([...document.querySelectorAll('.message-row')].map((row) => row.dataset.messageId));
    const newMessages = messages.filter((message) => !existingIds.has(String(message.id)));
    const typing = document.querySelector('#typing');
    const wasNearBottom = conversation.scrollHeight - conversation.scrollTop - conversation.clientHeight < 120;
    const divider = document.querySelector('.date-divider span');

    if (divider) divider.textContent = messages.length ? 'Conversation' : 'No messages yet';
    newMessages.forEach((message) => typing?.before(renderMessage(message)));
    activeChat.querySelector('.unread-badge')?.remove();

    if (newMessages.length) {
      const latestMessage = newMessages[newMessages.length - 1];
      updateChatPreview(activeChat, latestMessage);
      chatList?.prepend(activeChat);
      if (latestMessage.media_url) loadGallery(activeChat);
      if (wasNearBottom || latestMessage.direction === 'sent') {
        conversation.scrollTop = conversation.scrollHeight;
      }
    }
  } finally {
    isSyncingMessages = false;
  }
}

function selectChat(item) {
  chatItems().forEach((chat) => chat.classList.remove('active'));
  item.classList.add('active');
  activeChat = item;
  title.textContent = item.dataset.chat;
  status.innerHTML = `<i class="status-dot"></i> ${item.dataset.status}`;
  detailsName.textContent = item.dataset.chat;
  document.querySelector('.conversation-intro h3').textContent = item.dataset.chat;
  document.querySelector('.conversation').setAttribute('aria-label', `Conversation with ${item.dataset.chat}`);
  setAvatar(document.querySelector('#header-avatar'), item, 'large');
  setAvatar(document.querySelector('#intro-avatar'), item, 'large');
  setAvatar(document.querySelector('#details-avatar'), item, 'xl');
  loadMessages(item);
  loadGallery(item);
  if (window.innerWidth <= 680) setNavCollapsed(true);
}

chatItems().forEach((item) => item.addEventListener('click', () => selectChat(item)));
if (activeChat) selectChat(activeChat);

document.querySelector('#toggle-sidebar')?.addEventListener('click', () => {
  setNavCollapsed(!document.body.classList.contains('nav-collapsed'));
});
document.querySelector('#open-sidebar')?.addEventListener('click', () => setNavCollapsed(false));
document.querySelector('#close-sidebar')?.addEventListener('click', () => setNavCollapsed(true));
document.querySelector('#gallery-button')?.addEventListener('click', () => {
  setDetailsOpen(true);
  if (activeChat) loadGallery(activeChat);
});
detailsButton?.addEventListener('click', () => {
  const isOpen = document.body.classList.contains('details-open');
  setDetailsOpen(!isOpen);
  if (!isOpen && activeChat) loadGallery(activeChat);
});
document.querySelector('#close-details')?.addEventListener('click', () => setDetailsOpen(false));
document.querySelector('#call-button')?.addEventListener('click', () => showToast('Voice call preview started'));
document.querySelector('#video-button')?.addEventListener('click', () => showToast('Video call preview started'));
document.querySelector('#attach-button')?.addEventListener('click', () => fileInput?.click());
fileInput?.addEventListener('change', (event) => {
  selectedMedia = event.target.files[0] || null;
  if (!selectedMedia) {
    selectedFile.hidden = true;
    return;
  }
  selectedFile.textContent = selectedMedia.name;
  selectedFile.hidden = false;
});

async function sendTypingSignal() {
  if (!activeChat || !activeChat.dataset.typingUrl) return;
  const now = Date.now();
  if (now - lastTypingPing < 2500) return;
  lastTypingPing = now;

  await fetch(activeChat.dataset.typingUrl, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
  });
}

input?.addEventListener('input', () => {
  if (input.value.trim()) sendTypingSignal();
});

document.querySelector('#composer')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (!activeChat) return;
  const message = input.value.trim();
  if (!message && !selectedMedia) return;
  const hadMedia = Boolean(selectedMedia);

  const formData = new FormData();
  if (message) formData.append('body', message);
  if (selectedMedia) formData.append('media', selectedMedia);

  const response = await fetch(activeChat.dataset.sendUrl, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    body: formData,
  });

  if (!response.ok) {
    showToast('Could not send that message');
    return;
  }

  input.value = '';
  fileInput.value = '';
  selectedMedia = null;
  selectedFile.hidden = true;
  await loadMessages(activeChat);
  await loadGallery(activeChat);
  updateChatPreview(activeChat, { body: message, media_type: hadMedia ? 'media' : null, created_at: new Date().toISOString() });
  chatList?.prepend(activeChat);
});

document.querySelector('#search-input')?.addEventListener('input', (event) => {
  const query = event.target.value.toLowerCase();
  chatItems().forEach((item) => { item.hidden = !item.textContent.toLowerCase().includes(query); });
});

async function loadGallery(item) {
  if (!item || !mediaGrid) return;
  const response = await fetch(item.dataset.galleryUrl, { headers: { Accept: 'application/json' } });
  const data = await response.json();
  mediaGrid.innerHTML = '';
  galleryCount.textContent = data.items.length;

  data.items.forEach((media) => {
    const button = document.createElement('a');
    button.className = 'gallery-item';
    button.href = media.media_url;
    button.target = '_blank';
    button.rel = 'noreferrer';
    button.dataset.search = `${media.created_at_display} ${media.media_original_name || ''} ${media.media_type || ''} ${media.sender_name}`.toLowerCase();
    button.innerHTML = media.media_type === 'photo'
      ? `<img src="${media.media_url}" alt="${media.media_original_name || 'Photo'}">`
      : `<video src="${media.media_url}"></video>`;
    button.insertAdjacentHTML('beforeend', `<span>${media.created_at_display}<small>${media.media_original_name || media.media_type} ${formatBytes(media.media_size)}</small></span>`);
    mediaGrid.append(button);
  });

  galleryEmpty.hidden = data.items.length > 0;
}

gallerySearch?.addEventListener('input', (event) => {
  const query = event.target.value.toLowerCase();
  [...document.querySelectorAll('.gallery-item')].forEach((item) => {
    item.hidden = !item.dataset.search.includes(query);
  });
});

async function pollUnread() {
  if (!document.querySelector('[data-page="chat"]')) return;
  const response = await fetch('/messages/unread-summary', { headers: { Accept: 'application/json' } });
  const data = await response.json();
  const unreadTotal = Object.values(data.unread || {}).reduce((total, item) => total + Number(item.unread_count), 0);

  Object.entries(data.unread || {}).forEach(([senderId, summary]) => {
    const item = document.querySelector(`.chat-item[data-user-id="${senderId}"]`);
    if (!item || item === activeChat) return;
    let badge = item.querySelector('.unread-badge');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'unread-badge';
      item.querySelector('.chat-copy').after(badge);
    }
    badge.textContent = summary.unread_count;
    chatList?.prepend(item);
  });

  if (unreadTotal > latestUnreadTotal) {
    playNotification();
    showToast('New message received');
  }
  latestUnreadTotal = unreadTotal;
}

async function pollTypingStatus() {
  const typing = document.querySelector('#typing');
  if (!typing || !activeChat?.dataset.typingStatusUrl) return;

  const response = await fetch(activeChat.dataset.typingStatusUrl, { headers: { Accept: 'application/json' } });
  const data = await response.json();
  typing.hidden = !data.typing;
  if (data.typing) conversation.scrollTop = conversation.scrollHeight;
}

document.addEventListener('keydown', (event) => {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
    event.preventDefault();
    document.querySelector('#search-input')?.focus();
  }
});

const themeClasses = ['dark', 'dark-default', 'dark-purple', 'dark-white'];

function applyTheme(theme) {
  const selectedTheme = theme || 'light';
  document.body.classList.remove(...themeClasses);
  if (selectedTheme !== 'light') document.body.classList.add('dark', selectedTheme);
  localStorage.setItem('pm-theme', selectedTheme);
  document.querySelectorAll('.theme-switch button').forEach((button) => button.classList.toggle('active', button.dataset.theme === selectedTheme));
}

document.querySelectorAll('.theme-switch button').forEach((button) => button.addEventListener('click', () => applyTheme(button.dataset.theme)));
applyTheme(localStorage.getItem('pm-theme') || 'light');
document.querySelector('#notification-toggle')?.addEventListener('click', (event) => event.currentTarget.classList.toggle('active'));
document.querySelector('#close-settings')?.addEventListener('click', () => { settingsPanel.hidden = true; });
setInterval(pollUnread, 5000);
setInterval(syncActiveMessages, 2500);
setInterval(pollTypingStatus, 2500);
