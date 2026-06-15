<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class OrderDetail extends Model
{
    protected string $table = 'order_details';

    protected array $fillable = [
        'order_id', 'product_id', 'variant_id', 'quantity', 'unit_price',
    ];

    /**
     * Lấy các sản phẩm trong đơn hàng.
     */
    public function byOrder(int $orderId): array
    {
        $stmt = $this->db()->prepare("
            SELECT od.*, p.name AS product_name, p.image_url,
                   v.name AS variant_name, v.image_url AS variant_image
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            LEFT JOIN product_variants v ON v.id = od.variant_id
            WHERE od.order_id = ?
            ORDER BY od.id ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
