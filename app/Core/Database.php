<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database - PDO connection singleton
 *
 * Mọi query trong project ĐỀU dùng prepared statement qua $pdo->prepare()
 * Tuyệt đối KHÔNG concat string SQL với input
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function pdo(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $cfg = App::$config['db'];
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );

        try {
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement thật
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$cfg['charset']} COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            // Trong production không in ra credentials
            $debug = App::$config['app']['debug'] ?? false;
            $msg = $debug ? $e->getMessage() : 'Database connection error';
            throw new \RuntimeException($msg, (int)$e->getCode());
        }

        return self::$instance;
    }
}
