<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Model - base class cho Active Record kiểu đơn giản
 *
 * Mỗi model con KẾ THỪA và set $table
 *
 * Cung cấp sẵn: all, find, where, create, update, delete, paginate
 * Mọi query đều dùng prepared statement → an toàn SQL injection
 *
 * Nếu cần query phức tạp, dùng $this->db()->prepare(...) trực tiếp
 */
abstract class Model
{
    /** Tên bảng - subclass phải set */
    protected string $table = '';

    /** Tên cột primary key */
    protected string $primaryKey = 'id';

    /** Whitelist cột được phép mass-assign (chống over-assign) */
    protected array $fillable = [];

    protected function db(): PDO
    {
        return Database::pdo();
    }

    /**
     * Lấy tất cả records
     * @return array<int,array<string,mixed>>
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db()->query("SELECT * FROM {$this->table} ORDER BY $orderBy");
        return $stmt->fetchAll();
    }

    /**
     * Tìm theo primary key
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Tìm 1 record theo cột bất kỳ
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1"
        );
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lấy nhiều record theo điều kiện đơn giản
     * @param array<string,mixed> $conditions  ['status' => 'active']
     */
    public function where(array $conditions, string $orderBy = 'id DESC'): array
    {
        $clauses = [];
        $params  = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "$col = ?";
            $params[]  = $val;
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        $stmt  = $this->db()->prepare(
            "SELECT * FROM {$this->table} $where ORDER BY $orderBy"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tạo record mới. Chỉ field trong $fillable mới được insert.
     * @return int Last insert ID
     */
    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $cols),
            implode(', ', $placeholders)
        );
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Cập nhật record theo primary key
     */
    public function update(int|string $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if ($data === []) {
            return false;
        }

        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";

        $params   = array_values($data);
        $params[] = $id;

        $stmt = $this->db()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xoá record theo primary key
     */
    public function delete(int|string $id): bool
    {
        $stmt = $this->db()->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    public function count(array $conditions = []): int
    {
        $clauses = [];
        $params  = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "$col = ?";
            $params[]  = $val;
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        $stmt  = $this->db()->prepare("SELECT COUNT(*) AS c FROM {$this->table} $where");
        $stmt->execute($params);
        return (int)$stmt->fetch()['c'];
    }

    /**
     * Phân trang đơn giản
     * @return array{rows: array, total: int, page: int, perPage: int, lastPage: int}
     */
    public function paginate(int $page = 1, int $perPage = 10, string $orderBy = 'id DESC'): array
    {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        $total   = (int)$this->db()->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        $stmt    = $this->db()->prepare(
            "SELECT * FROM {$this->table} ORDER BY $orderBy LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute();

        return [
            'rows'     => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }

    private function filterFillable(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
