<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session - wrapper an toàn cho $_SESSION
 *
 * Tự động set HttpOnly và Secure khi HTTPS
 * Dùng cho cart, auth, flash messages
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        session_set_cookie_params([
            'lifetime' => App::$config['session']['lifetime'] ?? 1800,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('TECHMART_SID');
        session_start();

        self::checkTimeout();
    }

    private static function checkTimeout(): void
    {
        $lifetime = App::$config['session']['lifetime'] ?? 1800;
        if (isset($_SESSION['_last_activity'])
            && (time() - $_SESSION['_last_activity']) > $lifetime
        ) {
            self::destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Regenerate session ID — gọi sau khi login thành công
     * Chống session fixation
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
