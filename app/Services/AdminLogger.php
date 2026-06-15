<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Models\AdminLog;

/**
 * Ghi audit trail mỗi khi admin thực hiện action có side-effect.
 *
 * Thiết kế "best-effort": mọi exception trong quá trình ghi log đều bị
 * catch và error_log — không để logging fail làm crash action chính.
 * VD: DB log down → sản phẩm vẫn xoá được, chỉ mất log entry.
 */
final class AdminLogger
{
    /**
     * Ghi một hành động admin vào bảng admin_logs.
     *
     * @param string      $action      Key ngắn gọn, vd 'create', 'delete', 'change_status'
     * @param string      $entityType  Loại đối tượng bị tác động: 'product', 'order', ...
     * @param int|null    $entityId    ID đối tượng bị tác động (null khi entity chưa có ID)
     * @param string|null $description Mô tả human-readable tiếng Việt, không chứa password/token
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null,
    ): void {
        try {
            // Chỉ log khi đã xác thực và có quyền admin/staff
            if (!Auth::check() || !Auth::isAdmin()) {
                return;
            }

            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            // Truncate user-agent để không vượt VARCHAR(255)
            $ua = mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

            (new AdminLog())->create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip_address'  => $ip,
                'user_agent'  => $ua,
            ]);
        } catch (\Throwable $e) {
            // Không để lỗi logging làm crash request đang xử lý
            error_log('[AdminLogger] ' . $e->getMessage());
        }
    }
}
