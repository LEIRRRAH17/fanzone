
// Apply saved theme immediately (before render)
(function () {
  const saved = localStorage.getItem('fz-theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  updateIcon(saved);
})();

function toggleTheme() {
  const html    = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next    = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('fz-theme', next);
  updateIcon(next);
}

function updateIcon(theme) {
  const btn = document.getElementById('ttoggle');
  if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}