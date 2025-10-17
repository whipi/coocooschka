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

require_auth();

// Resolve projects.json from prod (/private) or local (/data)
$dataProd = $docroot ? $docroot . '/private/data/projects.json' : __DIR__ . '/../private/data/projects.json';
$dataDev  = __DIR__ . '/../data/projects.json';

$projectsFile = is_file($dataProd) ? $dataProd : (@is_file($dataDev) ? $dataDev : null);

// Load projects safely
$projects = [];
if ($projectsFile && is_readable($projectsFile)) {
  $raw     = @file_get_contents($projectsFile);
  $decoded = json_decode($raw ?: '[]', true);
  if (is_array($decoded)) $projects = $decoded;
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <title>Übersicht – Vibe Projects</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
  <header class="topbar">
    <h1>My PProjects</h1>
    <nav><a href="/logout.php" class="btn-secondary">Logout</a></nav>
  </header>

  <main class="grid">
    <?php foreach ($projects as $p): ?>
      <a class="tile" href="<?= htmlspecialchars($p['url'] ?? '#') ?>" target="_blank" rel="noopener">
        <h2><?= htmlspecialchars($p['name'] ?? 'Unbenannt') ?></h2>
        <p><?= htmlspecialchars($p['desc'] ?? '') ?></p>
        <small><?= htmlspecialchars($p['type'] ?? '—') ?> • <?= htmlspecialchars($p['env'] ?? '—') ?></small>
      </a>
    <?php endforeach; ?>

    <?php if (empty($projects)): ?>
      <div class="empty">Noch keine Projekte. Wir fügen sie im nächsten Schritt hinzu.</div>
    <?php endif; ?>
  </main>
</body>
</html>