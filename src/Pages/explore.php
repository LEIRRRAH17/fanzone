<?php
// ============================================================
// explore.php — FanZone | Explore Fandoms
// MVC: This view receives data from your Controller/Model.
// Replace the variables below with data from your DB queries.
// ============================================================

// -- Current logged-in user (from session, set by your Auth controller)
$currentUser = $_SESSION['user'] ?? [];
$userInitials = strtoupper(substr($currentUser['username'] ?? '', 0, 2));

// -- Trending hashtags → from your TrendingModel
// Expected keys per row: tag (string), posts (int)
$trending = $trending ?? [];

// -- Fans to follow → from your UserModel
// Expected keys per row: id, initials, name, handle, avatar_color
$fansToFollow = $fansToFollow ?? [];

// -- Fandom cards → from your FandomModel
// Expected keys per row: id, name, category, members (int), is_hot (bool)
$fandoms = $fandoms ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FanZone – Explore</title>
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
            --orange:       #f97316;
            --sidebar-w:    240px;
            --right-w:      300px;
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

        /* Layout */
        .page-body { display: flex; flex: 1; max-width: 1400px; margin: 0 auto; width: 100%; padding: 0 12px; }

        /* Left Sidebar */
        .sidebar { width: var(--sidebar-w); flex-shrink: 0; padding: 20px 8px; position: sticky; top: 56px; height: calc(100vh - 56px); overflow-y: auto; }
        .sb-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); padding: 0 10px; margin-bottom: 6px; }
        .sb-link { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; text-decoration: none; color: var(--text-muted); font-size: 0.88rem; font-weight: 500; transition: background 0.15s, color 0.15s; margin-bottom: 1px; }
        .sb-link:hover { background: var(--bg-card); color: var(--text-primary); }
        .sb-link.active { background: rgba(168,85,247,0.12); color: var(--accent); }
        .sb-link svg { width: 17px; height: 17px; flex-shrink: 0; }
        .sb-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
        .sb-divider { height: 1px; background: var(--border); margin: 12px 10px; }

        /* Main */
        .main-content { flex: 1; min-width: 0; padding: 20px 14px; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 18px; margin-bottom: 14px; }
        .page-title { font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 800; margin-bottom: 14px; }

        /* Search */
        .search-wrap { position: relative; }
        .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 16px; height: 16px; pointer-events: none; }
        .search-input { width: 100%; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px 10px 38px; color: var(--text-primary); font-size: 0.88rem; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; }
        .search-input::placeholder { color: var(--text-muted); }
        .search-input:focus { border-color: var(--accent); }

        .section-title { font-family: 'Syne', sans-serif; font-size: 0.95rem; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }

        /* Fandoms grid */
        .fandoms-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .fandom-card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px; padding: 14px; transition: border-color 0.2s, background 0.2s; }
        .fandom-card:hover { border-color: var(--accent); background: var(--bg-hover); }
        .fdom-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .fdom-name { font-family: 'Syne', sans-serif; font-size: 0.9rem; font-weight: 700; margin-bottom: 5px; }
        .fdom-badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
        .badge-anime  { background: rgba(168,85,247,0.18); color: #c084fc; }
        .badge-games  { background: rgba(34,197,94,0.14);  color: #4ade80; }
        .badge-movies { background: rgba(251,146,60,0.14); color: #fb923c; }
        .hot-label { font-size: 0.68rem; color: var(--orange); font-weight: 600; display: flex; align-items: center; gap: 3px; }
        .fdom-bottom { display: flex; justify-content: space-between; align-items: center; }
        .members-txt { font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
        .members-txt svg { width: 13px; height: 13px; }
        .btn-join { background: linear-gradient(135deg, var(--accent), var(--accent-pink)); color: #fff; border: none; border-radius: 7px; padding: 5px 14px; font-size: 0.78rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: opacity 0.2s; }
        .btn-join:hover { opacity: 0.85; }
        .btn-join.joined { background: transparent; border: 1px solid var(--accent); color: var(--accent); }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 0.85rem; }
        .empty-state svg { width: 36px; height: 36px; margin: 0 auto 10px; display: block; opacity: 0.35; }

        /* Right Sidebar */
        .right-sidebar { width: var(--right-w); flex-shrink: 0; padding: 20px 8px; position: sticky; top: 56px; height: calc(100vh - 56px); overflow-y: auto; }
        .widget-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 16px; margin-bottom: 12px; }
        .widget-title { font-family: 'Syne', sans-serif; font-size: 0.9rem; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 7px; }
        .widget-title svg { width: 16px; height: 16px; color: var(--accent); }
        .trend-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid var(--border); font-size: 0.82rem; }
        .trend-row:last-child { border-bottom: none; }
        .trend-n { color: var(--text-muted); font-weight: 600; width: 14px; font-size: 0.78rem; }
        .trend-tag { color: var(--accent); font-weight: 600; flex: 1; }
        .trend-posts { font-size: 0.72rem; color: var(--text-muted); }
        .fan-row { display: flex; align-items: center; gap: 9px; padding: 7px 0; }
        .fan-av { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 700; color: #fff; flex-shrink: 0; font-family: 'Syne', sans-serif; }
        .fan-info { flex: 1; min-width: 0; }
        .fan-name  { font-size: 0.82rem; font-weight: 600; }
        .fan-handle { font-size: 0.7rem; color: var(--text-muted); }
        .btn-follow { background: linear-gradient(135deg, var(--accent), var(--accent-pink)); color: #fff; border: none; border-radius: 7px; padding: 4px 12px; font-size: 0.72rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; white-space: nowrap; transition: opacity 0.2s; }
        .btn-follow:hover { opacity: 0.85; }
        .btn-follow.following { background: transparent; border: 1px solid var(--accent); color: var(--accent); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-logo">FAN<span>ZONE</span></a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="explore.php" class="active">Explore</a>
        <a href="messages.php">Messages</a>
    </div>
    <div class="nav-right">
        <button class="icon-btn" title="Toggle theme">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
        <a href="profile.php" class="nav-avatar"><?= htmlspecialchars($userInitials) ?></a>
    </div>
</nav>

<div class="page-body">

    <aside class="sidebar">
        <div class="sb-label">Menu</div>
        <a href="index.php" class="sb-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
            Newsfeed
        </a>
        <a href="profile.php" class="sb-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            My Profile
        </a>
        <a href="messages.php" class="sb-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Messages
        </a>
        <div class="sb-divider"></div>
        <div class="sb-label">Fandoms</div>
        <a href="?category=anime" class="sb-link"><span class="sb-icon" style="background:rgba(168,85,247,0.18)">📺</span>Anime</a>
        <a href="?category=games" class="sb-link"><span class="sb-icon" style="background:rgba(34,197,94,0.14)">🎮</span>Games</a>
        <a href="?category=movies" class="sb-link"><span class="sb-icon" style="background:rgba(251,146,60,0.14)">🎬</span>Movies</a>
    </aside>

    <main class="main-content">
        <div class="card">
            <div class="page-title">Explore Fandoms</div>
            <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" id="searchInput" placeholder="Search for anime, games, movies..." oninput="filterFandoms(this.value)" />
            </div>
        </div>

        <div class="card">
            <div class="section-title">🔥 Trending Fandoms</div>
            <?php if (empty($fandoms)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                No fandoms available yet.
            </div>
            <?php else: ?>
            <div class="fandoms-grid" id="fandomsGrid">
                <?php foreach ($fandoms as $f):
                    $cat = strtolower(htmlspecialchars($f['category']));
                ?>
                <div class="fandom-card" data-name="<?= strtolower(htmlspecialchars($f['name'])) ?>">
                    <div class="fdom-top">
                        <div>
                            <div class="fdom-name"><?= htmlspecialchars($f['name']) ?></div>
                            <span class="fdom-badge badge-<?= $cat ?>"><?= ucfirst($cat) ?></span>
                        </div>
                        <?php if (!empty($f['is_hot'])): ?>
                        <div class="hot-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            Hot
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="fdom-bottom">
                        <div class="members-txt">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                            <?= number_format($f['members']) ?> members
                        </div>
                        <button class="btn-join" data-id="<?= (int)$f['id'] ?>" onclick="toggleJoin(this)">Join</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <aside class="right-sidebar">
        <div class="widget-card">
            <div class="widget-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                Trending
            </div>
            <?php if (empty($trending)): ?>
                <p style="font-size:0.78rem;color:var(--text-muted)">No trending topics yet.</p>
            <?php else: ?>
                <?php foreach ($trending as $i => $t): ?>
                <div class="trend-row">
                    <span class="trend-n"><?= $i + 1 ?></span>
                    <span class="trend-tag"><?= htmlspecialchars($t['tag']) ?></span>
                    <span class="trend-posts"><?= number_format($t['posts']) ?> posts</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="widget-card">
            <div class="widget-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/><line x1="20" y1="8" x2="20" y2="14"/></svg>
                Fans to Follow
            </div>
            <?php if (empty($fansToFollow)): ?>
                <p style="font-size:0.78rem;color:var(--text-muted)">No suggestions right now.</p>
            <?php else: ?>
                <?php foreach ($fansToFollow as $fan): ?>
                <div class="fan-row">
                    <div class="fan-av" style="background:<?= htmlspecialchars($fan['avatar_color']) ?>"><?= htmlspecialchars($fan['initials']) ?></div>
                    <div class="fan-info">
                        <div class="fan-name"><?= htmlspecialchars($fan['name']) ?></div>
                        <div class="fan-handle"><?= htmlspecialchars($fan['handle']) ?></div>
                    </div>
                    <button class="btn-follow" data-id="<?= (int)$fan['id'] ?>" onclick="toggleFollow(this)">Follow</button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>
</div>

<script>
function filterFandoms(q) {
    document.querySelectorAll('#fandomsGrid .fandom-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q.toLowerCase().trim()) ? '' : 'none';
    });
}
function toggleJoin(btn) {
    const joined = btn.classList.toggle('joined');
    btn.textContent = joined ? 'Joined' : 'Join';
    // TODO: POST to your FandomController → /fandom/join/{id}
}
function toggleFollow(btn) {
    const following = btn.classList.toggle('following');
    btn.textContent = following ? 'Following' : 'Follow';
    // TODO: POST to your UserController → /user/follow/{id}
}
</script>
</body>
</html>
