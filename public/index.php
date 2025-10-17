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