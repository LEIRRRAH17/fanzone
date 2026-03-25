<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FanZone – Login</title>
  <link rel="icon" type="image/jpeg" href="assets/favicon.jpg" />
  <link rel="stylesheet" href="assets/css/auth.css" />
  <script src="assets/js/theme.js"></script>
</head>
<body>

  <!-- Theme toggle -->
  <button class="ttoggle" id="ttoggle" onclick="toggleTheme()">☀️</button>

  <div class="auth-card">

    <!-- ══ LEFT PANEL ══ -->
    <div class="auth-left">
      <div class="left-inner">
        <div class="brand-name">FanZone</div>
        <p class="brand-desc">
          Join the ultimate fandom universe. Connect with fellow fans,
          discover new content, and dive deep into your favorite worlds.
        </p>
        <ul class="feature-list">
          <li>Exclusive fan content &amp; discussions</li>
          <li>Real-time event updates</li>
          <li>Community-driven recommendations</li>
          <li>Personalized fan experiences</li>
        </ul>
      </div>
    </div>

    <!-- ══ RIGHT PANEL ══ -->
    <div class="auth-right">

      <!-- LOGIN PANEL -->
      <div id="login-panel" style="display:flex;flex-direction:column;">

        <div class="form-title">Welcome Back!</div>
        <div class="form-sub">Log back in and catch up on everything happening in the fandom universe.</div>

        <?php if (!empty($error)): ?>
          <div class="alert err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login">
          <div class="igroup">
            <input type="text" name="username" placeholder="Username"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required />
          </div>
          <div class="igroup">
            <input type="password" name="password" placeholder="Password" required />
          </div>
          <button type="submit" class="btn-auth">Login</button>
        </form>

        <p class="auth-hint">
          New to the fandom? <a href="#" onclick="openRegister()">Join the community</a>
        </p>



      </div><!-- /login-panel -->

      <!-- REGISTER PANEL -->
      <div id="register-panel" style="display:none;flex-direction:column;">

        <div class="form-title">Join the Fandom!</div>
        <div class="form-sub">Create your account and start exploring the universe.</div>

        <?php if (!empty($error)): ?>
          <div class="alert err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert ok">
            <?= htmlspecialchars($success) ?> —
            <a href="index.php?action=login" style="color:#6ee7b7;font-weight:600;">Login →</a>
          </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register">
          <div class="igroup">
            <input type="text" name="full_name" placeholder="Full Name"
              value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required />
          </div>
          <div class="igroup">
            <input type="text" name="username" placeholder="Username"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required minlength="3" />
          </div>
          <div class="igroup">
            <input type="email" name="email" placeholder="Email Address"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
          </div>
          <div class="igroup">
            <input type="password" name="password" placeholder="Password"
              required minlength="6" oninput="checkStrength(this.value)" />
            <div class="str-bar"><div class="str-fill" id="str-fill"></div></div>
            <span class="str-label" id="str-label"></span>
          </div>
          <div class="igroup">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
          </div>

          <span class="fandom-label">Pick your fandoms (at least one)</span>
          <div class="fandom-picker">
            <?php foreach (['Anime','Games','Movies','Manga','K-Drama'] as $f): ?>
            <label class="fandom-tag">
              <input type="checkbox" name="fandoms[]" value="<?= $f ?>"
                <?= in_array($f, $_POST['fandoms'] ?? []) ? 'checked' : '' ?> />
              <span><?= $f ?></span>
            </label>
            <?php endforeach; ?>
          </div>

          <button type="submit" class="btn-auth">Create Account</button>
        </form>

        <p class="auth-hint">
          Already a fan? <a href="#" onclick="openLogin()">Log in here</a>
        </p>


      </div><!-- /register-panel -->

    </div><!-- /auth-right -->
  </div><!-- /auth-card -->

  <script src="assets/js/auth.js"></script>

  <?php if (isset($_GET['action']) && $_GET['action'] === 'register'): ?>
  <script>window.addEventListener('DOMContentLoaded', openRegister);</script>
  <?php endif; ?>

</body>
</html>