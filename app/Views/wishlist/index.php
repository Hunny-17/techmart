<?php /** @var array $items */ ?>
<?php $totalWishlist = count($items); ?>

<section class="page-hero wishlist-hero mb-4">
    <div>
        <span class="store-eyebrow">Danh sách yêu thích</span>
        <h1>Sản phẩm đã lưu</h1>
        <p>Lưu lại những thiết bị bạn đang cân nhắc để quay lại so sánh, xem tồn kho và đặt hàng nhanh hơn.</p>
    </div>
    <div class="wishlist-hero-count">
        <i class="bi bi-heart-fill"></i>
        <strong id="wishlist-count"><?= number_format($totalWishlist) ?></strong>
        <span id="wishlist-subtitle">sản phẩm đã lưu</span>
    </div>
</section>

<?php if (empty($items)): ?>
    <div class="page-empty-state wishlist-empty-state">
        <i class="bi bi-heart"></i>
        <h2>Chưa có sản phẩm yêu thích</h2>
        <p>Nhấn biểu tượng trái tim trên sản phẩm để lưu lại các lựa chọn bạn muốn xem sau.</p>
        <a href="<?= url('/products') ?>" class="btn btn-primary">
            Khám phá sản phẩm <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
<?php else: ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="text-muted small">Các sản phẩm trong danh sách vẫn cập nhật theo giá và tồn kho hiện tại.</div>
        <a href="<?= url('/products') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-grid me-1"></i> Xem tất cả sản phẩm
        </a>
    </div>

    <div class="row g-3">
        <?php foreach ($items as $p): ?>
            <div class="col-6 col-md-4 col-xl-3 wishlist-card-col">
                <article class="home-product-card wishlist-product-card">
                    <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-media">
                        <img src="<?= e($p['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>"
                             alt="<?= e($p['name']) ?>">
                        <?php if ((int)$p['stock_quantity'] <= 0): ?>
                            <span class="product-card-sold-out">Hết hàng</span>
                        <?php endif; ?>
                    </a>
                    <button type="button"
                            class="wishlist-btn wishlist-btn--card active"
                            data-product-id="<?= e($p['id']) ?>"
                            title="Xóa khỏi yêu thích">
                        <i class="bi bi-heart-fill"></i>
                    </button>
                    <div class="home-product-body">
                        <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-name">
                            <?= e($p['name']) ?>
                        </a>
                        <div class="home-product-meta">
                            <span class="home-product-price"><?= format_vnd((float)$p['price']) ?></span>
                            <?php if ((int)$p['stock_quantity'] > 0): ?>
                                <span class="badge text-bg-light border">Còn <?= number_format((int)$p['stock_quantity']) ?></span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Hết hàng</span>
                            <?php endif; ?>
                        </div>
                        <div class="wishlist-card-actions">
                            <?php if ((int)$p['stock_quantity'] > 0): ?>
                                <form action="<?= url('/cart/add') ?>" method="post" class="flex-fill">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-secondary flex-fill" disabled>
                                    Hết hàng
                                </button>
                            <?php endif; ?>
                            <a href="<?= url('/products/' . $p['id']) ?>"
                               class="btn btn-sm btn-outline-secondary wishlist-view-btn" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script nonce="<?= csp_nonce() ?>">
document.addEventListener('wishlist:toggled', e => {
    if (e.detail.wishlisted) {
        return;
    }

    const card = e.target.closest('.wishlist-card-col');
    if (!card) {
        return;
    }

    card.remove();
    const remaining = document.querySelectorAll('.wishlist-product-card').length;
    if (remaining === 0) {
        location.reload();
        return;
    }
    const count = document.getElementById('wishlist-count');
    if (count) {
        count.textContent = new Intl.NumberFormat('vi-VN').format(remaining);
    }
});
</script>
