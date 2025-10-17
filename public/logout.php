<?php
// Robust config loader: prod (DOCUMENT_ROOT) → prod (relative) → local dev
$docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');

$candidates = array_filter([
  $docroot ? $docroot . '/private/config/config.php' : null, // /httpdocs/private/...
  __DIR__ . '/../private/config/config.php',                 // relative prod fallback
  __DIR__ . '/../config/config.php',                        // local dev
]);

$loaded = false;
foreach ($candidates as $p) {
  if (is_readable($p)) { require $p; $loaded = true; break; }
}

if (!$loaded) {
  error_log('Config not found. Tried: ' . implode(' | ', $candidates));
  http_response_code(500);
  echo 'Config not found.';
  exit;
}

// Clear session safely
$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE && ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
if (session_status() === PHP_SESSION_ACTIVE) {
  session_destroy();
}

header('Location: /login.php');
exit;