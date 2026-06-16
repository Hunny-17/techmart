<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Voucher extends Model
{
    protected string $table = 'vouchers';

    protected array $fillable = [
        'code', 'discount_type', 'discount_value',
        'min_order', 'max_uses', 'expires_at', 'is_active',
    ];

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM vouchers WHERE code = ? LIMIT 1');
        $stmt->execute([strtoupper(trim($code))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Kiểm tra xem code có hợp lệ với đơn hàng $orderTotal không.
     *
     * @return array{valid: bool, error: string, discount: float, voucher: array|null}
     */
    public function validate(string $code, float $orderTotal): array
    {
        $v = $this->findByCode($code);

        if ($v === null) {
            return ['valid' => false, 'error' => 'Mã giảm giá không tồn tại.', 'discount' => 0.0, 'voucher' => null];
        }
        if (!(bool)$v['is_active']) {
            return ['valid' => false, 'error' => 'Mã giảm giá đã bị vô hiệu hóa.', 'discount' => 0.0, 'voucher' => null];
        }
        if ($v['expires_at'] !== null && strtotime((string)$v['expires_at']) < time()) {
            return ['valid' => false, 'error' => 'Mã giảm giá đã hết hạn.', 'discount' => 0.0, 'voucher' => null];
        }
        if ($v['max_uses'] !== null && (int)$v['used_count'] >= (int)$v['max_uses']) {
            return ['valid' => false, 'error' => 'Mã giảm giá đã hết lượt sử dụng.', 'discount' => 0.0, 'voucher' => null];
        }
        if ((float)$v['min_order'] > 0 && $orderTotal < (float)$v['min_order']) {
            $minFmt = number_format((float)$v['min_order'], 0, ',', '.') . 'đ';
            return ['valid' => false, 'error' => 'Đơn hàng tối thiểu ' . $minFmt . ' để dùng mã này.', 'discount' => 0.0, 'voucher' => null];
        }

        $discount = $v['discount_type'] === 'percent'
            ? $orderTotal * ((float)$v['discount_value'] / 100.0)
            : (float)$v['discount_value'];
        $discount = min($discount, $orderTotal);

        return ['valid' => true, 'error' => '', 'discount' => round($discount), 'voucher' => $v];
    }

    public function availableForCustomer(float $orderTotal = 0.0, int $limit = 6): array
    {
        $limit = max(1, min(12, $limit));
        $stmt = $this->db()->prepare("
            SELECT *
            FROM vouchers
            WHERE is_active = 1
              AND (expires_at IS NULL OR expires_at > NOW())
              AND (max_uses IS NULL OR used_count < max_uses)
              AND COALESCE(min_order, 0) <= ?
            ORDER BY discount_value DESC, created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$orderTotal]);

        return $stmt->fetchAll();
    }

    public function orderUsageCount(int $id): int
    {
        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM orders WHERE voucher_id = ?');
        $stmt->execute([$id]);

        return (int)$stmt->fetchColumn();
    }
    public function incrementUsed(int $id): void
    {
        $this->db()->prepare('UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?')->execute([$id]);
    }

    public function decrementUsed(int $id): void
    {
        // GREATEST(0, ...) đảm bảo không xuống âm
        $this->db()->prepare('UPDATE vouchers SET used_count = GREATEST(0, used_count - 1) WHERE id = ?')->execute([$id]);
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        if ($ignoreId !== null) {
            $stmt = $this->db()->prepare('SELECT 1 FROM vouchers WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([strtoupper(trim($code)), $ignoreId]);
        } else {
            $stmt = $this->db()->prepare('SELECT 1 FROM vouchers WHERE code = ? LIMIT 1');
            $stmt->execute([strtoupper(trim($code))]);
        }
        return (bool)$stmt->fetchColumn();
    }

    public function stats(): array
    {
        $stmt = $this->db()->query("
            SELECT
                SUM(CASE WHEN is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) THEN 1 ELSE 0 END) AS active_count,
                COUNT(*) AS total_count,
                COALESCE(SUM(used_count), 0) AS total_used
            FROM vouchers
        ");
        $row = $stmt->fetch();
        return [
            'active_count' => (int)($row['active_count'] ?? 0),
            'total_count'  => (int)($row['total_count'] ?? 0),
            'total_used'   => (int)($row['total_used'] ?? 0),
        ];
    }

    public function paginateFiltered(int $page = 1, int $perPage = 15, ?string $keyword = null, ?string $activeFilter = null): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where  = [];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[]  = 'code LIKE ?';
            $params[] = '%' . strtoupper($keyword) . '%';
        }
        if ($activeFilter === '1' || $activeFilter === '0') {
            $where[]  = 'is_active = ?';
            $params[] = (int)$activeFilter;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM vouchers $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("SELECT * FROM vouchers $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);

        return [
            'rows'     => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }
}
