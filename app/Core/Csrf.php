<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Csrf - CSRF token protection
 *
 * Cách dùng:
 *  Trong view (form):
 *      <input type="hidden" name="_token" value="<?= Csrf::token() ?>">
 *
 *  Trong controller (đầu method POST):
 *      Csrf::verify();
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    /**
     * Lấy token hiện tại, tạo mới nếu chưa có
     */
    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::KEY);
    }

    /**
     * Verify token từ POST. Throw exception nếu sai.
     */
    public static function verify(): void
    {
        $sent = $_POST['_token'] ?? '';
        $real = Session::get(self::KEY, '');

        if (!is_string($sent) || $sent === '' || !hash_equals((string)$real, $sent)) {
            http_response_code(419);
            throw new \RuntimeException('CSRF token không hợp lệ. Vui lòng tải lại trang.');
        }
    }
}
