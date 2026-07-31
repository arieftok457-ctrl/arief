<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/UrlShortener.php';

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/', '/') ?: '/';

if ($path !== '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $code = ltrim($path, '/');
    if (preg_match('/^[A-Za-z0-9]{1,12}$/', $code) === 1) {
        $original = UrlShortener::resolve($code);
        if ($original !== null) {
            header('Location: ' . $original, true, 302);
            exit;
        }
    }

    http_response_code(404);
    exit('URL pendek tidak ditemukan.');
}

$flash = null;
$shortUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim((string) ($_POST['url'] ?? ''));

    if ($input === '' || !UrlShortener::isValidUrl($input)) {
        $flash = 'URL tidak valid. Gunakan format http:// atau https://';
    } else {
        $code = UrlShortener::create($input);
        $baseUrl = rtrim(
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                ? $_SERVER['HTTP_X_FORWARDED_PROTO']
                : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            '/'
        );
        $shortUrl = $baseUrl . '/' . $code;
    }
}

$recent = UrlShortener::recent();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShortNURL - Pangkas URL Anda</title>
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; --border: #e2e8f0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: var(--bg); color: #0f172a; }
        main { max-width: 640px; margin: 4rem auto; padding: 0 1rem; }
        h1 { font-size: 1.75rem; margin-bottom: .25rem; }
        p.sub { color: #64748b; margin-top: 0; }
        form { display: flex; gap: .5rem; margin-top: 1.5rem; }
        input[type=url] { flex: 1; padding: .7rem .9rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; }
        button { padding: .7rem 1.4rem; border: 0; border-radius: 8px; background: var(--primary); color: #fff; font-size: 1rem; cursor: pointer; }
        .box { margin-top: 1rem; padding: 1rem; border-radius: 8px; }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .success a { color: var(--primary); font-weight: 600; }
        table { width: 100%; margin-top: 2rem; border-collapse: collapse; background: #fff; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        th, td { text-align: left; padding: .6rem .9rem; border-bottom: 1px solid var(--border); font-size: .875rem; }
        th { background: #f1f5f9; }
        td.url-cell { max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        a { text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <h1>ShortNURL</h1>
        <p class="sub">Pangkas URL panjang menjadi tautan pendek yang mudah dibagikan.</p>

        <form method="post" action="/">
            <input type="url" name="url" placeholder="https://contoh.com/artikel/sangat-panjang/..." required>
            <button type="submit">Pangkas</button>
        </form>

        <?php if ($flash !== null): ?>
            <div class="box error"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($shortUrl !== null): ?>
            <div class="box success">
                URL pendek Anda: <a href="<?= htmlspecialchars($shortUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($shortUrl, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        <?php endif; ?>

        <?php if ($recent !== []): ?>
            <table>
                <thead>
                    <tr>
                        <th>URL Pendek</th>
                        <th>URL Asli</th>
                        <th>Klik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td><a href="/<?= htmlspecialchars($row['short_code'], ENT_QUOTES, 'UTF-8') ?>">/<?= htmlspecialchars($row['short_code'], ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td class="url-cell" title="<?= htmlspecialchars($row['original_url'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['original_url'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $row['clicks'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
