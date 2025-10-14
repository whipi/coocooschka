<?php
// Load config (prod first, then local) robustly
$docroot    = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/');
$prodConfig = $docroot . '/private/config/config.php';
$devConfig  = __DIR__ . '/../config/config.php';

if (is_readable($prodConfig)) {
  require $prodConfig;
} elseif (@is_readable($devConfig)) { // suppress open_basedir warning for local fallback
  require $devConfig;
} else {
  error_log('Config not found. Tried: ' . $prodConfig . ' and ' . $devConfig);
  http_response_code(500);
  echo 'Config not found.';
  exit;
}

// Already logged in? -> overview
if (function_exists('is_logged_in') && is_logged_in()) {
  header('Location: /overview.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u   = trim($_POST['username'] ?? '');
  $p   = $_POST['password'] ?? '';
  $tok = $_POST['csrf'] ?? '';

  if (!function_exists('verify_csrf') || !verify_csrf($tok)) {
    $error = 'Formular ungültig (CSRF). Bitte neu laden.';
  } elseif (!defined('VC_USERNAME') || $u !== VC_USERNAME) {
    $error = 'Falscher Benutzername.';
  } elseif (!defined('VC_PASSWORD_HASH') || !password_verify($p, VC_PASSWORD_HASH)) {
    $error = 'Falsches Passwort.';
  } else {
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_regenerate_id(true);
    }
    $_SESSION['user'] = VC_USERNAME;
    header('Location: /overview.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <title>Login – Vibe Projects</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body class="center">
  <form class="card" method="post" action="/login.php" autocomplete="on">
    <h1>Login</h1>
    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <label>Username:
      <input name="username" id="username" autocomplete="username" required>
    </label>
    <label>Password:
      <input name="password" id="password" type="password" autocomplete="current-password" required>
    </label>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <button type="submit">Login</button>
  </form>
</body>
</html>