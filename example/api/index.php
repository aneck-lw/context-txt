<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

if (function_exists('apcu_fetch')) {
    $key = 'rate:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $count = apcu_fetch($key);
    if ($count === false) {
        apcu_store($key, 1, 60);
    } elseif ($count >= 60) {
        http_response_code(429);
        echo json_encode(['error' => 'rate_limited', 'retry_after' => 60]);
        exit;
    } else {
        apcu_inc($key);
    }
}

$movies = json_decode(file_get_contents(__DIR__ . '/../movies.json'), true)['movies'];

$path = trim($_SERVER['PATH_INFO'] ?? '', '/');

if ($path === '') {
    echo json_encode([
        'endpoints' => [
            'GET /api/movies'          => 'list movies — supports genre, decade, director, min_score, tag, language, q, sort, order',
            'GET /api/movies/{slug}'   => 'single movie by slug',
        ],
    ]);
    exit;
}

if ($path === 'movies') {
    $result = $movies;

    if (isset($_GET['genre']) && $_GET['genre'] !== '') {
        $v = strtolower(trim($_GET['genre']));
        $result = array_values(array_filter($result, fn($m) => $m['genre'] === $v));
    }

    if (isset($_GET['decade']) && $_GET['decade'] !== '') {
        $start = (int) rtrim(trim($_GET['decade']), 's');
        $result = array_values(array_filter($result, fn($m) => $m['year'] >= $start && $m['year'] <= $start + 9));
    }

    if (isset($_GET['director']) && $_GET['director'] !== '') {
        $v = trim($_GET['director']);
        $result = array_values(array_filter($result, fn($m) => stripos($m['director'], $v) !== false));
    }

    if (isset($_GET['min_score']) && $_GET['min_score'] !== '') {
        $v = (float) $_GET['min_score'];
        $result = array_values(array_filter($result, fn($m) => $m['imdb_score'] >= $v));
    }

    if (isset($_GET['tag']) && $_GET['tag'] !== '') {
        $v = strtolower(trim($_GET['tag']));
        $result = array_values(array_filter($result, fn($m) => in_array($v, $m['tags'])));
    }

    if (isset($_GET['language']) && $_GET['language'] !== '') {
        $v = strtolower(trim($_GET['language']));
        $result = array_values(array_filter($result, fn($m) => strtolower($m['language']) === $v));
    }

    if (isset($_GET['q']) && $_GET['q'] !== '') {
        $v = trim($_GET['q']);
        $result = array_values(array_filter($result, fn($m) =>
            stripos($m['title'], $v) !== false || stripos($m['synopsis'], $v) !== false
        ));
    }

    $valid_sorts = ['year', 'title', 'imdb_score', 'runtime'];
    $sort  = in_array($_GET['sort'] ?? '', $valid_sorts) ? $_GET['sort'] : 'year';
    $order = (strtolower($_GET['order'] ?? 'asc') === 'desc') ? -1 : 1;
    usort($result, fn($a, $b) => $order * ($a[$sort] <=> $b[$sort]));

    $filters = array_filter([
        'genre'     => $_GET['genre']     ?? null,
        'decade'    => $_GET['decade']    ?? null,
        'director'  => $_GET['director']  ?? null,
        'min_score' => isset($_GET['min_score']) ? (float) $_GET['min_score'] : null,
        'tag'       => $_GET['tag']       ?? null,
        'language'  => $_GET['language']  ?? null,
        'q'         => $_GET['q']         ?? null,
    ]);

    echo json_encode([
        'count'   => count($result),
        'filters' => $filters ?: (object) [],
        'movies'  => $result,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (preg_match('/^movies\/([a-z0-9-]+)$/', $path, $m)) {
    foreach ($movies as $movie) {
        if ($movie['slug'] === $m[1]) {
            echo json_encode($movie, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    http_response_code(404);
    echo json_encode(['error' => 'not_found', 'slug' => $m[1]]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);
