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