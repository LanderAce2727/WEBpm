const chatItems = [...document.querySelectorAll('.chat-item')];
const title = document.querySelector('#chat-title');
const status = document.querySelector('#chat-status');
const headerAvatar = document.querySelector('#header-avatar');
const detailsAvatar = document.querySelector('#details-avatar');
const detailsName = document.querySelector('#details-name');
const detailsPanel = document.querySelector('#details-panel');
const conversation = document.querySelector('#conversation');
const input = document.querySelector('#message-input');
const toast = document.querySelector('#toast');
const sidebar = document.querySelector('.sidebar');
const settingsPanel = document.querySelector('#settings-panel');
const messageSets = {
  'Mum & Dad': [
    ['received', 'Mum', 'Good morning, love. Are we still on for Sunday lunch?', 'Today, 10:38'],
    ['sent', 'You', 'Absolutely. I’ll bring the lemon tart you like.', 'Today, 10:40'],
    ['received', 'Dad', 'Don\'t forget Sunday lunch ♥', 'Today, 10:42'],
    ['sent', 'You', 'Wouldn’t miss it. See you both at 1pm!', 'Today, 10:42'],
  ],
  Sofia: [
    ['received', 'Sofia', 'I found the perfect place for our weekend walk.', 'Yesterday, 18:12'],
    ['received', 'Sofia', 'Sent a photo', 'Yesterday, 18:13'],
    ['sent', 'You', 'It looks lovely. I’m in!', 'Yesterday, 18:20'],
  ],
  Leo: [
    ['received', 'Leo', 'That view is unreal.', 'Tuesday, 16:04'],
    ['sent', 'You', 'You have to send me the route.', 'Tuesday, 16:07'],
  ],
  'Family plans': [
    ['received', 'Alex', 'I can bring dessert.', 'Monday, 12:28'],
    ['sent', 'You', 'Perfect. I’ll bring drinks for everyone.', 'Monday, 12:31'],
  ],
};

function renderMessages(name) {
  document.querySelectorAll('.message-row').forEach((message) => message.remove());
  const typing = document.querySelector('#typing');
  messageSets[name].forEach(([direction, sender, text, sentAt]) => {
    const row = document.createElement('div');
    row.className = `message-row ${direction}`;
    const avatar = direction === 'received'
      ? `<div class="message-avatar"><img src="${document.querySelector('.chat-item.active').dataset.avatar}" alt="${sender}" /></div>`
      : '';
    const checks = direction === 'sent' ? ' <span class="read-mark">✓✓</span>' : '';
    row.innerHTML = `${avatar}<div><span class="sender-label"></span><div class="bubble"></div><time></time></div>`;
    row.querySelector('.sender-label').textContent = sender;
    row.querySelector('.bubble').textContent = text;
    row.querySelector('time').innerHTML = `${sentAt}${checks}`;
    typing.before(row);
  });
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add('show');
  window.clearTimeout(showToast.timer);
  showToast.timer = window.setTimeout(() => toast.classList.remove('show'), 2200);
}

function selectChat(item) {
  chatItems.forEach((chat) => chat.classList.remove('active'));
  item.classList.add('active');
  const name = item.dataset.chat;
  title.textContent = name;
  status.innerHTML = `<i class="status-dot"></i> ${item.dataset.status}`;
  headerAvatar.src = item.dataset.avatar;
  detailsAvatar.src = item.dataset.avatar;
  detailsName.textContent = name;
  document.querySelector('.conversation-intro h3').textContent = name;
  document.querySelector('.conversation').setAttribute('aria-label', `Conversation with ${name}`);
  renderMessages(name);
  if (window.innerWidth <= 680) sidebar.classList.remove('open');
}

chatItems.forEach((item) => item.addEventListener('click', () => selectChat(item)));
document.querySelector('#open-sidebar')?.addEventListener('click', () => sidebar.classList.add('open'));
document.querySelector('#close-sidebar')?.addEventListener('click', () => sidebar.classList.remove('open'));
document.querySelector('#gallery-button')?.addEventListener('click', () => {
  detailsPanel.style.display = 'block';
  showToast('Shared moments opened');
});
document.querySelector('#close-details')?.addEventListener('click', () => detailsPanel.style.display = 'none');
document.querySelector('#call-button')?.addEventListener('click', () => showToast('Voice call preview started'));
document.querySelector('#video-button')?.addEventListener('click', () => showToast('Video call preview started'));
document.querySelector('#new-chat')?.addEventListener('click', () => showToast('New family chat is ready to set up'));
document.querySelector('#attach-button')?.addEventListener('click', () => document.querySelector('#file-input').click());
document.querySelector('#file-input')?.addEventListener('change', (event) => {
  if (event.target.files[0]) showToast(`${event.target.files[0].name} attached`);
});

document.querySelector('#composer')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const message = input.value.trim();
  if (!message) return;
  const row = document.createElement('div');
  row.className = 'message-row sent';
  row.innerHTML = `<div><span class="sender-label">You</span><div class="bubble"></div><time></time></div>`;
  row.querySelector('.bubble').textContent = message;
  row.querySelector('time').innerHTML = `${new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date())} <span class="read-mark">✓✓</span>`;
  document.querySelector('#typing').before(row);
  input.value = '';
  conversation.scrollTop = conversation.scrollHeight;
});

document.querySelector('#search-input')?.addEventListener('input', (event) => {
  const query = event.target.value.toLowerCase();
  chatItems.forEach((item) => { item.hidden = !item.textContent.toLowerCase().includes(query); });
});
document.addEventListener('keydown', (event) => {
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); document.querySelector('#search-input').focus(); }
});

const themeClasses = ['dark', 'dark-purple', 'dark-white'];

function applyTheme(theme) {
  const selectedTheme = theme === 'dark' ? 'dark-purple' : theme;
  document.body.classList.remove(...themeClasses);
  if (selectedTheme !== 'light') document.body.classList.add('dark', selectedTheme);
  localStorage.setItem('pm-theme', selectedTheme);
  document.querySelectorAll('.theme-switch button').forEach((button) => button.classList.toggle('active', button.dataset.theme === selectedTheme));
}

document.querySelectorAll('.theme-switch button').forEach((button) => button.addEventListener('click', () => applyTheme(button.dataset.theme)));
applyTheme(localStorage.getItem('pm-theme') || 'light');
document.querySelector('#notification-toggle')?.addEventListener('click', (event) => event.currentTarget.classList.toggle('active'));
document.querySelector('#close-settings')?.addEventListener('click', () => { settingsPanel.hidden = true; });

document.querySelectorAll('.nav-tab').forEach((tab) => tab.addEventListener('click', () => {
  document.querySelectorAll('.nav-tab').forEach((item) => item.classList.remove('active'));
  tab.classList.add('active');
  if (tab.dataset.view === 'settings' && settingsPanel) settingsPanel.hidden = false;
  if (tab.dataset.view === 'profile') showToast('Profile view selected');
}));
