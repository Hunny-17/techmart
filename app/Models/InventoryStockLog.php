<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class InventoryStockLog extends Model
{
    protected string $table = 'inventory_stock_logs';

    protected array $fillable = [
        'product_id',
        'variant_id',
        'admin_user_id',
        'quantity',
        'stock_before',
        'stock_after',
    ];

    public function byProduct(int $productId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db()->prepare("
            SELECT
                isl.*,
                p.name AS product_name,
                pv.name AS variant_name,
                u.full_name AS admin_name,
                u.email AS admin_email
            FROM inventory_stock_logs isl
            JOIN products p ON p.id = isl.product_id
            LEFT JOIN product_variants pv ON pv.id = isl.variant_id
            JOIN users u ON u.id = isl.admin_user_id
            WHERE isl.product_id = ?
            ORDER BY isl.created_at DESC, isl.id DESC
            LIMIT $limit
        ");
        $stmt->execute([$productId]);

        return $stmt->fetchAll();
    }
}
