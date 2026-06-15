<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Product - sản phẩm
 *
 * Mẫu CRUD đầy đủ. Codex tham khảo file này để scaffold model khác:
 * Order, OrderDetail, Review, Category.
 */
final class Product extends Model
{
    protected string $table = 'products';

    protected array $fillable = [
        'category_id', 'name', 'slug', 'description',
        'price', 'stock_quantity', 'image_url', 'status',
    ];

    /**
     * Lấy sản phẩm kèm tên danh mục (JOIN)
     */
    public function withCategory(int $id): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Sản phẩm nổi bật (cho trang chủ)
     */
    public function featured(int $limit = 8): array
    {
        $stmt = $this->db()->prepare("
            SELECT * FROM products
            WHERE status = 'active'
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int,array{id:int,name:string,price:float,image_url:string}> */
    public function suggest(string $keyword, int $limit = 6): array
    {
        $stmt = $this->db()->prepare("
            SELECT id, name, price, image_url
            FROM products
            WHERE status = 'active' AND name LIKE ?
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        $stmt->execute(['%' . $keyword . '%']);
        return $stmt->fetchAll();
    }

    public function lowStockCount(int $threshold = 5): int
    {
        $stmt = $this->db()->prepare("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM products
                    WHERE status = 'active' AND stock_quantity <= ?
                ) +
                (
                    SELECT COUNT(*)
                    FROM product_variants
                    WHERE status = 'active' AND stock_quantity <= ?
                ) AS total
        ");
        $stmt->execute([$threshold, $threshold]);

        return (int)$stmt->fetchColumn();
    }

    public function lowStockItems(int $limit = 8, int $threshold = 5): array
    {
        $limit = max(1, min(30, $limit));
        $stmt = $this->db()->prepare("
            (
                SELECT
                    p.id AS product_id,
                    NULL AS variant_id,
                    p.name AS product_name,
                    NULL AS variant_name,
                    p.stock_quantity AS stock_quantity,
                    'product' AS item_type
                FROM products p
                WHERE p.status = 'active'
                  AND p.stock_quantity <= ?
            )
            UNION ALL
            (
                SELECT
                    p.id AS product_id,
                    pv.id AS variant_id,
                    p.name AS product_name,
                    pv.name AS variant_name,
                    pv.stock_quantity AS stock_quantity,
                    'variant' AS item_type
                FROM product_variants pv
                JOIN products p ON p.id = pv.product_id
                WHERE pv.status = 'active'
                  AND p.status = 'active'
                  AND pv.stock_quantity <= ?
            )
            ORDER BY stock_quantity ASC, product_name ASC
            LIMIT $limit
        ");
        $stmt->execute([$threshold, $threshold]);

        return $stmt->fetchAll();
    }

    public function inventoryItems(?string $keyword = null, ?string $stockFilter = null, int $threshold = 5): array
    {
        $params = [];
        $where = [];

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(product_name LIKE ? OR variant_name LIKE ? OR category_name LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($stockFilter === 'out') {
            $where[] = 'stock_quantity <= 0';
        } elseif ($stockFilter === 'low') {
            $where[] = 'stock_quantity > 0 AND stock_quantity <= ?';
            $params[] = $threshold;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db()->prepare("
            SELECT *
            FROM (
                SELECT
                    p.id AS product_id,
                    NULL AS variant_id,
                    p.name AS product_name,
                    NULL AS variant_name,
                    c.name AS category_name,
                    p.stock_quantity AS stock_quantity,
                    p.image_url AS image_url,
                    p.status AS status,
                    'product' AS item_type
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                UNION ALL
                SELECT
                    p.id AS product_id,
                    pv.id AS variant_id,
                    p.name AS product_name,
                    pv.name AS variant_name,
                    c.name AS category_name,
                    pv.stock_quantity AS stock_quantity,
                    COALESCE(pv.image_url, p.image_url) AS image_url,
                    pv.status AS status,
                    'variant' AS item_type
                FROM product_variants pv
                JOIN products p ON p.id = pv.product_id
                LEFT JOIN categories c ON c.id = p.category_id
            ) inventory
            $whereSql
            ORDER BY stock_quantity ASC, product_name ASC, variant_name ASC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function inventorySummary(int $threshold = 5): array
    {
        $stmt = $this->db()->prepare("
            SELECT
                COUNT(*) AS total_items,
                SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) AS out_stock_items,
                SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= ? THEN 1 ELSE 0 END) AS low_stock_items,
                COALESCE(SUM(stock_quantity), 0) AS total_stock
            FROM (
                SELECT stock_quantity FROM products
                UNION ALL
                SELECT stock_quantity FROM product_variants
            ) inventory
        ");
        $stmt->execute([$threshold]);
        $row = $stmt->fetch() ?: [];

        return [
            'total_items' => (int)($row['total_items'] ?? 0),
            'out_stock_items' => (int)($row['out_stock_items'] ?? 0),
            'low_stock_items' => (int)($row['low_stock_items'] ?? 0),
            'total_stock' => (int)($row['total_stock'] ?? 0),
        ];
    }

    public function hasOrderDetails(int $id): bool
    {
        $stmt = $this->db()->prepare("
            SELECT COUNT(*)
            FROM order_details
            WHERE product_id = ?
        ");
        $stmt->execute([$id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function deactivate(int $id): bool
    {
        return $this->update($id, ['status' => 'inactive']);
    }

    public function adminPaginate(
        int $page = 1,
        int $perPage = 10,
        ?string $keyword = null,
        ?string $status = null,
        bool $lowStockOnly = false,
        int $lowStockThreshold = 5
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
        }

        if ($status !== null && in_array($status, ['active', 'inactive'], true)) {
            $where[] = 'p.status = ?';
            $params[] = $status;
        }

        if ($lowStockOnly) {
            $where[] = "(
                p.stock_quantity <= ?
                OR EXISTS (
                    SELECT 1
                    FROM product_variants pv_low
                    WHERE pv_low.product_id = p.id
                        AND pv_low.status = 'active'
                        AND pv_low.stock_quantity <= ?
                )
            )";
            $params[] = $lowStockThreshold;
            $params[] = $lowStockThreshold;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM products p $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db()->prepare("
            SELECT
                p.*,
                c.name AS category_name,
                COUNT(pv.id) AS variant_count,
                COALESCE(SUM(CASE WHEN pv.status = 'active' THEN pv.stock_quantity ELSE 0 END), 0) AS variant_stock_total,
                MIN(CASE WHEN pv.status = 'active' THEN pv.stock_quantity ELSE NULL END) AS min_variant_stock,
                COUNT(DISTINCT od.id) AS order_detail_count,
                GROUP_CONCAT(DISTINCT
                    CASE
                        WHEN pv.status = 'active' AND pv.stock_quantity <= ?
                        THEN CONCAT(pv.name, ' (', pv.stock_quantity, ')')
                        ELSE NULL
                    END
                    ORDER BY pv.stock_quantity ASC, pv.name ASC
                    SEPARATOR ', '
                ) AS low_stock_variants
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_variants pv ON pv.product_id = p.id
            LEFT JOIN order_details od ON od.product_id = p.id
            $whereSql
            GROUP BY p.id, c.name
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT $perPage OFFSET $offset
        ");
        $dataParams = $params;
        array_unshift($dataParams, $lowStockThreshold);
        $stmt->execute($dataParams);

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Tìm kiếm + filter cho customer page
     */
    public function search(
        ?string $keyword,
        ?int    $categoryId,
        int     $page     = 1,
        int     $perPage  = 12,
        string  $sort     = 'newest',
        ?float  $minPrice = null,
        ?float  $maxPrice = null
    ): array {
        $where  = ["status = 'active'"];
        $params = [];

        if ($keyword !== null && $keyword !== '') {
            $where[]  = '(name LIKE ? OR description LIKE ?)';
            $kw       = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
        }
        if ($categoryId !== null) {
            $where[]  = 'category_id = ?';
            $params[] = $categoryId;
        }
        if ($minPrice !== null) {
            $where[]  = 'price >= ?';
            $params[] = $minPrice;
        }
        if ($maxPrice !== null) {
            $where[]  = 'price <= ?';
            $params[] = $maxPrice;
        }

        $orderBy = match ($sort) {
            'price_asc'  => 'price ASC, created_at DESC',
            'price_desc' => 'price DESC, created_at DESC',
            'name_asc'   => 'name ASC',
            default      => 'created_at DESC',
        };

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        // Count
        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM products $whereSql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Data
        $dataStmt = $this->db()->prepare(
            "SELECT * FROM products $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset"
        );
        $dataStmt->execute($params);

        return [
            'rows'     => $dataStmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => (int)ceil($total / $perPage),
        ];
    }

    /** Lấy nhiều sản phẩm theo danh sách ID, giữ nguyên thứ tự */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $idList       = implode(',', $ids);
        $stmt         = $this->db()->prepare("
            SELECT * FROM products
            WHERE status = 'active' AND id IN ($placeholders)
            ORDER BY FIELD(id, $idList)
        ");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    public function related(int $productId, int $categoryId, int $limit = 4): array
    {
        $stmt = $this->db()->prepare("
            SELECT * FROM products
            WHERE status = 'active'
              AND category_id = ?
              AND id != ?
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$categoryId, $productId]);
        return $stmt->fetchAll();
    }
}
