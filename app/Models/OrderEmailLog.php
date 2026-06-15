<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class OrderEmailLog extends Model
{
    protected string $table = 'order_email_logs';

    protected array $fillable = [
        'order_id',
        'recipient',
        'subject',
        'status',
        'send_status',
        'mail_file',
        'error_message',
    ];

    public function byOrder(int $orderId): array
    {
        $stmt = $this->db()->prepare("
            SELECT *
            FROM order_email_logs
            WHERE order_id = ?
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$orderId]);

        return $stmt->fetchAll();
    }

    public function findForOrder(int $id, int $orderId): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT *
            FROM order_email_logs
            WHERE id = ? AND order_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $orderId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
