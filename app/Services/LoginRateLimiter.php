<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\LoginAttempt;

/**
 * Chống brute-force đăng nhập bằng cách giới hạn số lần thử theo IP + email.
 *
 * Thuật toán:
 *   - Mỗi lần thử đăng nhập đều được ghi vào bảng login_attempts.
 *   - Nếu số lần fail trong cửa sổ $windowMinutes đạt $maxAttempts
 *     → tính thời gian lockout từ lần fail đầu tiên trong cửa sổ + $lockoutMinutes.
 *   - Khi đăng nhập thành công, xoá toàn bộ fail log của IP+email để reset bộ đếm,
 *     tránh người dùng thật bị lock sau khi nhớ lại mật khẩu đúng.
 *   - Khoá theo cặp IP+email (không khoá IP toàn cục) để tránh ảnh hưởng
 *     nhiều người dùng dùng chung mạng (NAT/VPN).
 */
final class LoginRateLimiter
{
    public function __construct(
        private readonly int $maxAttempts    = 5,
        private readonly int $windowMinutes  = 15,
        private readonly int $lockoutMinutes = 30,
    ) {}

    /**
     * Kiểm tra IP + email đã vượt ngưỡng và còn trong lockout window chưa.
     * Trả về true nếu cần block (không được thử tiếp).
     */
    public function tooManyAttempts(string $ip, string $email): bool
    {
        $model    = new LoginAttempt();
        $failures = $model->countRecentFailures($ip, $email, $this->windowMinutes);

        if ($failures < $this->maxAttempts) {
            return false;
        }

        // Đã đủ số lần fail — kiểm tra thêm xem lockout còn hiệu lực không.
        $remaining = $model->remainingLockoutSeconds(
            $ip, $email, $this->windowMinutes, $this->lockoutMinutes
        );

        return $remaining > 0;
    }

    /**
     * Ghi lại kết quả một lần thử đăng nhập.
     * Nếu thành công → xoá fail log cũ để reset bộ đếm.
     */
    public function recordAttempt(string $ip, string $email, bool $success): void
    {
        $model = new LoginAttempt();

        // Không truyền attempted_at — để MySQL tự set DEFAULT CURRENT_TIMESTAMP,
        // tránh lệch giờ giữa PHP timezone và MySQL timezone.
        $model->create([
            'ip_address' => $ip,
            'email'      => $email,
            'success'    => $success ? 1 : 0,
        ]);

        // Sau khi login thành công, xoá fail log cũ → reset bộ đếm brute-force.
        if ($success) {
            $model->deleteFailures($ip, $email);
        }
    }

    /**
     * Trả về số giây còn phải chờ trước khi được thử lại.
     * 0 nếu không bị block.
     */
    public function availableIn(string $ip, string $email): int
    {
        $model    = new LoginAttempt();
        $failures = $model->countRecentFailures($ip, $email, $this->windowMinutes);

        if ($failures < $this->maxAttempts) {
            return 0;
        }

        return $model->remainingLockoutSeconds(
            $ip, $email, $this->windowMinutes, $this->lockoutMinutes
        );
    }
}
