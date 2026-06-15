<?php /** @var array $suggested */ ?>

<div class="error-404-block">
    <div class="error-404-code">404</div>
    <h1 class="error-404-title">Trang không tìm thấy</h1>
    <p class="error-404-msg">Trang bạn đang tìm không tồn tại, có thể đã bị xóa hoặc địa chỉ URL không đúng.</p>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <a href="<?= url('/') ?>" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> Về trang chủ
        </a>
        <a href="<?= url('/products') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-grid me-1"></i> Xem sản phẩm
        </a>
    </div>
</div>

<?php if (!empty($suggested)): ?>
    <div class="error-404-suggestions">
        <div class="error-404-suggestions-title">Có thể bạn quan tâm</div>
        <div class="row g-3">
            <?php foreach ($suggested as $p): ?>
                <div class="col-6 col-md-3">
                    <article class="home-product-card">
                        <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-media">
                            <img src="<?= e($p['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>"
                                 alt="<?= e($p['name']) ?>">
                            <?php if ((int)($p['stock_quantity'] ?? 1) <= 0): ?>
                                <span class="product-card-sold-out">Hết hàng</span>
                            <?php endif; ?>
                        </a>
                        <div class="home-product-body">
                            <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-name"><?= e($p['name']) ?></a>
                            <div class="home-product-meta">
                                <span class="home-product-price"><?= format_vnd((float)$p['price']) ?></span>
                            </div>
                            <a href="<?= url('/products/' . $p['id']) ?>"
                               class="btn btn-sm btn-outline-primary w-100">Xem chi tiết</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
