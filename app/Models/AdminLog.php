<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AdminLog extends Model
{
    protected string $table = 'admin_logs';

    protected array $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'description', 'ip_address', 'user_agent',
    ];

    /**
     * Phân trang log kèm tên & email admin thực hiện.
     * Hỗ trợ filter theo user_id, action, entity_type, date_from, date_to.
     *
     * @return array{rows: array, total: int, page: int, perPage: int, lastPage: int}
     */
    public function paginateWithUser(int $page, int $perPage, array $filters = []): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        [$clauses, $params] = $this->buildFilters($filters);
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $countStmt = $this->db()->prepare(
            "SELECT COUNT(*) FROM admin_logs al $where"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT al.*, u.full_name AS admin_name, u.email AS admin_email
            FROM admin_logs al
            JOIN users u ON u.id = al.user_id
            $where
            ORDER BY al.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);

        return [
            'rows'     => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    /** Danh sách action duy nhất — dùng cho dropdown filter */
    public function distinctActions(): array
    {
        return $this->db()
            ->query("SELECT DISTINCT action FROM admin_logs ORDER BY action ASC")
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** Danh sách entity_type duy nhất — dùng cho dropdown filter */
    public function distinctEntityTypes(): array
    {
        return $this->db()
            ->query("SELECT DISTINCT entity_type FROM admin_logs ORDER BY entity_type ASC")
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** Danh sách admin đã có log — dùng cho dropdown filter */
    public function distinctAdmins(): array
    {
        return $this->db()->query("
            SELECT DISTINCT u.id, u.full_name, u.email
            FROM admin_logs al
            JOIN users u ON u.id = al.user_id
            ORDER BY u.full_name ASC
        ")->fetchAll();
    }

    /**
     * Xây dựng WHERE clauses và params từ mảng filter.
     * Trả về [array $clauses, array $params].
     */
    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params  = [];

        if (!empty($filters['user_id'])) {
            $clauses[] = 'al.user_id = ?';
            $params[]  = (int)$filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $clauses[] = 'al.action = ?';
            $params[]  = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $clauses[] = 'al.entity_type = ?';
            $params[]  = $filters['entity_type'];
        }
        if (!empty($filters['date_from'])) {
            $clauses[] = 'DATE(al.created_at) >= ?';
            $params[]  = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $clauses[] = 'DATE(al.created_at) <= ?';
            $params[]  = $filters['date_to'];
        }

        return [$clauses, $params];
    }
}
