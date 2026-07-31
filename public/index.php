<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Database.php';

$dbStatus = '?';

try {
    Database::pdo()->query('SELECT 1');
    $dbStatus = 'OK';
} catch (PDOException $e) {
    $dbStatus = 'GAGAL: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShortNURL</title>
</head>
<body>
    <h1>ShortNURL</h1>
    <p>Layanan shorten URL - environment siap.</p>
    <p>Koneksi database: <strong><?= $dbStatus ?></strong></p>
</body>
</html>
