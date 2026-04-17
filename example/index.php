<?php
$movies = json_decode(file_get_contents(__DIR__ . '/movies.json'), true)['movies'];

$genre = $_GET['genre'] ?? '';
$q     = trim($_GET['q'] ?? '');
$sort  = in_array($_GET['sort'] ?? '', ['year','title','imdb_score','runtime']) ? $_GET['sort'] : 'imdb_score';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

$filtered = $movies;
if ($genre) $filtered = array_values(array_filter($filtered, fn($m) => $m['genre'] === $genre));
if ($q)     $filtered = array_values(array_filter($filtered, fn($m) => stripos($m['title'], $q) !== false || stripos($m['synopsis'], $q) !== false));

$dir = $order === 'desc' ? -1 : 1;
usort($filtered, fn($a, $b) => $dir * ($a[$sort] <=> $b[$sort]));

$genre_colors = [
    'sci-fi'    => ['#5aabcf','#0d1e28','#1a3040'],
    'action'    => ['#d4722a','#28140a','#401f0f'],
    'crime'     => ['#c44040','#200808','#381010'],
    'comedy'    => ['#9abf3a','#1c2808','#2e3e10'],
    'horror'    => ['#c03040','#1c0408','#300610'],
    'animation' => ['#3abfb8','#081e1c','#103030'],
    'drama'     => ['#9a5abf','#160828','#241040'],
];

function genre_badge(string $g, array $gc): string {
    [$t,$bg,$bd] = $gc[$g] ?? ['#aaa','#222','#333'];
    return "<span class=\"badge\" style=\"color:{$t};background:{$bg};border-color:{$bd}\">{$g}</span>";
}

function score_color(float $s): string {
    if ($s >= 9.0) return '#e8c84a';
    if ($s >= 8.0) return '#6abf6a';
    if ($s >= 7.0) return '#5aabcf';
    return '#888';
}

function sort_link(string $col, string $label, string $cur_sort, string $cur_order, string $genre, string $q): string {
    $next = ($cur_sort === $col && $cur_order === 'asc') ? 'desc' : 'asc';
    $p = http_build_query(array_filter(['genre' => $genre, 'q' => $q, 'sort' => $col, 'order' => $next]));
    $arrow = $cur_sort === $col ? ($cur_order === 'asc' ? ' ↑' : ' ↓') : '';
    $color = $cur_sort === $col ? 'color:#e8c84a' : '';
    return "<a href=\"?{$p}\" style=\"{$color}\">{$label}{$arrow}</a>";
}

$all_genres = ['sci-fi','action','crime','comedy','horror','animation','drama'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Movie Database</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #0d0d0d; color: #f0f0f0; min-height: 100vh; padding: 2rem; }

    h1 { font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #e8c84a; margin-bottom: .3rem; }
    .subtitle { font-size: .78rem; color: #555; margin-bottom: 1.8rem; }
    .subtitle a { color: #444; text-decoration: none; }
    .subtitle a:hover { color: #666; }

    .filters { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; margin-bottom: 1.2rem; }
    .genre-btn { font-size: .68rem; padding: .28rem .65rem; border-radius: 3px; border: 1px solid #2a2a2a; background: #181818; color: #666; cursor: pointer; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .genre-btn:hover { border-color: #444; color: #999; }
    .search-wrap { display: flex; gap: .4rem; align-items: center; margin-left: .4rem; }
    .search-box { font-size: .8rem; padding: .3rem .6rem; border-radius: 3px; border: 1px solid #2a2a2a; background: #181818; color: #f0f0f0; outline: none; width: 180px; }
    .search-box:focus { border-color: #444; }
    .search-btn { font-size: .72rem; padding: .3rem .65rem; border-radius: 3px; border: 1px solid #333; background: #222; color: #aaa; cursor: pointer; }
    .clear-link { font-size: .75rem; color: #444; text-decoration: none; margin-left: .3rem; }
    .clear-link:hover { color: #777; }

    .count { font-size: .75rem; color: #444; margin-bottom: .8rem; }
    .count strong { color: #888; font-weight: 500; }

    table { width: 100%; border-collapse: collapse; }
    thead th { text-align: left; font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #444; padding: .5rem .8rem; border-bottom: 2px solid #202020; white-space: nowrap; }
    thead th a { color: inherit; text-decoration: none; }
    thead th a:hover { color: #777; }
    tbody tr { border-bottom: 1px solid #181818; }
    tbody tr:hover { background: #141414; }
    tbody td { padding: .5rem .8rem; vertical-align: middle; font-size: .82rem; }

    .td-title a { font-weight: 600; color: #f0f0f0; text-decoration: none; }
    .td-title a:hover { color: #e8c84a; }
    .td-year { color: #555; font-size: .78rem; white-space: nowrap; text-align: right; }
    .td-director { color: #bbb; }
    .td-score { font-weight: 700; white-space: nowrap; }
    .td-runtime { color: #555; font-size: .75rem; white-space: nowrap; text-align: right; }
    .td-tags { line-height: 1.9; }

    .badge { display: inline-block; font-size: .65rem; padding: .13rem .45rem; border-radius: 3px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; border: 1px solid; }
    .tag { display: inline-block; font-size: .63rem; padding: .1rem .38rem; border-radius: 3px; color: #555; background: #181818; border: 1px solid #242424; margin: .1rem .1rem .1rem 0; }
  </style>
</head>
<body>

<h1>Movie Database</h1>
<p class="subtitle">80 films · <a href="context.txt">context.txt</a> · <a href="api/context.txt">api/context.txt</a></p>

<form method="get" class="filters">
  <?php foreach ($all_genres as $g):
    $active = $genre === $g;
    [$t,$bg,$bd] = $genre_colors[$g];
    $style = $active ? "color:{$t};background:{$bg};border-color:{$bd}" : '';
  ?>
    <button type="submit" name="genre" value="<?= $active ? '' : htmlspecialchars($g) ?>" class="genre-btn" style="<?= $style ?>"><?= $g ?></button>
  <?php endforeach; ?>
  <input type="hidden" name="sort"  value="<?= htmlspecialchars($sort) ?>">
  <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
  <span class="search-wrap">
    <input type="text" name="q" class="search-box" placeholder="Search title…" value="<?= htmlspecialchars($q) ?>">
    <button type="submit" class="search-btn">Go</button>
    <?php if ($genre || $q): ?><a href="?" class="clear-link">✕ clear</a><?php endif; ?>
  </span>
</form>

<p class="count">
  <?= count($filtered) ?> film<?= count($filtered) !== 1 ? 's' : '' ?>
  <?php if ($genre): ?> &middot; genre: <strong><?= htmlspecialchars($genre) ?></strong><?php endif; ?>
  <?php if ($q):     ?> &middot; &ldquo;<strong><?= htmlspecialchars($q) ?></strong>&rdquo;<?php endif; ?>
</p>

<table>
  <thead>
    <tr>
      <th><?= sort_link('title',      'Title',    $sort, $order, $genre, $q) ?></th>
      <th><?= sort_link('year',       'Year',     $sort, $order, $genre, $q) ?></th>
      <th>Director</th>
      <th>Genre</th>
      <th><?= sort_link('imdb_score', 'Score',    $sort, $order, $genre, $q) ?></th>
      <th><?= sort_link('runtime',    'Runtime',  $sort, $order, $genre, $q) ?></th>
      <th>Tags</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($filtered as $m): ?>
    <tr>
      <td class="td-title"><a href="movie/<?= $m['slug'] ?>"><?= htmlspecialchars($m['title']) ?></a></td>
      <td class="td-year"><?= $m['year'] ?></td>
      <td class="td-director"><?= htmlspecialchars($m['director']) ?></td>
      <td><?= genre_badge($m['genre'], $genre_colors) ?></td>
      <td class="td-score" style="color:<?= score_color($m['imdb_score']) ?>"><?= number_format($m['imdb_score'], 1) ?></td>
      <td class="td-runtime"><?= $m['runtime'] ?> min</td>
      <td class="td-tags"><?php foreach ($m['tags'] as $tag) echo "<span class=\"tag\">{$tag}</span> "; ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
