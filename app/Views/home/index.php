<?php
/**
 * @var array $featured
 * @var array $categories
 * @var array $newArrivals
 */
$featured = $featured ?? [];
$categories = $categories ?? [];
$newArrivals = $newArrivals ?? [];
$heroProduct = $featured[0] ?? null;
$heroImage = $heroProduct['image_url'] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80';
$heroSlides = array_slice($featured, 0, 5);
$isNewProduct = static function (array $product): bool {
    if (empty($product['created_at'])) {
        return false;
    }

    $createdAt = strtotime((string)$product['created_at']);
    return $createdAt !== false && $createdAt >= strtotime('-14 days');
};
$homeStockBadge = static function (array $product): string {
    $stock = (int)($product['stock_quantity'] ?? 0);
    if ($stock <= 0) {
        return '<span class="badge text-bg-danger">Hết hàng</span>';
    }
    if ($stock <= 5) {
        return '<span class="badge text-bg-warning text-dark border">Còn ít ' . number_format($stock) . '</span>';
    }

    return '<span class="badge text-bg-light border">Còn ' . number_format($stock) . '</span>';
};
$homeProductCard = static function (array $product, array $wishlistedIds, callable $isNewProduct, callable $stockBadge): void {
    $stock = (int)($product['stock_quantity'] ?? 0);
    $isWishlisted = in_array((int)$product['id'], $wishlistedIds, true);
    ?>
    <article class="home-product-card home-page-product-card">
        <a href="<?= url('/products/' . $product['id']) ?>" class="home-product-media">
            <img src="<?= e($product['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>" alt="<?= e($product['name']) ?>">
            <?php if ($isNewProduct($product)): ?>
                <span class="product-card-new-badge">Mới</span>
            <?php endif; ?>
            <?php if ($stock > 0 && $stock <= 5): ?>
                <span class="product-card-low-stock-badge">Còn ít</span>
            <?php endif; ?>
            <?php if ($stock <= 0): ?>
                <span class="product-card-sold-out">Hết hàng</span>
            <?php endif; ?>
        </a>
        <button type="button"
                class="wishlist-btn wishlist-btn--card <?= $isWishlisted ? 'active' : '' ?>"
                data-product-id="<?= e($product['id']) ?>"
                title="<?= $isWishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
            <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
        </button>
        <div class="home-product-body">
            <a href="<?= url('/products/' . $product['id']) ?>" class="home-product-name"><?= e($product['name']) ?></a>
            <div class="home-product-meta">
                <span class="home-product-price"><?= format_vnd((float)$product['price']) ?></span>
                <?= $stockBadge($product) ?>
            </div>
            <a href="<?= url('/products/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                Xem chi tiết
            </a>
        </div>
    </article>
    <?php
};
$categoryIcons = [
    'laptop' => 'bi-laptop',
    'máy tính' => 'bi-pc-display',
    'may tinh' => 'bi-pc-display',
    'điện thoại' => 'bi-phone',
    'dien thoai' => 'bi-phone',
    'phone' => 'bi-phone',
    'tai nghe' => 'bi-headphones',
    'phụ kiện' => 'bi-usb-plug',
    'phu kien' => 'bi-usb-plug',
    'bàn phím' => 'bi-keyboard',
    'ban phim' => 'bi-keyboard',
    'chuột' => 'bi-mouse',
    'chuot' => 'bi-mouse',
];
?>

<?php if (!empty($heroSlides)): ?>
<section class="tech-home-hero tech-home-hero-slider mb-4" data-home-hero>
    <div class="tech-home-hero-track">
        <?php foreach ($heroSlides as $index => $slide): ?>
            <?php $slideImage = $slide['image_url'] ?: 'https://placehold.co/720x520?text=TechMart'; ?>
            <article class="tech-home-hero-slide <?= $index === 0 ? 'active' : '' ?>" data-hero-slide>
                <div class="tech-home-hero-copy">
                    <span class="store-eyebrow"><?= $isNewProduct($slide) ? 'Sản phẩm mới' : 'TechMart nổi bật' ?></span>
                    <h1><?= e($slide['name']) ?></h1>
                    <p>
                        <?= e(mb_strlen((string)($slide['description'] ?? '')) > 0
                            ? mb_substr(strip_tags((string)$slide['description']), 0, 130) . '...'
                            : 'Khám phá sản phẩm công nghệ chính hãng, thông tin rõ ràng và hình ảnh trực quan để mua sắm tự tin hơn.') ?>
                    </p>
                    <div class="tech-home-hero-actions">
                        <a class="btn btn-primary btn-lg" href="<?= url('/products/' . $slide['id']) ?>">
                            Xem chi tiết <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a class="btn btn-outline-light btn-lg" href="<?= url('/products') ?>">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="tech-home-hero-price">
                        <span>Giá bán</span>
                        <strong><?= format_vnd((float)$slide['price']) ?></strong>
                        <?php if ($isNewProduct($slide)): ?>
                            <em>Mới về</em>
                        <?php endif; ?>
                        <?php if ((int)$slide['stock_quantity'] > 0 && (int)$slide['stock_quantity'] <= 5): ?>
                            <em class="tech-home-hero-low-stock">Còn ít</em>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tech-home-hero-visual">
                    <img src="<?= e($slideImage) ?>" alt="<?= e($slide['name']) ?>">
                    <div class="tech-hero-product-note">
                        <span><?= $isNewProduct($slide) ? 'Mới tại TechMart' : 'Đang được chú ý' ?></span>
                        <strong><?= e($slide['name']) ?></strong>
                        <small>
                            <?= format_vnd((float)$slide['price']) ?> ·
                            <?= (int)$slide['stock_quantity'] > 0 ? 'Còn ' . number_format((int)$slide['stock_quantity']) : 'Hết hàng' ?>
                        </small>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (count($heroSlides) > 1): ?>
        <div class="tech-home-hero-dots" aria-label="Chọn sản phẩm nổi bật">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <button type="button"
                        class="<?= $index === 0 ? 'active' : '' ?>"
                        data-hero-dot="<?= e($index) ?>"
                        aria-label="Xem slide <?= e($index + 1) ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php else: ?>
<section class="tech-home-hero mb-4">
    <div class="tech-home-hero-copy">
        <span class="store-eyebrow">TechMart</span>
        <h1>Thiết bị công nghệ cho setup làm việc và giải trí hiện đại</h1>
        <p>Khám phá sản phẩm chính hãng, thông tin rõ ràng, ảnh thực tế và lựa chọn mẫu trực quan để mua sắm tự tin hơn.</p>
        <a class="btn btn-primary btn-lg" href="<?= url('/products') ?>">Xem sản phẩm <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</section>
<?php endif; ?>

<section class="home-service-strip mb-4" aria-label="TechMart service highlights">
    <div>
        <i class="bi bi-patch-check"></i>
        <span>Sản phẩm chính hãng</span>
    </div>
    <div>
        <i class="bi bi-images"></i>
        <span>Ảnh và mẫu rõ ràng</span>
    </div>
    <div>
        <i class="bi bi-shield-check"></i>
        <span>Quy trình đặt hàng minh bạch</span>
    </div>
</section>

<?php if (!empty($categories)): ?>
    <section class="mb-4">
        <div class="section-heading">
            <div>
                <h2>Danh mục nổi bật</h2>
                <p>Chọn nhanh nhóm thiết bị bạn đang cần.</p>
            </div>
            <a href="<?= url('/products') ?>" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="home-category-grid">
            <?php foreach ($categories as $category): ?>
                <?php
                $categoryName = !empty($category['parent_name']) ? $category['parent_name'] . ' / ' . $category['name'] : $category['name'];
                $normalizedName = function_exists('mb_strtolower')
                    ? mb_strtolower((string)$categoryName, 'UTF-8')
                    : strtolower((string)$categoryName);
                $icon = 'bi-cpu';
                foreach ($categoryIcons as $keyword => $candidateIcon) {
                    if (str_contains($normalizedName, $keyword)) {
                        $icon = $candidateIcon;
                        break;
                    }
                }
                ?>
                <a class="home-category-tile" href="<?= url('/products?cat=' . $category['id']) ?>">
                    <span class="home-category-icon"><i class="bi <?= e($icon) ?>"></i></span>
                    <span>
                        <strong><?= e($categoryName) ?></strong>
                        <small>Xem sản phẩm phù hợp</small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($newArrivals)): ?>
<section class="home-new-arrivals mb-5">
    <div class="section-heading">
        <div>
            <h2>Sản phẩm mới về</h2>
            <p>Các lựa chọn vừa được cập nhật tại TechMart.</p>
        </div>
        <a href="<?= url('/products?sort=newest') ?>" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
    </div>
    <div class="row g-3">
        <?php foreach ($newArrivals as $p): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <?php $homeProductCard($p, $wishlistedIds ?? [], $isNewProduct, $homeStockBadge); ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="home-featured-products">
    <div class="section-heading">
        <div>
            <h2>Sản phẩm gợi ý</h2>
            <p>Các lựa chọn đang có sẵn tại TechMart.</p>
        </div>
        <a href="<?= url('/products') ?>" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
    </div>

    <?php if (empty($featured)): ?>
        <div class="alert alert-info">Chưa có sản phẩm nào.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($featured as $p): ?>
                <div class="col-6 col-md-4 col-xl-3">
                    <?php $homeProductCard($p, $wishlistedIds ?? [], $isNewProduct, $homeStockBadge); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($recentProducts)): ?>
<section class="mt-5 home-recent-products">
    <div class="section-heading">
        <div>
            <h2>Đã xem gần đây</h2>
            <p>Các sản phẩm bạn đã ghé thăm.</p>
        </div>
    </div>
    <div class="row g-3">
        <?php foreach ($recentProducts as $r): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <?php $homeProductCard($r, $wishlistedIds ?? [], $isNewProduct, $homeStockBadge); ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>