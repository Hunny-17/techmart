<?php
declare(strict_types=1);

namespace App\Core;

/**
 * App - Bootstrap toàn bộ ứng dụng
 *
 * Trách nhiệm:
 *  - Khởi tạo autoloader
 *  - Load config
 *  - Khởi tạo session
 *  - Khởi tạo router và load routes
 *  - Bắt exception toàn cục
 */
final class App
{
    public static array $config = [];
    public static Router $router;
    public static string $nonce = '';

    public static function run(): void
    {
        header_remove('X-Powered-By');
        self::$nonce = base64_encode(random_bytes(16));
        self::registerAutoloader();
        self::loadConfig();
        self::setErrorHandling();
        self::sendSecurityHeaders();
        Session::start();
        self::dispatch();
    }

    private static function sendSecurityHeaders(): void
    {
        $n = self::$nonce;
        header("Content-Security-Policy: " . implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$n}' cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com",
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net",
            "img-src 'self' data: https://placehold.co https://images.unsplash.com https://img.vietqr.io",
            "frame-src https://www.google.com",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));
    }

    private static function registerAutoloader(): void
    {
        // PSR-4 mini: namespace App\... → file app/...
        spl_autoload_register(static function (string $class): void {
            $prefix  = 'App\\';
            $baseDir = dirname(__DIR__) . '/'; // .../techmart-web/app/

            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (is_file($file)) {
                require $file;
            }
        });

        // Helpers global
        require_once __DIR__ . '/helpers.php';
    }

    private static function loadConfig(): void
    {
        self::$config = require dirname(__DIR__, 2) . '/config/config.php';
    }

    private static function setErrorHandling(): void
    {
        $debug = self::$config['app']['debug'] ?? false;

        if ($debug) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', self::$config['paths']['storage'] . '/logs/php-error.log');
        }

        set_exception_handler(static function (\Throwable $e): void {
            $debug = App::$config['app']['debug'] ?? false;
            error_log($e->getMessage() . "\n" . $e->getTraceAsString());

            http_response_code(500);
            if ($debug) {
                echo '<pre style="padding:20px;background:#fee;color:#900;font-family:monospace">';
                echo 'EXCEPTION: ' . htmlspecialchars($e->getMessage()) . "\n\n";
                echo 'IN: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
                echo htmlspecialchars($e->getTraceAsString());
                echo '</pre>';
            } else {
                View::render('errors/500', [], null);
            }
        });
    }

    private static function dispatch(): void
    {
        self::$router = new Router();
        $router = self::$router;
        require dirname(__DIR__, 2) . '/config/routes.php';
        self::$router->dispatch();
    }
}
