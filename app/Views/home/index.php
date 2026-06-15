<?php
/**
 * @var array $featured
 * @var array $categories
 */
$featured = $featured ?? [];
$categories = $categories ?? [];
$heroProduct = $featured[0] ?? null;
$heroImage = $heroProduct['image_url'] ?? 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80';
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

<section class="tech-home-hero mb-4">
    <div class="tech-home-hero-copy">
        <span class="store-eyebrow">TechMart</span>
        <h1>Thiết bị công nghệ cho setup làm việc và giải trí hiện đại</h1>
        <p>
            Khám phá sản phẩm chính hãng, thông tin rõ ràng, ảnh thực tế và lựa chọn mẫu trực quan để mua sắm tự tin hơn.
        </p>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary btn-lg" href="<?= url('/products') ?>">
                Xem sản phẩm <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <?php if ($heroProduct): ?>
                <a class="btn btn-outline-light btn-lg" href="<?= url('/products/' . $heroProduct['id']) ?>">
                    Sản phẩm nổi bật
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="tech-home-hero-visual">
        <img src="<?= e($heroImage) ?>" alt="<?= e($heroProduct['name'] ?? 'TechMart products') ?>">
        <?php if ($heroProduct): ?>
            <div class="tech-hero-product-note">
                <span>Đang được chú ý</span>
                <strong><?= e($heroProduct['name']) ?></strong>
                <small><?= format_vnd((float)$heroProduct['price']) ?></small>
            </div>
        <?php endif; ?>
    </div>
</section>

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

<section>
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
                    <article class="home-product-card">
                        <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-media">
                            <img src="<?= e($p['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>" alt="<?= e($p['name']) ?>">
                            <?php if ((int)$p['stock_quantity'] <= 0): ?>
                                <span class="product-card-sold-out">Hết hàng</span>
                            <?php endif; ?>
                        </a>
                        <?php $isWishlisted = in_array((int)$p['id'], $wishlistedIds ?? [], true); ?>
                        <button type="button"
                                class="wishlist-btn wishlist-btn--card <?= $isWishlisted ? 'active' : '' ?>"
                                data-product-id="<?= e($p['id']) ?>"
                                title="<?= $isWishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
                            <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        </button>
                        <div class="home-product-body">
                            <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-name"><?= e($p['name']) ?></a>
                            <div class="home-product-meta">
                                <span class="home-product-price"><?= format_vnd((float)$p['price']) ?></span>
                                <span class="badge text-bg-light border">Còn <?= number_format((int)$p['stock_quantity']) ?></span>
                            </div>
                            <a href="<?= url('/products/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                                Xem chi tiết
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($recentProducts)): ?>
<section class="mt-5">
    <div class="section-heading">
        <div>
            <h2>Đã xem gần đây</h2>
            <p>Các sản phẩm bạn đã ghé thăm.</p>
        </div>
    </div>
    <div class="row g-3">
        <?php foreach ($recentProducts as $r): ?>
            <?php $isWishlisted = in_array((int)$r['id'], $wishlistedIds ?? [], true); ?>
            <div class="col-6 col-md-4 col-xl-3">
                <article class="home-product-card">
                    <a href="<?= url('/products/' . $r['id']) ?>" class="home-product-media">
                        <img src="<?= e($r['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>"
                             alt="<?= e($r['name']) ?>">
                        <?php if ((int)$r['stock_quantity'] <= 0): ?>
                            <span class="product-card-sold-out">Hết hàng</span>
                        <?php endif; ?>
                    </a>
                    <button type="button"
                            class="wishlist-btn wishlist-btn--card <?= $isWishlisted ? 'active' : '' ?>"
                            data-product-id="<?= e($r['id']) ?>"
                            title="<?= $isWishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
                        <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    </button>
                    <div class="home-product-body">
                        <a href="<?= url('/products/' . $r['id']) ?>" class="home-product-name"><?= e($r['name']) ?></a>
                        <div class="home-product-meta">
                            <span class="home-product-price"><?= format_vnd((float)$r['price']) ?></span>
                            <span class="badge text-bg-light border">Còn <?= number_format((int)$r['stock_quantity']) ?></span>
                        </div>
                        <a href="<?= url('/products/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                            Xem chi tiết
                        </a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
