<?php
declare(strict_types=1);

namespace App\Core\Contracts;

/**
 * Contract cho entity có thể đăng nhập vào hệ thống TechMart.
 *
 * Áp dụng Dependency Inversion Principle (chữ D trong SOLID):
 * module cấp cao (Auth service, Controller) phụ thuộc vào abstraction này,
 * không phụ thuộc trực tiếp vào concrete class như User.
 * Nếu sau này có EmployeeAccount, GuestAccount... chỉ cần implement
 * interface này mà không cần sửa code phía trên.
 */
interface Authenticatable
{
    /** Trả về primary key của entity */
    public function getId(): int;

    /** Trả về địa chỉ email dùng để đăng nhập */
    public function getEmail(): string;

    /** Trả về role: 'customer' | 'staff' | 'admin' */
    public function getRole(): string;

    /** Kiểm tra tài khoản có đang bị khóa không */
    public function isLocked(): bool;

    /** Xác minh plain-text password so với hash đã lưu */
    public function verifyPassword(string $plainPassword): bool;
}
