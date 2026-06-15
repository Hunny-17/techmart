<?php /** @var array $products */ ?>

<section class="page-hero compare-hero mb-4">
    <div>
        <span class="store-eyebrow">So sánh sản phẩm</span>
        <h1>So sánh chi tiết</h1>
        <p>Đối chiếu thông số và giá cả để chọn sản phẩm phù hợp nhất với nhu cầu của bạn.</p>
    </div>
    <div class="compare-hero-actions">
        <a href="<?= url('/products') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua sắm
        </a>
    </div>
</section>

<?php if (empty($products)): ?>
    <div class="page-empty-state">
        <i class="bi bi-columns-gap"></i>
        <h2>Chưa có sản phẩm nào để so sánh</h2>
        <p>Thêm sản phẩm bằng cách nhấn nút <strong>So sánh</strong> trên trang danh sách hoặc chi tiết sản phẩm.</p>
        <a href="<?= url('/products') ?>" class="btn btn-primary">
            <i class="bi bi-grid me-1"></i> Xem sản phẩm
        </a>
    </div>
<?php else: ?>

<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <h2>Bảng so sánh</h2>
            <p><?= count($products) ?> sản phẩm đang so sánh</p>
        </div>
        <i class="bi bi-columns-gap"></i>
    </div>

    <div class="compare-table-wrap">
        <table class="compare-table">
            <thead>
                <tr>
                    <th class="compare-attr-col"></th>
                    <?php foreach ($products as $p): ?>
                        <th class="compare-prod-col">
                            <div class="compare-prod-header">
                                <a href="<?= url('/products/' . $p['id']) ?>" class="compare-prod-img-wrap">
                                    <img src="<?= e($p['image_url'] ?: 'https://placehold.co/200x160?text=No+Image') ?>"
                                         alt="<?= e($p['name']) ?>">
                                </a>
                                <a href="<?= url('/products/' . $p['id']) ?>" class="compare-prod-name">
                                    <?= e($p['name']) ?>
                                </a>
                                <button class="btn btn-sm btn-outline-danger compare-remove-btn"
                                        data-compare-id="<?= e($p['id']) ?>">
                                    <i class="bi bi-x-lg me-1"></i> Xóa
                                </button>
                            </div>
                        </th>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?>
                        <th class="compare-prod-col compare-empty-col">
                            <div class="compare-prod-header compare-prod-empty">
                                <i class="bi bi-plus-circle compare-empty-icon"></i>
                                <span>Thêm sản phẩm</span>
                                <a href="<?= url('/products') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    Chọn sản phẩm
                                </a>
                            </div>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <tr class="compare-row">
                    <td class="compare-attr">Giá bán</td>
                    <?php foreach ($products as $p): ?>
                        <td class="compare-val compare-val-price">
                            <?= format_vnd((float)$p['price']) ?>
                        </td>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?><td class="compare-val compare-empty-val">—</td><?php endfor; ?>
                </tr>
                <tr class="compare-row">
                    <td class="compare-attr">Danh mục</td>
                    <?php foreach ($products as $p): ?>
                        <td class="compare-val"><?= e($p['category_name'] ?? '—') ?></td>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?><td class="compare-val compare-empty-val">—</td><?php endfor; ?>
                </tr>
                <tr class="compare-row">
                    <td class="compare-attr">Tình trạng</td>
                    <?php foreach ($products as $p): ?>
                        <td class="compare-val">
                            <?php if ((int)$p['stock_quantity'] > 0): ?>
                                <span class="badge text-bg-success">Còn hàng (<?= number_format((int)$p['stock_quantity']) ?>)</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Hết hàng</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?><td class="compare-val compare-empty-val">—</td><?php endfor; ?>
                </tr>
                <tr class="compare-row">
                    <td class="compare-attr">Mô tả</td>
                    <?php foreach ($products as $p): ?>
                        <td class="compare-val compare-val-desc">
                            <?= e(mb_substr(strip_tags($p['description'] ?? ''), 0, 200, 'UTF-8')) ?>
                            <?php if (mb_strlen(strip_tags($p['description'] ?? ''), 'UTF-8') > 200): ?>…<?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?><td class="compare-val compare-empty-val">—</td><?php endfor; ?>
                </tr>
                <tr class="compare-row compare-row-action">
                    <td class="compare-attr"></td>
                    <?php foreach ($products as $p): ?>
                        <td class="compare-val">
                            <a href="<?= url('/products/' . $p['id']) ?>" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-bag-plus me-1"></i> Xem &amp; đặt mua
                            </a>
                        </td>
                    <?php endforeach; ?>
                    <?php for ($i = count($products); $i < 3; $i++): ?><td class="compare-val compare-empty-val"></td><?php endfor; ?>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php endif; ?>
