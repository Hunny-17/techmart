<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Order extends Model
{
    protected string $table = 'orders';

    protected array $fillable = [
        'user_id', 'total_amount', 'voucher_id', 'discount_amount', 'status',
        'shipping_address', 'payment_method', 'payment_reference_code', 'payment_status', 'note',
    ];

    /**
     * Lấy danh sách đơn hàng kèm thông tin khách hàng, có filter theo trạng thái.
     */
    public function paginateWithCustomer(
        ?string $status = null,
        int $page = 1,
        int $perPage = 10,
        ?string $keyword = null,
        ?string $paymentMethod = null
    ): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        if ($keyword !== null && $keyword !== '') {
            if (ctype_digit($keyword)) {
                $where[] = '(o.id = ? OR u.full_name LIKE ? OR u.email LIKE ? OR o.shipping_address LIKE ?)';
                $params[] = (int)$keyword;
            } else {
                $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR o.shipping_address LIKE ?)';
            }
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($paymentMethod !== null && in_array($paymentMethod, ['cod', 'bank_transfer', 'e_wallet'], true)) {
            $where[] = 'o.payment_method = ?';
            $params[] = $paymentMethod;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db()->prepare("
            SELECT COUNT(*)
            FROM orders o
            JOIN users u ON o.user_id = u.id
            $whereSql
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT
                o.*,
                u.full_name AS customer_name,
                u.email AS customer_email,
                u.phone AS customer_phone,
                COUNT(od.id) AS item_count,
                COALESCE(SUM(od.quantity), 0) AS total_quantity
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_details od ON od.order_id = o.id
            $whereSql
            GROUP BY o.id, u.full_name, u.email, u.phone
            ORDER BY o.created_at DESC, o.id DESC
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

    public function exportWithCustomer(
        ?string $status = null,
        ?string $keyword = null,
        ?string $paymentMethod = null
    ): array {
        $where = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }

        if ($keyword !== null && $keyword !== '') {
            if (ctype_digit($keyword)) {
                $where[] = '(o.id = ? OR u.full_name LIKE ? OR u.email LIKE ? OR o.shipping_address LIKE ?)';
                $params[] = (int)$keyword;
            } else {
                $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR o.shipping_address LIKE ?)';
            }
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($paymentMethod !== null && in_array($paymentMethod, ['cod', 'bank_transfer', 'e_wallet'], true)) {
            $where[] = 'o.payment_method = ?';
            $params[] = $paymentMethod;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db()->prepare("
            SELECT
                o.id,
                o.total_amount,
                o.status,
                o.payment_method,
                o.payment_reference_code,
                o.payment_status,
                o.shipping_address,
                o.created_at,
                u.full_name AS customer_name,
                u.email AS customer_email,
                u.phone AS customer_phone,
                COUNT(od.id) AS item_count,
                COALESCE(SUM(od.quantity), 0) AS total_quantity
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_details od ON od.order_id = o.id
            $whereSql
            GROUP BY o.id, u.full_name, u.email, u.phone
            ORDER BY o.created_at DESC, o.id DESC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Lấy một đơn hàng kèm thông tin khách hàng.
     */
    public function withCustomer(int $id): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT o.*, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function changeStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function changePaymentStatus(int $id, string $status): bool
    {
        return $this->update($id, ['payment_status' => $status]);
    }

    public function paginateByUser(int $userId, int $page = 1, int $perPage = 8, ?string $status = null): array
    {
        $where  = ['user_id = ?'];
        $params = [$userId];

        if ($status !== null && $status !== '') {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $page     = max(1, $page);
        $offset   = ($page - 1) * $perPage;

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM orders $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare(
            "SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);

        return [
            'rows'     => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }

    public function statusCountsByUser(int $userId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT status, COUNT(*) AS total FROM orders WHERE user_id = ? GROUP BY status'
        );
        $stmt->execute([$userId]);
        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(string)$row['status']] = (int)$row['total'];
        }
        return $counts;
    }

    public function byUser(int $userId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare("
            SELECT o.*,
                   COUNT(od.id) AS item_count,
                   COALESCE(SUM(od.quantity), 0) AS total_quantity
            FROM orders o
            LEFT JOIN order_details od ON od.order_id = o.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function totalSpentByUser(int $userId): float
    {
        $stmt = $this->db()->prepare("
            SELECT COALESCE(SUM(total_amount), 0) FROM orders
            WHERE user_id = ? AND status = 'delivered'
        ");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public function totalSavingsByUser(int $userId): float
    {
        $stmt = $this->db()->prepare("
            SELECT COALESCE(SUM(discount_amount), 0) FROM orders
            WHERE user_id = ? AND discount_amount > 0
        ");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public function totalDiscountGiven(): float
    {
        $stmt = $this->db()->query("SELECT COALESCE(SUM(discount_amount), 0) FROM orders WHERE discount_amount > 0");
        return (float)$stmt->fetchColumn();
    }

    public function deliveredRevenue(): float
    {
        $stmt = $this->db()->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM orders
            WHERE status = 'delivered'
        ");
        return (float)$stmt->fetchColumn();
    }

    public function deliveredRevenueToday(): float
    {
        $stmt = $this->db()->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM orders
            WHERE status = 'delivered'
              AND DATE(created_at) = CURDATE()
        ");

        return (float)$stmt->fetchColumn();
    }

    public function deliveredRevenueThisMonth(): float
    {
        $stmt = $this->db()->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM orders
            WHERE status = 'delivered'
              AND YEAR(created_at) = YEAR(CURDATE())
              AND MONTH(created_at) = MONTH(CURDATE())
        ");

        return (float)$stmt->fetchColumn();
    }

    public function statusCounts(): array
    {
        $stmt = $this->db()->query("
            SELECT status, COUNT(*) AS total
            FROM orders
            GROUP BY status
        ");

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(string)$row['status']] = (int)$row['total'];
        }

        return $counts;
    }

    public function paymentStatusCounts(): array
    {
        $stmt = $this->db()->query("
            SELECT payment_status, COUNT(*) AS total
            FROM orders
            GROUP BY payment_status
        ");

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(string)$row['payment_status']] = (int)$row['total'];
        }

        return $counts;
    }

    public function revenueLast7Days(): array
    {
        $stmt = $this->db()->query("
            SELECT DATE(created_at) AS date, COALESCE(SUM(total_amount), 0) AS amount
            FROM orders
            WHERE status = 'delivered'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ");

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['date']] = (float)$row['amount'];
        }

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $result[] = [
                'date' => $date,
                'amount' => $rows[$date] ?? 0,
            ];
        }

        return $result;
    }

    public function ordersLast7Days(): array
    {
        $stmt = $this->db()->query("
            SELECT DATE(created_at) AS date, COUNT(*) AS total
            FROM orders
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at)
        ");

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['date']] = (int)$row['total'];
        }

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $result[] = [
                'date' => $date,
                'total' => $rows[$date] ?? 0,
            ];
        }

        return $result;
    }

    public function topProducts(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db()->prepare("
            SELECT p.name, SUM(od.quantity) AS quantity
            FROM order_details od
            JOIN products p ON p.id = od.product_id
            JOIN orders o ON o.id = od.order_id
            WHERE o.status IN ('confirmed', 'shipping', 'delivered')
            GROUP BY p.id, p.name
            ORDER BY quantity DESC
            LIMIT $limit
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
