<?php
// ── Load config (prod → local) ──────────────────────────────
$docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$candidates = array_filter([
  $docroot ? $docroot . '/private/config/config.php' : null, // /httpdocs/private/...
  __DIR__ . '/../private/config/config.php',                  // relative prod fallback
  __DIR__ . '/../config/config.php',                          // local dev
]);
$loaded = false;
foreach ($candidates as $p) {
  if (is_readable($p)) { require $p; $loaded = true; break; }
}
if (!$loaded) { http_response_code(500); echo 'Config not found.'; exit; }

// ── Never cache the login page (avoids stale CSRF) ──────────
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// ── Allow only same-site return URLs (https + *.coocooschka.de) ─
function allow_next(string $url): bool {
  if ($url === '' || strpos($url, 'https://') !== 0) return false;
  $host = parse_url($url, PHP_URL_HOST) ?? '';
  return is_string($host) && ($host === 'coocooschka.de' || str_ends_with($host, '.coocooschka.de'));
}

$next = $_GET['next'] ?? '';

// Already logged in? jump to next/overview.
if (function_exists('is_logged_in') && is_logged_in()) {
  header('Location: ' . (allow_next($next) ? $next : '/overview.php'));
  exit;
}

// ── Handle submit ───────────────────────────────────────────
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u   = trim($_POST['username'] ?? '');
  $p   = $_POST['password'] ?? '';
  $tok = $_POST['csrf'] ?? '';
  $next = $_POST['next'] ?? '';

  if (!function_exists('verify_csrf') || !verify_csrf($tok)) {
    $error = 'Formular ungültig (CSRF). Bitte neu laden.';
  } elseif (!defined('VC_USERNAME') || $u !== VC_USERNAME) {
    $error = 'Falscher Benutzername.';
  } elseif (!defined('VC_PASSWORD_HASH') || !password_verify($p, VC_PASSWORD_HASH)) {
    $error = 'Falsches Passwort.';
  } else {
    if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);
    $_SESSION['user'] = VC_USERNAME;
    header('Location: ' . (allow_next($next) ? $next : '/overview.php'));
    exit;
  }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login – Vibe Projects</title>
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <link rel="stylesheet" href="/styles.css">
</head>
<body class="center">
  <form class="card" method="post" action="/login.php" autocomplete="on">
    <h1>LogiN</h1>

    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label>Username:
      <input name="username" id="username" autocomplete="username" required>
    </label>

    <label>Password:
      <input name="password" id="password" type="password" autocomplete="current-password" required>
    </label>

    <!-- keep return target from ?next=... -->
    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

    <button type="submit">Login</button>
  </form>
</body>
</html>