<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Review extends Model
{
    protected string $table = 'reviews';

    protected array $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'comment', 'status',
    ];

    /**
     * Lấy đánh giá đang hiển thị của sản phẩm.
     */
    public function visibleByProduct(int $productId): array
    {
        $stmt = $this->db()->prepare("
            SELECT r.*, u.full_name AS customer_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'visible'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy tất cả đánh giá kèm thông tin sản phẩm và khách hàng cho admin.
     */
    public function allWithDetails(?string $status = null): array
    {
        $where = '';
        $params = [];

        if ($status !== null && $status !== '') {
            $where = 'WHERE r.status = ?';
            $params[] = $status;
        }

        $stmt = $this->db()->prepare("
            SELECT r.*, p.name AS product_name, u.full_name AS customer_name, u.email AS customer_email
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            $where
            ORDER BY r.created_at DESC, r.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function paginateWithDetails(
        int $page = 1,
        int $perPage = 10,
        ?string $status = null,
        ?int $rating = null,
        ?string $keyword = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'r.status = ?';
            $params[] = $status;
        }

        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $where[] = 'r.rating = ?';
            $params[] = $rating;
        }

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(p.name LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR r.comment LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db()->prepare("
            SELECT COUNT(*)
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            $whereSql
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT r.*, p.name AS product_name, u.full_name AS customer_name, u.email AS customer_email
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            $whereSql
            ORDER BY r.created_at DESC, r.id DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }

    public function hide(int $id): bool
    {
        return $this->update($id, ['status' => 'hidden']);
    }

    public function show(int $id): bool
    {
        return $this->update($id, ['status' => 'visible']);
    }

    /**
     * Lấy các đơn đã giao có sản phẩm này mà user chưa review.
     */
    public function reviewableOrders(int $userId, int $productId): array
    {
        $stmt = $this->db()->prepare("
            SELECT DISTINCT o.id, o.created_at
            FROM orders o
            JOIN order_details od ON od.order_id = o.id
            LEFT JOIN reviews r
                ON r.order_id = o.id
                AND r.product_id = od.product_id
                AND r.user_id = o.user_id
            WHERE o.user_id = ?
                AND od.product_id = ?
                AND o.status = 'delivered'
                AND r.id IS NULL
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetchAll();
    }

    public function canUserReview(int $userId, int $productId, int $orderId): bool
    {
        $stmt = $this->db()->prepare("
            SELECT COUNT(*)
            FROM orders o
            JOIN order_details od ON od.order_id = o.id
            LEFT JOIN reviews r
                ON r.order_id = o.id
                AND r.product_id = od.product_id
                AND r.user_id = o.user_id
            WHERE o.id = ?
                AND o.user_id = ?
                AND od.product_id = ?
                AND o.status = 'delivered'
                AND r.id IS NULL
        ");
        $stmt->execute([$orderId, $userId, $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
