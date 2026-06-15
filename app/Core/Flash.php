<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Flash - thông báo 1 lần (xem 1 lần là mất)
 *
 * Dùng cho thông báo thành công/lỗi sau redirect
 *
 *  Flash::set('success', 'Thêm sản phẩm thành công.');
 *  redirect('/admin/products');
 *
 * Trong view: <?php include '../partials/flash.php'; ?>
 */
final class Flash
{
    private const KEY = '_flash';

    public static function set(string $type, string $message): void
    {
        $flash = Session::get(self::KEY, []);
        $flash[$type] = $message;
        Session::set(self::KEY, $flash);
    }

    /**
     * Lấy ra và xoá luôn
     * @return array<string,string>
     */
    public static function pull(): array
    {
        $flash = Session::get(self::KEY, []);
        Session::forget(self::KEY);
        return $flash;
    }
}
