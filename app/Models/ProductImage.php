<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductImage extends Model
{
    protected string $table = 'product_images';

    protected array $fillable = [
        'product_id', 'image_url', 'sort_order',
    ];

    public function byProduct(int $productId): array
    {
        return $this->where(['product_id' => $productId], 'sort_order ASC, id ASC');
    }

    public function deleteByProduct(int $productId): bool
    {
        $stmt = $this->db()->prepare("DELETE FROM product_images WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }
}
