<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductVariant extends Model
{
    protected string $table = 'product_variants';

    protected array $fillable = [
        'product_id', 'name', 'price', 'stock_quantity', 'image_url', 'status',
    ];

    public function activeByProduct(int $productId): array
    {
        return $this->where(['product_id' => $productId, 'status' => 'active'], 'id ASC');
    }

    public function byProduct(int $productId): array
    {
        return $this->where(['product_id' => $productId], 'id ASC');
    }

    public function hasActiveForProduct(int $productId): bool
    {
        $stmt = $this->db()->prepare("SELECT 1 FROM product_variants WHERE product_id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$productId]);

        return (bool)$stmt->fetchColumn();
    }

    public function findForProduct(int $id, int $productId): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT *
            FROM product_variants
            WHERE id = ? AND product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $productId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByProduct(int $productId): bool
    {
        $stmt = $this->db()->prepare("DELETE FROM product_variants WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }
}
