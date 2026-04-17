<?php
$movies = json_decode(file_get_contents(__DIR__ . '/movies.json'), true)['movies'];
$slug   = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'] ?? ''));

$movie = null;
foreach ($movies as $m) {
    if ($m['slug'] === $slug) { $movie = $m; break; }
}

if (!$movie) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not Found</title></head>';
    echo '<body style="background:#0d0d0d;color:#f0f0f0;font-family:system-ui;padding:2rem">';
    echo '<p style="color:#555">Film not found. <a href="." style="color:#e8c84a">Back to list</a></p></body></html>';
    exit;
}

$genre_colors = [
    'sci-fi'    => ['#5aabcf','#0d1e28','#1a3040'],
    'action'    => ['#d4722a','#28140a','#401f0f'],
    'crime'     => ['#c44040','#200808','#381010'],
    'comedy'    => ['#9abf3a','#1c2808','#2e3e10'],
    'horror'    => ['#c03040','#1c0408','#300610'],
    'animation' => ['#3abfb8','#081e1c','#103030'],
    'drama'     => ['#9a5abf','#160828','#241040'],
];

[$t,$bg,$bd] = $genre_colors[$movie['genre']] ?? ['#aaa','#222','#333'];

function score_color(float $s): string {
    if ($s >= 9.0) return '#e8c84a';
    if ($s >= 8.0) return '#6abf6a';
    if ($s >= 7.0) return '#5aabcf';
    return '#888';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($movie['title']) ?> — Movie Database</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #0d0d0d; color: #f0f0f0; min-height: 100vh; padding: 2rem; }

    .back { font-size: .75rem; color: #999; text-decoration: none; display: inline-block; margin-bottom: 2rem; }
    .back:hover { color: #ccc; }

    .card { background: #181818; border: 1px solid #282828; border-radius: 4px; padding: 2rem; max-width: 680px; }

    .genre-badge { display: inline-block; font-size: .68rem; padding: .15rem .5rem; border-radius: 3px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; border: 1px solid; margin-bottom: 1rem; }

    h1 { font-size: 1.5rem; font-weight: 700; color: #f0f0f0; margin-bottom: .3rem; line-height: 1.2; }
    .year-dir { font-size: .85rem; color: #999; margin-bottom: 1.5rem; }

    .meta-grid { display: grid; grid-template-columns: max-content 1fr; gap: .4rem 1.2rem; margin-bottom: 1.5rem; font-size: .82rem; }
    .meta-label { color: #888; font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; padding-top: .1rem; }
    .meta-value { color: #ddd; }

    .synopsis { font-size: .9rem; color: #ccc; line-height: 1.6; margin-bottom: 1.5rem; }

    .tags-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: .5rem; }
    .tag { display: inline-block; font-size: .68rem; padding: .15rem .45rem; border-radius: 3px; color: #999; background: #1e1e1e; border: 1px solid #333; margin: .15rem .15rem .15rem 0; }

    .api-link { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #282828; font-size: .75rem; color: #888; }
    .api-link a { color: #999; text-decoration: none; font-family: monospace; }
    .api-link a:hover { color: #ccc; }
  </style>
</head>
<body>

<a href=".." class="back">← All films</a>

<div class="card">
  <span class="genre-badge" style="color:<?= $t ?>;background:<?= $bg ?>;border-color:<?= $bd ?>"><?= htmlspecialchars($movie['genre']) ?></span>

  <h1><?= htmlspecialchars($movie['title']) ?></h1>
  <p class="year-dir"><?= $movie['year'] ?> &middot; <?= htmlspecialchars($movie['director']) ?></p>

  <div class="meta-grid">
    <span class="meta-label">Score</span>
    <span class="meta-value" style="color:<?= score_color($movie['imdb_score']) ?>;font-weight:700"><?= number_format($movie['imdb_score'], 1) ?> <span style="color:#444;font-weight:400;font-size:.75rem">IMDb</span></span>

    <span class="meta-label">Runtime</span>
    <span class="meta-value"><?= $movie['runtime'] ?> min</span>

    <span class="meta-label">Language</span>
    <span class="meta-value"><?= htmlspecialchars($movie['language']) ?></span>

    <span class="meta-label">Country</span>
    <span class="meta-value"><?= htmlspecialchars($movie['country']) ?></span>
  </div>

  <p class="synopsis"><?= htmlspecialchars($movie['synopsis']) ?></p>

  <p class="tags-label">Tags</p>
  <div><?php foreach ($movie['tags'] as $tag) echo "<span class=\"tag\">{$tag}</span>"; ?></div>

  <div class="api-link">
    API: <a href="api/movies/<?= $movie['slug'] ?>">api/movies/<?= $movie['slug'] ?></a>
  </div>
</div>

</body>
</html>
