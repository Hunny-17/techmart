<?php
/**
 * @var array $product
 * @var array $reviews
 * @var float $averageRating
 * @var int $reviewCount
 * @var bool $canUserReview
 * @var array $userReview
 */
$gallery = [];
if (!empty($product['image_url'])) {
    $gallery[] = $product['image_url'];
}
foreach ($images ?? [] as $image) {
    $gallery[] = $image['image_url'];
}
$variantData = array_map(static fn($variant) => [
    'id' => (int)$variant['id'],
    'name' => (int)$variant['id'] === 7 && str_contains((string)$variant['name'], 'Tr?ng') ? 'Trắng / 512GB' : $variant['name'],
    'price' => $variant['price'] !== null ? (float)$variant['price'] : (float)$product['price'],
    'stock_quantity' => (int)$variant['stock_quantity'],
    'image_url' => $variant['image_url'] ?: null,
], $variants ?? []);
foreach ($variantData as $variant) {
    if (!empty($variant['image_url'])) {
        $gallery[] = $variant['image_url'];
    }
}
$gallery = array_values(array_unique(array_filter($gallery)));
$mainImage = $gallery[0] ?? 'https://placehold.co/500?text=No+Image';
$thumbImages = array_slice($gallery, 1);
$displayDescription = $product['description'] ?: 'Chưa có mô tả.';
if ((int)$product['id'] === 13 && str_contains((string)$product['description'], 'C?u')) {
    $displayDescription = "Cấu hình sản phẩm\n\n"
        . "Màn hình: 14 inch 2.8K OLED, 90Hz, 600 nit, HDR, 100% DCI-P3.\n"
        . "CPU: Intel Core Ultra 5 Processor 225H.\n"
        . "RAM: 16GB LPDDR5X.\n"
        . "Ổ cứng: 512GB M.2 NVMe PCIe 4.0 SSD.\n"
        . "Đồ họa: Intel Arc Graphics.\n"
        . "Hệ điều hành: Windows 11 tích hợp Copilot AI.";
}
?>

<div class="row g-4 align-items-start product-show-layout">
    <div class="col-lg-5">
        <div class="product-media-frame product-media-category-<?= e($product['category_id'] ?? 0) ?> mb-3">
            <img id="product-main-image"
                 src="<?= e($mainImage) ?>"
                 alt="<?= e($product['name']) ?>">
        </div>

        <?php if (!empty($thumbImages)): ?>
            <div class="product-gallery-thumbs">
                <button type="button"
                        class="btn p-0 border product-thumb active"
                        data-image="<?= e($mainImage) ?>"
                        data-default="1"
                        title="Ảnh chính">
                    <img src="<?= e($mainImage) ?>" alt="<?= e($product['name']) ?>">
                </button>
                <?php foreach ($thumbImages as $imageUrl): ?>
                    <button type="button"
                            class="btn p-0 border product-thumb"
                            data-image="<?= e($imageUrl) ?>"
                            title="Xem ảnh phụ">
                        <img src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-7 product-purchase-panel">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/products') ?>">Sản phẩm</a></li>
                <?php if (!empty($product['category_name']) && !empty($product['category_id'])): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= url('/products?cat=' . $product['category_id']) ?>"><?= e($product['category_name']) ?></a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"
                    style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($product['name']) ?>
                </li>
            </ol>
        </nav>

        <h1 class="h3 mb-2"><?= e($product['name']) ?></h1>

        <?php if ($product['category_name']): ?>
            <p class="text-muted mb-3">
                Danh mục: <span class="badge bg-secondary"><?= e($product['category_name']) ?></span>
            </p>
        <?php endif; ?>

        <p id="product-price" class="display-6 text-danger fw-bold mb-3">
            <?= format_vnd((float)$product['price']) ?>
        </p>

        <p class="mb-3">
            <span id="product-stock" class="badge <?= (int)$product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= (int)$product['stock_quantity'] > 0 ? 'Còn hàng (' . e($product['stock_quantity']) . ')' : 'Hết hàng' ?>
            </span>
        </p>

        <?php if (!empty($variantData)): ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">Chọn mẫu</label>
                <div class="d-flex flex-wrap gap-2" id="variant-options">
                    <?php foreach ($variantData as $variant): ?>
                        <button type="button"
                                class="btn btn-outline-primary variant-option"
                                data-id="<?= e($variant['id']) ?>"
                                data-name="<?= e($variant['name']) ?>"
                                data-price="<?= e($variant['price']) ?>"
                                data-stock="<?= e($variant['stock_quantity']) ?>"
                                data-image="<?= e($variant['image_url'] ?? '') ?>">
                            <?= e($variant['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <h6 class="text-muted">Mô tả:</h6>
            <p><?= nl2br(e($displayDescription)) ?></p>
        </div>

        <?php if ($product['status'] === 'active' && ((int)$product['stock_quantity'] > 0 || !empty($variantData))): ?>
            <form action="<?= url('/cart/add') ?>" method="post" class="d-flex gap-2 align-items-center flex-wrap">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">
                <input type="hidden" name="variant_id" id="variant_id" value="">

                <label class="me-2">Số lượng:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1"
                       max="<?= e($product['stock_quantity']) ?>"
                       class="form-control" style="width:100px">

                <button id="add-to-cart-button" class="btn btn-primary btn-lg" type="submit">
                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                </button>
            </form>
        <?php else: ?>
            <button class="btn btn-secondary btn-lg" disabled>Không còn hàng</button>
        <?php endif; ?>

        <?php $wishlistedNow = $wishlisted ?? false; ?>
        <button type="button"
                class="btn btn-outline-danger wishlist-btn mt-3 <?= $wishlistedNow ? 'active' : '' ?>"
                data-product-id="<?= e($product['id']) ?>"
                title="<?= $wishlistedNow ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
            <i class="bi <?= $wishlistedNow ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            <span class="wishlist-label"><?= $wishlistedNow ? 'Đã yêu thích' : 'Yêu thích' ?></span>
        </button>
        <button type="button"
                class="btn btn-outline-secondary compare-btn mt-2"
                data-compare-id="<?= e($product['id']) ?>"
                data-compare-name="<?= e($product['name']) ?>"
                data-compare-img="<?= e($product['image_url'] ?? '') ?>"
                title="So sánh">
            <i class="bi bi-columns-gap me-1"></i>
            <span class="compare-label">So sánh</span>
        </button>
    </div>
</div>

<?php if (!empty($related)): ?>
<section class="mt-5">
    <div class="section-heading">
        <div>
            <h2>Sản phẩm liên quan</h2>
            <p>Các sản phẩm khác trong cùng danh mục.</p>
        </div>
        <?php if (!empty($product['category_id'])): ?>
            <a href="<?= url('/products?cat=' . $product['category_id']) ?>" class="btn btn-outline-primary btn-sm">
                Xem thêm
            </a>
        <?php endif; ?>
    </div>
    <div class="row g-3">
        <?php foreach ($related as $r): ?>
            <?php $isWishlisted = in_array((int)$r['id'], $wishlistedIds ?? [], true); ?>
            <div class="col-6 col-md-4 col-xl-3">
                <article class="home-product-card">
                    <a href="<?= url('/products/' . $r['id']) ?>" class="home-product-media">
                        <img src="<?= e($r['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>"
                             alt="<?= e($r['name']) ?>">
                    </a>
                    <button type="button"
                            class="wishlist-btn wishlist-btn--card <?= $isWishlisted ? 'active' : '' ?>"
                            data-product-id="<?= e($r['id']) ?>"
                            title="<?= $isWishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
                        <i class="bi <?= $isWishlisted ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                    </button>
                    <div class="home-product-body">
                        <a href="<?= url('/products/' . $r['id']) ?>" class="home-product-name">
                            <?= e($r['name']) ?>
                        </a>
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

<?php
$reviewCount = count($reviews ?? []);
$averageRating = $reviewCount > 0
    ? array_sum(array_map(static fn($review) => (int)$review['rating'], $reviews)) / $reviewCount
    : 0;
?>

<section class="product-detail-info mt-5">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
        <div>
            <p class="text-primary fw-semibold mb-1">Thông tin sản phẩm</p>
            <h2 class="h4 mb-0">Mô tả chi tiết</h2>
        </div>
        <?php if (!empty($product['category_name'])): ?>
            <span class="badge rounded-pill text-bg-light product-detail-category">
                <?= e($product['category_name']) ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="product-description-panel">
        <div class="product-description-content">
            <?= nl2br(e($displayDescription)) ?>
        </div>
    </div>
</section>

<section class="mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">Đánh giá sản phẩm</h2>
            <?php if ($reviewCount > 0): ?>
                <div class="text-muted">
                    <?= e(number_format($averageRating, 1)) ?>/5 từ <?= e($reviewCount) ?> đánh giá
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($reviewableOrders)): ?>
            <a href="<?= url('/products/' . $product['id'] . '/review') ?>" class="btn btn-outline-primary">
                <i class="bi bi-star"></i> Viết đánh giá
            </a>
        <?php endif; ?>
    </div>

    <?php if ($reviewCount === 0): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-4">
                Chưa có đánh giá nào cho sản phẩm này.
            </div>
        </div>
    <?php else: ?>
        <div class="vstack gap-3">
            <?php foreach ($reviews as $review): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold"><?= e($review['customer_name']) ?></div>
                                <div class="text-warning" aria-label="<?= e($review['rating']) ?> sao">
                                    <?= str_repeat('&#9733;', (int)$review['rating']) ?>
                                    <span class="text-muted"><?= str_repeat('&#9734;', 5 - (int)$review['rating']) ?></span>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <?= e(date('d/m/Y', strtotime((string)$review['created_at']))) ?>
                            </div>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="mb-0 mt-3"><?= nl2br(e($review['comment'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<script nonce="<?= csp_nonce() ?>">
(() => {
    const defaultImage = <?= json_encode($mainImage, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const defaultPrice = <?= json_encode((float)$product['price']) ?>;
    const totalStock = <?= json_encode((int)$product['stock_quantity']) ?>;
    const mainImage = document.getElementById('product-main-image');
    const priceEl = document.getElementById('product-price');
    const stockEl = document.getElementById('product-stock');
    const variantInput = document.getElementById('variant_id');
    const quantityInput = document.getElementById('quantity');
    const addButton = document.getElementById('add-to-cart-button');
    const thumbs = Array.from(document.querySelectorAll('.product-thumb'));
    const variants = Array.from(document.querySelectorAll('.variant-option'));
    const formatVnd = value => new Intl.NumberFormat('vi-VN').format(Number(value || 0)) + 'đ';
    let selectedVariantImage = '';
    let pinnedGalleryImage = '';

    function setImage(imageUrl) {
        if (mainImage) {
            mainImage.src = imageUrl || defaultImage;
        }
    }

    function clearThumbs() {
        thumbs.forEach(item => item.classList.remove('active'));
    }

    function activateDefaultThumb() {
        clearThumbs();
        thumbs.find(item => item.dataset.default === '1')?.classList.add('active');
    }

    function updateStockLabel(value, isVariant) {
        if (!stockEl) {
            return;
        }

        const available = Number(value || 0);
        stockEl.className = 'badge ' + (available > 0 ? 'bg-success' : 'bg-danger');
        if (available <= 0) {
            stockEl.textContent = isVariant ? `Mẫu này hết hàng / Tổng ${totalStock}` : 'Hết hàng';
            return;
        }

        stockEl.textContent = isVariant
            ? `Mẫu còn ${available} / Tổng ${totalStock}`
            : `Còn hàng (${available})`;
    }

    function resetImageToDefault() {
        pinnedGalleryImage = '';
        selectedVariantImage = '';
        activateDefaultThumb();
        setImage(defaultImage);
    }

    thumbs.forEach(button => {
        button.addEventListener('mouseenter', () => {
            setImage(button.dataset.image || defaultImage);
        });

        button.addEventListener('mouseleave', () => {
            setImage(pinnedGalleryImage || selectedVariantImage || defaultImage);
        });

        button.addEventListener('click', () => {
            const imageUrl = button.dataset.image || defaultImage;
            if (button.dataset.default === '1' || pinnedGalleryImage === imageUrl) {
                resetImageToDefault();
                return;
            }

            pinnedGalleryImage = imageUrl;
            clearThumbs();
            button.classList.add('active');
            setImage(imageUrl);
        });
    });

    mainImage?.addEventListener('click', resetImageToDefault);

    variants.forEach(button => {
        button.addEventListener('click', () => {
            variants.forEach(item => item.classList.remove('active'));
            button.classList.add('active');

            const variantStock = Number(button.dataset.stock || 0);
            if (variantInput) {
                variantInput.value = button.dataset.id || '';
            }
            if (priceEl) {
                priceEl.textContent = formatVnd(button.dataset.price || defaultPrice);
            }
            if (quantityInput) {
                quantityInput.max = String(variantStock);
                quantityInput.value = variantStock > 0 ? '1' : '0';
            }
            if (addButton) {
                addButton.disabled = variantStock <= 0;
            }

            pinnedGalleryImage = '';
            selectedVariantImage = button.dataset.image || '';
            clearThumbs();
            updateStockLabel(variantStock, true);
            setImage(selectedVariantImage || defaultImage);
        });
    });

    if (variantInput) {
        variantInput.value = '';
    }
    if (priceEl) {
        priceEl.textContent = formatVnd(defaultPrice);
    }
    if (quantityInput) {
        quantityInput.max = String(totalStock);
        quantityInput.value = totalStock > 0 ? '1' : '0';
    }
    if (addButton) {
        addButton.disabled = totalStock <= 0;
    }
    updateStockLabel(totalStock, false);
    resetImageToDefault();
})();
</script>
