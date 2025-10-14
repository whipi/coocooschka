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

// Choose target based on auth
$target = (function_exists('is_logged_in') && is_logged_in())
  ? '/overview.php'
  : '/login.php';

// Avoid caching the redirect
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Temporary redirect (302)
header('Location: ' . $target, true, 302);
exit;