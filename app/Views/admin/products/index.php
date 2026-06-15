<?php
/**
 * @var array $result
 * @var array $filters
 */
$filters = $filters ?? ['q' => '', 'status' => '', 'stock' => ''];

$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter([
        'q' => $filters['q'] ?? '',
        'status' => $filters['status'] ?? '',
        'stock' => $filters['stock'] ?? '',
        'page' => $page,
    ], static fn($value) => $value !== '' && $value !== null);

    return url('/admin/products' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý sản phẩm</h2>
        <div class="text-muted">Theo dõi sản phẩm, mẫu sản phẩm và tồn kho.</div>
    </div>
    <a href="<?= url('/admin/products/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
    </a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/products') ?>">
            <div class="col-md-5">
                <label class="form-label">Tìm sản phẩm</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Tên hoặc mô tả">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Đang bán</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Ngừng bán</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tồn kho</label>
                <select name="stock" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="low" <?= $filters['stock'] === 'low' ? 'selected' : '' ?>>Sắp hết</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/products') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 admin-products-table">
            <thead class="table-light">
                <tr>
                    <th style="width:72px">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th class="text-center">Mẫu</th>
                    <th class="text-end">Giá</th>
                    <th class="text-center">Tồn kho</th>
                    <th class="text-center">Trạng thái</th>
                    <th style="width:160px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Chưa có sản phẩm phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $p): ?>
                        <?php
                        $variantCount = (int)($p['variant_count'] ?? 0);
                        $baseStock = (int)$p['stock_quantity'];
                        $variantStock = (int)($p['variant_stock_total'] ?? 0);
                        $totalStock = $baseStock + $variantStock;
                        $minVariantStock = $p['min_variant_stock'] !== null ? (int)$p['min_variant_stock'] : null;
                        $isLowStock = $baseStock <= 5 || ($minVariantStock !== null && $minVariantStock <= 5);
                        $hasOrderHistory = (int)($p['order_detail_count'] ?? 0) > 0;
                        $lowStockVariants = trim((string)($p['low_stock_variants'] ?? ''));
                        ?>
                        <tr>
                            <td>
                                <img class="admin-product-thumb" src="<?= e($p['image_url'] ?: 'https://placehold.co/64') ?>" alt="<?= e($p['name']) ?>">
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($p['name']) ?></div>
                                <div class="text-muted small">
                                    #<?= e($p['id']) ?>
                                    <?php if (!empty($p['category_name'])): ?>
                                        · <?= e($p['category_name']) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($hasOrderHistory): ?>
                                    <span class="badge text-bg-light border mt-1">Đã có đơn</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($variantCount > 0): ?>
                                    <span class="badge text-bg-primary"><?= number_format($variantCount) ?> mẫu</span>
                                <?php else: ?>
                                    <span class="text-muted small">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= format_vnd((float)$p['price']) ?></td>
                            <td class="text-center">
                                <div class="<?= $isLowStock ? 'text-danger fw-semibold' : 'fw-semibold' ?>"><?= number_format($totalStock) ?></div>
                                <div class="text-muted small">Chính: <?= number_format($baseStock) ?><?= $variantCount > 0 ? ' · Mẫu: ' . number_format($variantStock) : '' ?></div>
                                <?php if ($isLowStock): ?>
                                    <div class="small text-danger">
                                        <?php if ($baseStock <= 5): ?>
                                            Chính thấp<?= $lowStockVariants !== '' ? ' · ' : '' ?>
                                        <?php endif; ?>
                                        <?php if ($lowStockVariants !== ''): ?>
                                            Mẫu: <?= e($lowStockVariants) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['status'] === 'active'): ?>
                                    <span class="badge bg-success">Đang bán</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ngừng bán</span>
                                <?php endif; ?>
                                <?php if ($isLowStock): ?>
                                    <div class="mt-1"><span class="badge text-bg-warning">Sắp hết</span></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/products/' . $p['id'] . '/stock') ?>" class="btn btn-sm btn-outline-success" title="Nhập kho">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </a>
                                <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= url('/admin/products/' . $p['id'] . '/delete') ?>"
                                      method="post" class="d-inline"
                                      data-confirm="Xóa sản phẩm này? Nếu đã có đơn hàng, hệ thống sẽ chuyển sang ngừng bán để giữ lịch sử.">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm <?= $hasOrderHistory ? 'btn-outline-warning' : 'btn-outline-danger' ?>"
                                            title="<?= $hasOrderHistory ? 'Ngừng bán để giữ lịch sử đơn hàng' : 'Xóa sản phẩm chưa phát sinh đơn' ?>">
                                        <i class="bi <?= $hasOrderHistory ? 'bi-archive' : 'bi-trash' ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pageUrlFn = $pageUrl; require __DIR__ . '/../../partials/pagination.php'; ?>
