<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Contracts\Authenticatable;

/**
 * User - khách hàng và nhân viên dùng chung bảng, phân biệt qua `role`
 *
 * Roles: 'customer' | 'staff' | 'admin'
 * Status: 'active' | 'locked'
 */
final class User extends Model implements Authenticatable
{
    protected string $table = 'users';

    protected array $fillable = [
        'username', 'email', 'password_hash',
        'full_name', 'phone', 'address',
        'role', 'status',
        'email_verified_at', 'email_verification_token', 'email_verification_sent_at',
        'password_reset_token', 'password_reset_expires_at', 'password_reset_sent_at',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->update($id, [
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
        ]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    public function createPasswordResetToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db()->prepare("
            UPDATE users
            SET password_reset_token = ?,
                password_reset_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR),
                password_reset_sent_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([hash('sha256', $token), $id]);

        return $token;
    }

    public function passwordResetRetryAfter(array $user, int $cooldownSeconds = 60): int
    {
        return $this->retryAfter((string)($user['password_reset_sent_at'] ?? ''), $cooldownSeconds);
    }

    public function emailVerificationRetryAfter(array $user, int $cooldownSeconds = 60): int
    {
        return $this->retryAfter((string)($user['email_verification_sent_at'] ?? ''), $cooldownSeconds);
    }

    public function findByPasswordResetToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db()->prepare("
            SELECT *
            FROM users
            WHERE password_reset_token = ?
              AND password_reset_expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function resetPassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
            'password_reset_sent_at' => null,
        ]);
    }

    public function createEmailVerificationToken(int $id): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db()->prepare("
            UPDATE users
            SET email_verification_token = ?,
                email_verification_sent_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([hash('sha256', $token), $id]);

        return $token;
    }

    public function findByVerificationToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db()->prepare("
            SELECT *
            FROM users
            WHERE email_verification_token = ?
              AND email_verification_sent_at > NOW() - INTERVAL 24 HOUR
            LIMIT 1
        ");
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function hasExpiredVerificationToken(string $token): bool
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db()->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE email_verification_token = ?
              AND email_verification_sent_at <= NOW() - INTERVAL 24 HOUR
        ");
        $stmt->execute([$hash]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function markEmailVerified(int $id): bool
    {
        $stmt = $this->db()->prepare("
            UPDATE users
            SET email_verified_at          = NOW(),
                email_verification_token   = NULL,
                email_verification_sent_at = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function isEmailVerified(array $user): bool
    {
        return !empty($user['email_verified_at']);
    }

    private function retryAfter(string $sentAt, int $cooldownSeconds): int
    {
        if ($sentAt === '') {
            return 0;
        }

        $stmt = $this->db()->prepare("
            SELECT GREATEST(0, ? - TIMESTAMPDIFF(SECOND, ?, NOW())) AS retry_after
        ");
        $stmt->execute([$cooldownSeconds, $sentAt]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Tạo user mới với password đã hash
     */
    public function register(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['role']   = 'customer';
        $data['status'] = 'active';
        unset($data['password']);
        return $this->create($data);
    }

    public function lock(int $id): bool
    {
        return $this->update($id, ['status' => 'locked']);
    }

    public function unlock(int $id): bool
    {
        return $this->update($id, ['status' => 'active']);
    }

    /**
     * Lấy customers (cho admin xem)
     */
    public function customers(): array
    {
        return $this->where(['role' => 'customer']);
    }

    public function newCustomersToday(): int
    {
        $stmt = $this->db()->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'customer'
              AND DATE(created_at) = CURDATE()
        ");

        return (int)$stmt->fetchColumn();
    }

    public function newCustomersThisMonth(): int
    {
        $stmt = $this->db()->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'customer'
              AND YEAR(created_at) = YEAR(CURDATE())
              AND MONTH(created_at) = MONTH(CURDATE())
        ");

        return (int)$stmt->fetchColumn();
    }

    public function unverifiedCustomersCount(): int
    {
        $stmt = $this->db()->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'customer'
              AND email_verified_at IS NULL
        ");

        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy staff (cho admin xem)
     */
    public function staffs(): array
    {
        return $this->where(['role' => 'staff']);
    }

    // -------------------------------------------------------------------------
    // Authenticatable interface — Dependency Inversion Principle
    // -------------------------------------------------------------------------
    // $row chứa một hàng DB được nạp qua fromArray().
    // Các method phía trên (findByEmail, customers...) vẫn trả về array thuần
    // để Auth.php hiện tại không bị ảnh hưởng.
    // -------------------------------------------------------------------------

    /** Row dữ liệu của một user cụ thể, null nếu chưa được nạp */
    private ?array $row = null;

    /**
     * Factory: tạo instance đã mang dữ liệu một user cụ thể.
     * Dùng khi cần type-hint Authenticatable thay vì làm việc với array thô.
     *
     * Ví dụ: $user = User::fromArray($row); login($user);
     */
    public static function fromArray(array $row): self
    {
        $instance       = new self();
        $instance->row  = $row;
        return $instance;
    }

    /** Trả về primary key của user */
    public function getId(): int
    {
        return (int)($this->row['id'] ?? 0);
    }

    /** Trả về email đăng nhập */
    public function getEmail(): string
    {
        return (string)($this->row['email'] ?? '');
    }

    /** Trả về role: 'customer' | 'staff' | 'admin' */
    public function getRole(): string
    {
        return (string)($this->row['role'] ?? '');
    }

    /** Trả về true nếu tài khoản đang bị khóa */
    public function isLocked(): bool
    {
        return ($this->row['status'] ?? '') === 'locked';
    }

    /** Xác minh plain-text password so với bcrypt hash đã lưu */
    public function verifyPassword(string $plainPassword): bool
    {
        $hash = $this->row['password_hash'] ?? '';
        return $hash !== '' && password_verify($plainPassword, $hash);
    }
}
