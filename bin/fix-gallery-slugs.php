<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=cmd-new;charset=utf8mb4',
    'root',
    '32101',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$rows = $pdo->query("SELECT id, title FROM galleries WHERE slug IS NULL OR slug = ''");
if ($rows === false) {
    exit(0);
}

foreach ($rows as $row) {
    $base = Nette\Utils\Strings::webalize((string) $row['title']);
    $slug = $base !== '' ? $base : 'galerie';
    $candidate = $slug;
    $suffix = 2;
    $exists = $pdo->prepare('SELECT COUNT(*) FROM galleries WHERE slug = ? AND id <> ?');

    do {
        $exists->execute([$candidate, $row['id']]);
        if ((int) $exists->fetchColumn() === 0) {
            break;
        }

        $candidate = $slug . '-' . $suffix;
        $suffix++;
    } while (true);

    $update = $pdo->prepare('UPDATE galleries SET slug = ? WHERE id = ?');
    $update->execute([$candidate, $row['id']]);
}

echo "slug fix ok\n";
