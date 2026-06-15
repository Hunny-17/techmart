<?php
$variants = $variants ?? [];
$stockLogs = $stockLogs ?? [];
$variantTotal = array_sum(array_map(static fn($variant) => (int)$variant['stock_quantity'], $variants));
$baseStock = (int)$product['stock_quantity'];
$totalStock = $baseStock + $variantTotal;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1">Nhập kho sản phẩm</h2>
        <div class="text-muted"><?= e($product['name']) ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Sửa sản phẩm
        </a>
        <a href="<?= url('/admin/products') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng tồn kho</div>
                <div class="display-6 fw-semibold"><?= number_format($totalStock) ?></div>
                <div class="text-muted small">Chính <?= number_format($baseStock) ?><?= $variants ? ' · Mẫu ' . number_format($variantTotal) : '' ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Danh mục</div>
                <div class="h5 mb-0"><?= e($product['category_name'] ?? 'Chưa phân loại') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Số mẫu</div>
                <div class="h5 mb-0"><?= number_format(count($variants)) ?></div>
            </div>
        </div>
    </div>
</div>

<form action="<?= url('/admin/products/' . $product['id'] . '/stock') ?>" method="post" class="card shadow-sm">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="d-flex align-items-start gap-3 mb-4">
            <img class="admin-product-thumb" src="<?= e($product['image_url'] ?: 'https://placehold.co/80') ?>" alt="<?= e($product['name']) ?>">
            <div class="flex-grow-1">
                <label class="form-label fw-semibold">Sản phẩm chính</label>
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="form-control-plaintext">Hiện có: <strong><?= number_format($baseStock) ?></strong></div>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="product_quantity" class="form-control restock-input" min="0" step="1" value="0" placeholder="Số lượng cộng thêm">
                    </div>
                    <div class="col-md-3">
                        <span class="badge text-bg-light border">Sau nhập: <span class="restock-after" data-current="<?= $baseStock ?>"><?= number_format($baseStock) ?></span></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($variants): ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:72px">Ảnh</th>
                            <th>Mẫu sản phẩm</th>
                            <th class="text-center">Tồn hiện tại</th>
                            <th style="width:260px">Nhập thêm</th>
                            <th class="text-center">Sau nhập</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $variant): ?>
                            <?php $currentStock = (int)$variant['stock_quantity']; ?>
                            <tr>
                                <td>
                                    <img class="admin-product-thumb" src="<?= e($variant['image_url'] ?: $product['image_url'] ?: 'https://placehold.co/64') ?>" alt="<?= e($variant['name']) ?>">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($variant['name']) ?></div>
                                    <div class="text-muted small">#<?= e($variant['id']) ?></div>
                                </td>
                                <td class="text-center"><?= number_format($currentStock) ?></td>
                                <td>
                                    <input type="number" name="variant_quantities[<?= e($variant['id']) ?>]" class="form-control restock-input" min="0" step="1" value="0">
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border restock-after" data-current="<?= $currentStock ?>"><?= number_format($currentStock) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                Sản phẩm này chưa có mẫu riêng, chỉ cần nhập kho cho sản phẩm chính.
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="text-muted small">Số lượng nhập kho sẽ được cộng thêm, không ghi đè tồn hiện tại.</div>
        <button class="btn btn-primary">
            <i class="bi bi-box-arrow-in-down"></i> Xác nhận nhập kho
        </button>
    </div>
</form>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="fw-semibold">Lịch sử nhập kho</div>
        <span class="text-muted small">20 lần gần nhất của sản phẩm này</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($stockLogs)): ?>
            <div class="text-center text-muted py-4">Chưa có lịch sử nhập kho.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Thời gian</th>
                            <th>Người nhập</th>
                            <th>Mục nhập</th>
                            <th class="text-end">Số lượng</th>
                            <th class="text-center">Trước</th>
                            <th class="text-center">Sau</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockLogs as $log): ?>
                            <tr>
                                <td>
                                    <div><?= e(date('d/m/Y H:i', strtotime((string)$log['created_at']))) ?></div>
                                    <div class="text-muted small">#<?= e($log['id']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($log['admin_name'] ?: $log['admin_email']) ?></div>
                                    <div class="text-muted small"><?= e($log['admin_email']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($log['variant_name'])): ?>
                                        <span class="badge text-bg-primary">Mẫu</span>
                                        <?= e($log['variant_name']) ?>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Chính</span>
                                        <?= e($product['name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-success fw-semibold">+<?= number_format((int)$log['quantity']) ?></td>
                                <td class="text-center"><?= number_format((int)$log['stock_before']) ?></td>
                                <td class="text-center fw-semibold"><?= number_format((int)$log['stock_after']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= csp_nonce() ?>">
document.querySelectorAll('.restock-input').forEach(input => {
    input.addEventListener('input', () => {
        const container = input.closest('.row, tr');
        const output = container ? container.querySelector('.restock-after') : null;
        if (!output) {
            return;
        }

        const current = Number(output.dataset.current || 0);
        const add = Math.max(0, Number(input.value || 0));
        output.textContent = new Intl.NumberFormat('vi-VN').format(current + add);
    });
});
</script>
