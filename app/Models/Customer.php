<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Customer extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'username', 'email', 'password_hash',
        'full_name', 'phone', 'address',
        'role', 'status',
    ];

    public function exportAll(?string $keyword = null, ?string $status = null): array
    {
        $where  = ["u.role = 'customer'"];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[]  = '(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $kw       = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }
        if ($status !== null && in_array($status, ['active', 'locked'], true)) {
            $where[]  = 'u.status = ?';
            $params[] = $status;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $stmt = $this->db()->prepare("
            SELECT u.id, u.full_name, u.email, u.phone, u.address,
                   u.status, u.email_verified_at, u.created_at,
                   COUNT(o.id) AS order_count,
                   COALESCE(SUM(CASE WHEN o.status='delivered' THEN o.total_amount ELSE 0 END), 0) AS total_spent
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            $whereSql
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allCustomers(): array
    {
        $stmt = $this->db()->query("
            SELECT u.*, COUNT(o.id) AS order_count
            FROM users u
            LEFT JOIN orders o ON o.user_id = u.id
            WHERE u.role = 'customer'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function paginateCustomers(
        int $page = 1,
        int $perPage = 10,
        ?string $keyword = null,
        ?string $status = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ["u.role = 'customer'"];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($status !== null && in_array($status, ['active', 'locked'], true)) {
            $where[] = 'u.status = ?';
            $params[] = $status;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM users u $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT
                u.*,
                COALESCE(om.order_count, 0) AS order_count,
                COALESCE(om.delivered_count, 0) AS delivered_count,
                COALESCE(om.total_spent, 0) AS total_spent,
                om.last_order_at
            FROM users u
            LEFT JOIN (
                SELECT
                    user_id,
                    COUNT(*) AS order_count,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_count,
                    SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) AS total_spent,
                    MAX(created_at) AS last_order_at
                FROM orders
                GROUP BY user_id
            ) om ON om.user_id = u.id
            $whereSql
            ORDER BY u.created_at DESC, u.id DESC
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

    public function findCustomer(int $id): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT *
            FROM users
            WHERE id = ? AND role = 'customer'
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function lock(int $id): bool
    {
        return $this->update($id, ['status' => 'locked']);
    }

    public function unlock(int $id): bool
    {
        return $this->update($id, ['status' => 'active']);
    }
}
