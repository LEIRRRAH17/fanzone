<?php
// ============================================================
// profile.php — FanZone | My Profile
// MVC: This view receives data from your Controller/Model.
// Replace the variables below with data from your DB queries.
// ============================================================

// -- Profile data → from your UserModel (fetched by ProfileController)
// Expected keys: initials, name, handle, joined, bio, posts (int), followers (int), following (int)
$profile = $profile ?? [];

// -- Fandom tags on profile → from your UserFandomModel
// Expected: array of strings e.g. ['Anime', 'Games', 'Movies']
$profileFandoms = $profileFandoms ?? [];

// -- User's own posts → from your PostModel
// Expected keys per row: id, fandom, time_ago, content, likes (int), comments (int), reposts (int)
$userPosts = $userPosts ?? [];

// -- Trending + fans to follow (sidebar) → same as other pages
$trending    = $trending    ?? [];
$fansToFollow = $fansToFollow ?? [];

// -- Fandom badge color map
$fandomColors = [
    'anime'  => ['bg' => 'rgba(168,85,247,0.18)', 'text' => '#c084fc'],
    'games'  => ['bg' => 'rgba(34,197,94,0.14)',  'text' => '#4ade80'],
    'movies' => ['bg' => 'rgba(251,146,60,0.14)', 'text' => '#fb923c'],
];

// -- Current user initials (navbar)
$userInitials = !empty($profile['initials']) ? htmlspecialchars($profile['initials']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FanZone – <?= htmlspecialchars($profile['name'] ?? 'My Profile') ?></title>
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

        /* Profile Card */
        .profile-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; margin-bottom: 14px; }
        .profile-banner { height: 160px; background: linear-gradient(135deg, #a855f7 0%, #ec4899 55%, #f97316 100%); }
        .profile-body { padding: 0 22px 22px; }
        .profile-av-row { display: flex; align-items: flex-end; justify-content: space-between; margin-top: -46px; margin-bottom: 12px; }
        .profile-av { width: 92px; height: 92px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-pink)); display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 1.9rem; font-weight: 800; color: #fff; border: 4px solid var(--bg-card); flex-shrink: 0; }
        .btn-edit { background: transparent; border: 1px solid var(--border); color: var(--text-primary); border-radius: 8px; padding: 7px 18px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: border-color 0.2s, color 0.2s; }
        .btn-edit:hover { border-color: var(--accent); color: var(--accent); }
        .profile-name { font-family: 'Syne', sans-serif; font-size: 1.2rem; font-weight: 800; margin-bottom: 2px; }
        .profile-handle { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 10px; }
        .profile-bio { font-size: 0.88rem; color: var(--text-muted); line-height: 1.55; margin-bottom: 12px; }
        .profile-fandom-tags { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 18px; }
        .profile-fdom-tag { padding: 3px 12px; border-radius: 20px; font-size: 0.74rem; font-weight: 600; }

        .profile-stats { display: flex; border-top: 1px solid var(--border); padding-top: 18px; }
        .stat-item { flex: 1; text-align: center; border-right: 1px solid var(--border); }
        .stat-item:last-child { border-right: none; }
        .stat-num { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--accent); }
        .stat-label { font-size: 0.74rem; color: var(--text-muted); margin-top: 2px; }

        /* Tabs */
        .post-tabs { display: flex; border-bottom: 1px solid var(--border); margin-bottom: 14px; }
        .tab-btn { background: none; border: none; color: var(--text-muted); font-size: 0.88rem; font-weight: 600; padding: 9px 20px; cursor: pointer; border-bottom: 2px solid transparent; font-family: 'DM Sans', sans-serif; transition: color 0.2s, border-color 0.2s; }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab-btn:hover:not(.active) { color: var(--text-primary); }

        /* Post cards */
        .post-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 16px 18px; margin-bottom: 12px; transition: border-color 0.2s; }
        .post-card:hover { border-color: rgba(168,85,247,0.4); }
        .post-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .post-av { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-pink)); display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.7rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .post-meta { flex: 1; }
        .post-uname { font-size: 0.88rem; font-weight: 700; }
        .fandom-badge { display: inline-block; padding: 1px 8px; border-radius: 20px; font-size: 0.66rem; font-weight: 600; margin-left: 6px; }
        .badge-anime  { background: rgba(168,85,247,0.18); color: #c084fc; }
        .badge-games  { background: rgba(34,197,94,0.14);  color: #4ade80; }
        .badge-movies { background: rgba(251,146,60,0.14); color: #fb923c; }
        .post-time { font-size: 0.72rem; color: var(--text-muted); margin-top: 1px; }
        .post-body { font-size: 0.86rem; line-height: 1.6; margin-bottom: 12px; }
        .post-actions { display: flex; gap: 18px; }
        .act-btn { display: flex; align-items: center; gap: 5px; background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 0.78rem; font-weight: 500; font-family: 'DM Sans', sans-serif; transition: color 0.2s; }
        .act-btn:hover { color: var(--accent); }
        .act-btn svg { width: 15px; height: 15px; }

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
        .fan-av { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 0.68rem; font-weight: 700; color: #fff; flex-shrink: 0; }
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
        <a href="explore.php">Explore</a>
        <a href="messages.php">Messages</a>
    </div>
    <div class="nav-right">
        <button class="icon-btn" title="Toggle theme">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
        <a href="profile.php" class="nav-avatar"><?= $userInitials ?></a>
    </div>
</nav>

<div class="page-body">

    <aside class="sidebar">
        <div class="sb-label">Menu</div>
        <a href="index.php" class="sb-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
            Newsfeed
        </a>
        <a href="profile.php" class="sb-link active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            My Profile
        </a>
        <a href="messages.php" class="sb-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Messages
        </a>
        <div class="sb-divider"></div>
        <div class="sb-label">Fandoms</div>
        <a href="explore.php?category=anime" class="sb-link"><span class="sb-icon" style="background:rgba(168,85,247,0.18)">📺</span>Anime</a>
        <a href="explore.php?category=games" class="sb-link"><span class="sb-icon" style="background:rgba(34,197,94,0.14)">🎮</span>Games</a>
        <a href="explore.php?category=movies" class="sb-link"><span class="sb-icon" style="background:rgba(251,146,60,0.14)">🎬</span>Movies</a>
    </aside>

    <main class="main-content">

        <?php if (empty($profile)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Profile not found.
        </div>
        <?php else: ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-banner"></div>
            <div class="profile-body">
                <div class="profile-av-row">
                    <div class="profile-av"><?= htmlspecialchars($profile['initials']) ?></div>
                    <button class="btn-edit">Edit Profile</button>
                </div>
                <div class="profile-name"><?= htmlspecialchars($profile['name']) ?></div>
                <div class="profile-handle">
                    <?= htmlspecialchars($profile['handle']) ?> &bull; Joined <?= htmlspecialchars($profile['joined']) ?>
                </div>
                <?php if (!empty($profile['bio'])): ?>
                <div class="profile-bio"><?= htmlspecialchars($profile['bio']) ?></div>
                <?php endif; ?>

                <?php if (!empty($profileFandoms)): ?>
                <div class="profile-fandom-tags">
                    <?php foreach ($profileFandoms as $fdom):
                        $key = strtolower($fdom);
                        $fc  = $fandomColors[$key] ?? ['bg'=>'rgba(255,255,255,0.08)','text'=>'#aaa'];
                    ?>
                    <span class="profile-fdom-tag" style="background:<?= $fc['bg'] ?>;color:<?= $fc['text'] ?>">
                        <?= htmlspecialchars($fdom) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-num"><?= number_format($profile['posts'] ?? 0) ?></div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num"><?= number_format($profile['followers'] ?? 0) ?></div>
                        <div class="stat-label">Followers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num"><?= number_format($profile['following'] ?? 0) ?></div>
                        <div class="stat-label">Following</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post Tabs -->
        <div class="post-tabs">
            <button class="tab-btn active" onclick="switchTab(this,'posts')">Posts</button>
            <button class="tab-btn" onclick="switchTab(this,'media')">Media</button>
            <button class="tab-btn" onclick="switchTab(this,'likes')">Likes</button>
        </div>

        <div id="tab-posts">
            <?php if (empty($userPosts)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                No posts yet.
            </div>
            <?php else: ?>
                <?php foreach ($userPosts as $post):
                    $cat = strtolower($post['fandom'] ?? '');
                ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-av"><?= htmlspecialchars($profile['initials']) ?></div>
                        <div class="post-meta">
                            <div>
                                <span class="post-uname"><?= htmlspecialchars($profile['name']) ?></span>
                                <?php if (!empty($post['fandom'])): ?>
                                <span class="fandom-badge badge-<?= $cat ?>"><?= htmlspecialchars($post['fandom']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="post-time"><?= htmlspecialchars($post['time_ago']) ?></div>
                        </div>
                    </div>
                    <div class="post-body"><?= htmlspecialchars($post['content']) ?></div>
                    <div class="post-actions">
                        <button class="act-btn" data-base="<?= (int)$post['likes'] ?>" onclick="toggleLike(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                            <span><?= number_format($post['likes']) ?></span>
                        </button>
                        <button class="act-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            <span><?= number_format($post['comments']) ?></span>
                        </button>
                        <button class="act-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
                            <span><?= number_format($post['reposts']) ?></span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="tab-media" style="display:none">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                No media posts yet.
            </div>
        </div>
        <div id="tab-likes" style="display:none">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                No liked posts yet.
            </div>
        </div>

        <?php endif; ?>
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
function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['posts','media','likes'].forEach(id => {
        document.getElementById('tab-' + id).style.display = id === tabId ? '' : 'none';
    });
}
function toggleLike(btn) {
    const span = btn.querySelector('span');
    const base = parseInt(btn.dataset.base, 10);
    const liked = btn.dataset.liked === '1';
    span.textContent = (liked ? base : base + 1).toLocaleString();
    btn.style.color = liked ? '' : '#ec4899';
    btn.dataset.liked = liked ? '0' : '1';
    // TODO: POST to your PostController → /post/like/{id}
}
function toggleFollow(btn) {
    const following = btn.classList.toggle('following');
    btn.textContent = following ? 'Following' : 'Follow';
    // TODO: POST to your UserController → /user/follow/{id}
}
</script>
</body>
</html>
