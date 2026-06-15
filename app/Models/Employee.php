<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Employee extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'username', 'email', 'password_hash',
        'full_name', 'phone', 'address',
        'role', 'status',
    ];

    public function allStaff(): array
    {
        return $this->where(['role' => 'staff'], 'created_at DESC');
    }

    public function paginateStaff(
        int $page = 1,
        int $perPage = 10,
        ?string $keyword = null,
        ?string $status = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ["role = 'staff'"];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($status !== null && in_array($status, ['active', 'locked'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM users $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT *
            FROM users
            $whereSql
            ORDER BY created_at DESC, id DESC
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

    public function findStaff(int $id): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT *
            FROM users
            WHERE id = ? AND role = 'staff'
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findBy('email', $email) !== null;
    }

    public function createStaff(array $data): int
    {
        return $this->create([
            'username' => $data['email'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? '',
            'address' => '',
            'role' => 'staff',
            'status' => 'active',
        ]);
    }

    public function deleteStaff(int $id): bool
    {
        $stmt = $this->db()->prepare("
            DELETE FROM users
            WHERE id = ?
                AND role = 'staff'
                AND NOT EXISTS (
                    SELECT 1
                    FROM orders
                    WHERE orders.user_id = users.id
                )
        ");
        return $stmt->execute([$id]);
    }

    public function lockStaff(int $id): bool
    {
        $stmt = $this->db()->prepare("UPDATE users SET status = 'locked' WHERE id = ? AND role = 'staff'");
        return $stmt->execute([$id]);
    }

    public function unlockStaff(int $id): bool
    {
        $stmt = $this->db()->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'staff'");
        return $stmt->execute([$id]);
    }
}
