<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Auth - quản lý đăng nhập, phân quyền
 *
 * Lưu user info trong $_SESSION['user'] sau khi login
 * Roles: 'customer' | 'staff' | 'admin'
 */
final class Auth
{
    /**
     * Đăng nhập bằng email + password
     * @return bool true nếu thành công
     */
    public static function attempt(string $email, string $password): bool
    {
        $user = (new User())->findByEmail($email);
        if ($user === null) {
            return false;
        }
        if ($user['status'] !== 'active') {
            return false;
        }
        if (($user['role'] ?? '') === 'customer' && empty($user['email_verified_at'])) {
            Flash::set('error', 'Vui lòng xác thực email trước khi đăng nhập.');
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        Session::regenerate(); // chống session fixation

        unset($user['password_hash']);
        Session::set('user', $user);
        return true;
    }

    public static function logout(): void
    {
        Session::forget('user');
        Session::regenerate();
    }

    public static function check(): bool
    {
        return Session::has('user');
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], ['admin', 'staff'], true);
    }

    public static function isCustomer(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'customer';
    }
}
