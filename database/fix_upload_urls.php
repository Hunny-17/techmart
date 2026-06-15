<?php
declare(strict_types=1);

$pdo = new PDO(
    'mysql:host=localhost;port=3306;dbname=techmart;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->exec("
    UPDATE products
    SET image_url = REPLACE(image_url, '/storage/uploads/', 'assets/uploads/')
    WHERE image_url LIKE '/storage/uploads/%'
");

echo "image urls fixed\n";
