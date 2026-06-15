<?php
/**
 * @var array  $items
 * @var array  $summary
 * @var array  $filters
 * @var int    $threshold
 */
$filters = $filters ?? ['q' => '', 'stock' => ''];
$summary = $summary ?? ['total_items' => 0, 'out_stock_items' => 0, 'low_stock_items' => 0, 'total_stock' => 0];
$threshold = (int)($threshold ?? 5);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý tồn kho</h2>
        <div class="text-muted">Theo dõi tồn kho sản phẩm chính và từng mẫu sản phẩm.</div>
    </div>
    <a href="<?= url('/admin/products') ?>" class="btn btn-outline-primary">
        <i class="bi bi-box-seam me-1"></i> Danh sách sản phẩm
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng mục tồn kho</div>
                <div class="h3 mb-0"><?= number_format((int)$summary['total_items']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng số lượng</div>
                <div class="h3 mb-0"><?= number_format((int)$summary['total_stock']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Sắp hết hàng</div>
                <div class="h3 mb-0 text-warning"><?= number_format((int)$summary['low_stock_items']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Hết hàng</div>
                <div class="h3 mb-0 text-danger"><?= number_format((int)$summary['out_stock_items']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/inventory') ?>">
            <div class="col-md-7">
                <label class="form-label">Tìm kiếm</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Tên sản phẩm, mẫu hoặc danh mục">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tồn kho</label>
                <select name="stock" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="low" <?= $filters['stock'] === 'low' ? 'selected' : '' ?>>Sắp hết</option>
                    <option value="out" <?= $filters['stock'] === 'out' ? 'selected' : '' ?>>Hết hàng</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/inventory') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:72px">Ảnh</th>
                        <th>Mục tồn kho</th>
                        <th>Danh mục</th>
                        <th class="text-center">Loại</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end" style="width:130px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không có mục tồn kho phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $stock = (int)$item['stock_quantity'];
                            $isOut = $stock <= 0;
                            $isLow = !$isOut && $stock <= $threshold;
                            ?>
                            <tr>
                                <td>
                                    <img class="admin-product-thumb" src="<?= e($item['image_url'] ?: 'https://placehold.co/64') ?>" alt="<?= e($item['product_name']) ?>">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($item['product_name']) ?></div>
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <div class="text-muted small">Mẫu: <?= e($item['variant_name']) ?></div>
                                    <?php else: ?>
                                        <div class="text-muted small">Sản phẩm chính</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($item['category_name'] ?? 'Chưa phân loại') ?></td>
                                <td class="text-center">
                                    <?php if ($item['item_type'] === 'variant'): ?>
                                        <span class="badge text-bg-primary">Mẫu</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Chính</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="<?= $isOut || $isLow ? 'text-danger fw-semibold' : 'fw-semibold' ?>">
                                        <?= number_format($stock) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($isOut): ?>
                                        <span class="badge text-bg-danger">Hết hàng</span>
                                    <?php elseif ($isLow): ?>
                                        <span class="badge text-bg-warning">Sắp hết</span>
                                    <?php elseif ($item['status'] === 'active'): ?>
                                        <span class="badge text-bg-success">Ổn</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Ngừng bán</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/products/' . $item['product_id'] . '/stock') ?>" class="btn btn-sm btn-outline-success" title="Nhập kho">
                                        <i class="bi bi-box-arrow-in-down"></i>
                                    </a>
                                    <a href="<?= url('/admin/products/' . $item['product_id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Sửa sản phẩm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
