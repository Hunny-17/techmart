<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    protected string $table = 'categories';

    protected array $fillable = [
        'name', 'slug', 'parent_id',
    ];

    public function options(?int $excludeId = null): array
    {
        $sql = "
            SELECT c.*, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON p.id = c.parent_id
        ";
        $params = [];

        if ($excludeId !== null) {
            $sql .= " WHERE c.id <> ?";
            $params[] = $excludeId;
        }

        $sql .= " ORDER BY c.parent_id IS NOT NULL, c.name ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function paginateWithProductCount(
        int $page = 1,
        int $perPage = 10,
        ?string $keyword = null
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(c.name LIKE ? OR c.slug LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM categories c $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT
                c.*,
                p.name AS parent_name,
                COUNT(pr.id) AS product_count
            FROM categories c
            LEFT JOIN categories p ON p.id = c.parent_id
            LEFT JOIN products pr ON pr.category_id = c.id
            $whereSql
            GROUP BY c.id, p.name
            ORDER BY c.created_at DESC, c.id DESC
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

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM categories WHERE slug = ?";
        $params = [$slug];

        if ($ignoreId !== null) {
            $sql .= " AND id <> ?";
            $params[] = $ignoreId;
        }

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function productCount(int $id): int
    {
        $stmt = $this->db()->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);

        return (int)$stmt->fetchColumn();
    }
}
