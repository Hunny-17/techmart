<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class LoginAttempt extends Model
{
    protected string $table = 'login_attempts';

    protected array $fillable = ['ip_address', 'email', 'attempted_at', 'success'];

    /**
     * Đếm số lần đăng nhập thất bại trong N phút gần đây theo IP + email.
     * Dùng để kiểm tra có vượt ngưỡng chưa trước khi cho phép thử tiếp.
     */
    public function countRecentFailures(string $ip, string $email, int $windowMinutes = 15): int
    {
        $stmt = $this->db()->prepare("
            SELECT COUNT(*) AS c
            FROM login_attempts
            WHERE ip_address  = ?
              AND email        = ?
              AND success      = 0
              AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$ip, $email, $windowMinutes]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Trả về timestamp của lần thử cuối cùng (bất kể thành công hay thất bại),
     * null nếu chưa có record nào.
     */
    public function lastAttemptAt(string $ip, string $email): ?string
    {
        $stmt = $this->db()->prepare("
            SELECT attempted_at
            FROM login_attempts
            WHERE ip_address = ?
              AND email       = ?
            ORDER BY attempted_at DESC
            LIMIT 1
        ");
        $stmt->execute([$ip, $email]);
        $val = $stmt->fetchColumn();

        return $val !== false ? (string)$val : null;
    }

    /**
     * Trả về số giây còn lại trong lockout window của IP+email.
     * Tính từ lần fail đầu tiên trong cửa sổ + lockoutMinutes.
     * 0 nếu không còn bị khoá.
     */
    public function remainingLockoutSeconds(
        string $ip,
        string $email,
        int $windowMinutes,
        int $lockoutMinutes,
    ): int {
        $stmt = $this->db()->prepare("
            SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(),
                DATE_ADD(MIN(attempted_at), INTERVAL ? MINUTE)
            )) AS remaining
            FROM login_attempts
            WHERE ip_address  = ?
              AND email        = ?
              AND success      = 0
              AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$lockoutMinutes, $ip, $email, $windowMinutes]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Xoá toàn bộ fail log của IP+email để reset bộ đếm sau khi login thành công.
     */
    public function deleteFailures(string $ip, string $email): void
    {
        $this->db()->prepare("
            DELETE FROM login_attempts
            WHERE ip_address = ? AND email = ? AND success = 0
        ")->execute([$ip, $email]);
    }

    /**
     * Xoá các log cũ hơn N ngày để bảng không phình ra theo thời gian.
     * Trả về số hàng đã xoá.
     */
    public function cleanup(int $olderThanDays = 7): int
    {
        $stmt = $this->db()->prepare("
            DELETE FROM login_attempts
            WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$olderThanDays]);

        return $stmt->rowCount();
    }
}
