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

require_auth();

// Resolve projects.json from prod (/private) or local (/data)
$dataProd = __DIR__ . '/../private/data/projects.json';
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
    <h1>Vibe Projects</h1>
    <nav><a href="/logout.php">Logout</a></nav>
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