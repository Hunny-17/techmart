<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Wishlist extends Model
{
    protected string $table = 'wishlists';

    protected array $fillable = ['user_id', 'product_id'];

    public function isWishlisted(int $userId, int $productId): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Toggle wishlist — trả về trạng thái mới (true = đã thêm, false = đã xoá)
     */
    public function toggle(int $userId, int $productId): bool
    {
        if ($this->isWishlisted($userId, $productId)) {
            $stmt = $this->db()->prepare(
                'DELETE FROM wishlists WHERE user_id = ? AND product_id = ?'
            );
            $stmt->execute([$userId, $productId]);
            return false;
        }

        $this->create(['user_id' => $userId, 'product_id' => $productId]);
        return true;
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM wishlists WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function byUserWithProducts(int $userId): array
    {
        $stmt = $this->db()->prepare("
            SELECT w.id AS wishlist_id, w.created_at AS wishlisted_at,
                   p.id, p.name, p.price, p.image_url, p.status, p.stock_quantity
            FROM wishlists w
            JOIN products p ON p.id = w.product_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** @return int[] */
    public function productIdsByUser(int $userId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT product_id FROM wishlists WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'product_id'));
    }
}
