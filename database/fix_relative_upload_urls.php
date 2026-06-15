<?php
declare(strict_types=1);

$pdo = new PDO(
    'mysql:host=localhost;port=3306;dbname=techmart;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$base = 'http://localhost/techmart-web/assets/uploads/';

$pdo->exec("
    UPDATE products
    SET image_url = CONCAT('$base', SUBSTRING(image_url, LENGTH('assets/uploads/') + 1))
    WHERE image_url LIKE 'assets/uploads/%'
");

echo "relative upload urls fixed\n";
