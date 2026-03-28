<?php
// ============================================================
// messages.php — FanZone | Messages
// MVC: This view receives data from your Controller/Model.
// Replace the variables below with data from your DB queries.
// ============================================================

// -- Current logged-in user (from session)
$currentUser  = $_SESSION['user'] ?? [];
$userInitials = strtoupper(substr($currentUser['username'] ?? '', 0, 2));

// -- Conversation list → from your MessageModel
// Expected keys per row: id, name, initials, avatar_color, preview, time_ago, unread_count (int), is_online (bool), fandom
$conversations = $conversations ?? [];

// -- Active conversation ID (from query string or default to first)
$activeId   = isset($_GET['conv']) ? (int)$_GET['conv'] : ($conversations[0]['id'] ?? 0);
$activeConv = null;
foreach ($conversations as $c) {
    if ($c['id'] === $activeId) { $activeConv = $c; break; }
}
if (!$activeConv && !empty($conversations)) $activeConv = $conversations[0];

// -- Messages for active conversation → from your MessageModel
// Expected keys per row: from ('me'|'other'), text, timestamp
$messages = $messages ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FanZone – Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-primary:   #0d0d0f;
            --bg-secondary: #141418;
            --bg-card:      #1a1a20;
            --bg-hover:     #22222a;
            --border:       #2a2a35;
            --text-primary: #f0f0f5;
            --text-muted:   #888899;
            --accent:       #a855f7;
            --accent-pink:  #ec4899;
            --green:        #22c55e;
        }
        body { font-family: 'DM Sans', system-ui, sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; display: flex; flex-direction: column; }

        /* Navbar */
        .navbar { position: sticky; top: 0; z-index: 100; height: 56px; background: rgba(13,13,15,0.9); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 24px; gap: 20px; }
        .navbar-logo { font-family: 'Syne', sans-serif; font-size: 1.35rem; font-weight: 800; color: var(--accent); letter-spacing: -0.5px; margin-right: auto; text-decoration: none; }
        .navbar-logo span { color: var(--accent-pink); }
        .nav-links { display: flex; gap: 4px; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-size: 0.88rem; font-weight: 500; padding: 6px 14px; border-radius: 8px; transition: color 0.2s, background 0.2s; }
        .nav-links a:hover { color: var(--text-primary); background: var(--bg-card); }
        .nav-links a.active { color: var(--accent); background: rgba(168,85,247,0.1); }
        .nav-right { display: flex; align-items: center; gap: 8px; }
        .icon-btn { background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; transition: background 0.2s; }
        .icon-btn:hover { background: var(--bg-card); color: var(--text-primary); }
        .nav-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-pink)); display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.72rem; font-weight: 700; color: #fff; cursor: pointer; border: none; flex-shrink: 0; text-decoration: none; }

        /* Messages Layout */
        .messages-layout { display: flex; flex: 1; height: calc(100vh - 56px); overflow: hidden; }

        /* DM List */
        .dm-list { width: 300px; flex-shrink: 0; border-right: 1px solid var(--border); background: var(--bg-secondary); display: flex; flex-direction: column; }
        .dm-list-header { padding: 16px 18px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .dm-list-header h2 { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; }
        .dm-new-btn { background: none; border: none; cursor: pointer; color: var(--text-muted); transition: color 0.2s; display: flex; align-items: center; }
        .dm-new-btn:hover { color: var(--accent); }
        .dm-new-btn svg { width: 20px; height: 20px; }
        .dm-search-wrap { padding: 10px 12px; border-bottom: 1px solid var(--border); position: relative; }
        .dm-search-wrap svg { position: absolute; left: 22px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 15px; height: 15px; pointer-events: none; }
        .dm-search { width: 100%; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 7px 10px 7px 32px; color: var(--text-primary); font-size: 0.82rem; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; }
        .dm-search::placeholder { color: var(--text-muted); }
        .dm-search:focus { border-color: var(--accent); }
        .dm-items { overflow-y: auto; flex: 1; }

        .dm-item { display: flex; align-items: center; gap: 11px; padding: 13px 16px; cursor: pointer; transition: background 0.15s; text-decoration: none; color: inherit; border-bottom: 1px solid var(--border); }
        .dm-item:hover { background: var(--bg-hover); }
        .dm-item.active { background: rgba(168,85,247,0.08); }
        .dm-av-wrap { position: relative; flex-shrink: 0; }
        .dm-av { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.78rem; font-weight: 700; color: #fff; }
        .online-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--green); border: 2px solid var(--bg-secondary); position: absolute; bottom: 1px; right: 1px; }
        .dm-meta { flex: 1; min-width: 0; }
        .dm-meta-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
        .dm-name { font-size: 0.88rem; font-weight: 600; }
        .dm-time { font-size: 0.72rem; color: var(--text-muted); }
        .dm-preview-row { display: flex; align-items: center; gap: 5px; }
        .dm-preview { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
        .unread-badge { background: var(--accent); color: #fff; border-radius: 50%; width: 17px; height: 17px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.62rem; font-weight: 700; flex-shrink: 0; }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 0.82rem; }
        .empty-state svg { width: 36px; height: 36px; margin: 0 auto 10px; display: block; opacity: 0.35; }

        /* Chat Panel */
        .chat-panel { flex: 1; display: flex; flex-direction: column; background: var(--bg-primary); }
        .chat-no-selection { flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 10px; color: var(--text-muted); font-size: 0.88rem; }
        .chat-no-selection svg { width: 42px; height: 42px; opacity: 0.25; }

        .chat-header { padding: 13px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; background: var(--bg-secondary); }
        .chat-header-av { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.75rem; font-weight: 700; color: #fff; position: relative; flex-shrink: 0; }
        .chat-header-info { flex: 1; }
        .chat-header-name { font-family: 'Syne', sans-serif; font-size: 0.95rem; font-weight: 700; }
        .chat-badges { display: flex; align-items: center; gap: 7px; margin-top: 2px; }
        .fandom-badge { display: inline-block; padding: 1px 8px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
        .badge-anime  { background: rgba(168,85,247,0.18); color: #c084fc; }
        .badge-games  { background: rgba(34,197,94,0.14);  color: #4ade80; }
        .badge-movies { background: rgba(251,146,60,0.14); color: #fb923c; }
        .online-label { font-size: 0.75rem; color: var(--green); font-weight: 500; }

        .chat-date { text-align: center; font-size: 0.72rem; color: var(--text-muted); margin: 12px 0; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 18px 20px; display: flex; flex-direction: column; gap: 10px; }

        .msg-row { display: flex; align-items: flex-end; gap: 8px; }
        .msg-row.me { flex-direction: row-reverse; }
        .msg-av { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.6rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .msg-col { display: flex; flex-direction: column; }
        .msg-row.me .msg-col { align-items: flex-end; }
        .bubble { max-width: 55%; padding: 9px 13px; border-radius: 16px; font-size: 0.85rem; line-height: 1.5; }
        .msg-row.other .bubble { background: var(--bg-card); border: 1px solid var(--border); border-bottom-left-radius: 4px; }
        .msg-row.me .bubble { background: linear-gradient(135deg, var(--accent), var(--accent-pink)); color: #fff; border-bottom-right-radius: 4px; }
        .msg-ts { font-size: 0.68rem; color: var(--text-muted); margin-top: 3px; }
        .msg-row.me .msg-ts { text-align: right; }

        .chat-input-bar { padding: 12px 16px; border-top: 1px solid var(--border); background: var(--bg-secondary); display: flex; align-items: center; gap: 10px; }
        .icon-action { background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; transition: color 0.2s; }
        .icon-action:hover { color: var(--accent); }
        .icon-action svg { width: 20px; height: 20px; }
        .chat-input { flex: 1; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 9px 13px; color: var(--text-primary); font-size: 0.88rem; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; }
        .chat-input::placeholder { color: var(--text-muted); }
        .chat-input:focus { border-color: var(--accent); }
        .send-btn { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-pink)); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: opacity 0.2s; }
        .send-btn:hover { opacity: 0.85; }
        .send-btn svg { width: 15px; height: 15px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-logo">FAN<span>ZONE</span></a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="explore.php">Explore</a>
        <a href="messages.php" class="active">Messages</a>
    </div>
    <div class="nav-right">
        <button class="icon-btn" title="Toggle theme">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
        <a href="profile.php" class="nav-avatar"><?= htmlspecialchars($userInitials) ?></a>
    </div>
</nav>

<div class="messages-layout">

    <!-- DM List -->
    <div class="dm-list">
        <div class="dm-list-header">
            <h2>Messages</h2>
            <button class="dm-new-btn" title="New message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </div>
        <div class="dm-search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="dm-search" placeholder="Search DMs" />
        </div>
        <div class="dm-items">
            <?php if (empty($conversations)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                No conversations yet.
            </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                <a
                    href="messages.php?conv=<?= (int)$conv['id'] ?>"
                    class="dm-item <?= $conv['id'] === $activeId ? 'active' : '' ?>"
                >
                    <div class="dm-av-wrap">
                        <div class="dm-av" style="background:<?= htmlspecialchars($conv['avatar_color']) ?>">
                            <?= htmlspecialchars($conv['initials']) ?>
                        </div>
                        <?php if (!empty($conv['is_online'])): ?>
                        <div class="online-dot"></div>
                        <?php endif; ?>
                    </div>
                    <div class="dm-meta">
                        <div class="dm-meta-top">
                            <span class="dm-name"><?= htmlspecialchars($conv['name']) ?></span>
                            <span class="dm-time"><?= htmlspecialchars($conv['time_ago']) ?></span>
                        </div>
                        <div class="dm-preview-row">
                            <span class="dm-preview"><?= htmlspecialchars($conv['preview']) ?></span>
                            <?php if (!empty($conv['unread_count'])): ?>
                            <span class="unread-badge"><?= (int)$conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Panel -->
    <div class="chat-panel">
        <?php if (!$activeConv): ?>
        <div class="chat-no-selection" style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;color:var(--text-muted);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:42px;height:42px;opacity:0.25;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Select a conversation to start chatting.
        </div>
        <?php else: ?>
        <div class="chat-header">
            <div class="chat-header-av" style="background:<?= htmlspecialchars($activeConv['avatar_color']) ?>">
                <?= htmlspecialchars($activeConv['initials']) ?>
                <?php if (!empty($activeConv['is_online'])): ?>
                <div class="online-dot" style="border-color:var(--bg-secondary);"></div>
                <?php endif; ?>
            </div>
            <div class="chat-header-info">
                <div class="chat-header-name"><?= htmlspecialchars($activeConv['name']) ?></div>
                <div class="chat-badges">
                    <?php if (!empty($activeConv['fandom'])): ?>
                    <span class="fandom-badge badge-<?= strtolower(htmlspecialchars($activeConv['fandom'])) ?>">
                        <?= htmlspecialchars($activeConv['fandom']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($activeConv['is_online'])): ?>
                    <span class="online-label">Online</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php if (empty($messages)): ?>
            <div class="chat-no-selection" style="flex:1;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:36px;height:36px;opacity:0.25;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                No messages yet. Say hello!
            </div>
            <?php else: ?>
            <div class="chat-date">Today &bull; <?= date('F j, Y') ?></div>
            <?php foreach ($messages as $msg): ?>
            <div class="msg-row <?= $msg['from'] === 'me' ? 'me' : 'other' ?>">
                <?php if ($msg['from'] !== 'me'): ?>
                <div class="msg-av" style="background:<?= htmlspecialchars($activeConv['avatar_color']) ?>">
                    <?= htmlspecialchars($activeConv['initials']) ?>
                </div>
                <?php endif; ?>
                <div class="msg-col">
                    <div class="bubble"><?= htmlspecialchars($msg['text']) ?></div>
                    <div class="msg-ts"><?= htmlspecialchars($msg['timestamp']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="chat-input-bar">
            <button class="icon-action" title="Attach file">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <input
                type="text"
                class="chat-input"
                id="msgInput"
                placeholder="Message"
                onkeydown="if(event.key==='Enter') sendMessage()"
            />
            <button class="icon-action" title="Emoji">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            </button>
            <button class="send-btn" onclick="sendMessage()" title="Send">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function sendMessage() {
    const input = document.getElementById('msgInput');
    const text  = input.value.trim();
    if (!text) return;
    const container = document.getElementById('chatMessages');
    const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const row = document.createElement('div');
    row.className = 'msg-row me';
    row.innerHTML = `<div class="msg-col"><div class="bubble">${esc(text)}</div><div class="msg-ts">${now}</div></div>`;
    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
    input.value = '';
    // TODO: POST message to your MessageController → /message/send
}
function esc(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
window.addEventListener('load', () => {
    const c = document.getElementById('chatMessages');
    if (c) c.scrollTop = c.scrollHeight;
});
</script>
</body>
</html>
