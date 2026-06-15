<?php
/** @var array $result */ /** @var string|null $keyword */ /** @var array $categories */
/** @var int|null $categoryId */ /** @var string $sort */
/** @var float|null $minPrice */ /** @var float|null $maxPrice */
$sort ??= 'newest';
$sortLabels = [
    'newest'     => 'Mới nhất',
    'price_asc'  => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
    'name_asc'   => 'Tên A-Z',
];
$totalProducts = (int)($result['total'] ?? 0);
$currentPage = (int)($result['page'] ?? 1);
$lastPage = (int)($result['lastPage'] ?? 1);
?>
<section class="page-hero catalog-hero mb-4">
    <div>
        <span class="store-eyebrow">TechMart Catalog</span>
        <h1>Sản phẩm công nghệ</h1>
        <p>Tìm nhanh laptop, điện thoại và phụ kiện theo danh mục, khoảng giá và cách sắp xếp phù hợp với nhu cầu mua sắm.</p>
    </div>
    <div class="catalog-hero-stats">
        <div>
            <strong><?= number_format($totalProducts) ?></strong>
            <span>sản phẩm phù hợp</span>
        </div>
        <div>
            <strong><?= number_format(count($categories)) ?></strong>
            <span>danh mục</span>
        </div>
    </div>
</section>

<form class="catalog-filter-panel mb-4" method="get">
    <div class="catalog-filter-grid">
        <div class="catalog-filter-search">
            <label class="form-label" for="catalog-q">Từ khóa</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input id="catalog-q" class="form-control" name="q" placeholder="Tìm theo tên sản phẩm..."
                       value="<?= e($keyword ?? '') ?>">
            </div>
        </div>
        <div>
            <label class="form-label" for="catalog-cat">Danh mục</label>
            <select id="catalog-cat" class="form-select" name="cat">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= (int)($categoryId ?? 0) === (int)$category['id'] ? 'selected' : '' ?>>
                        <?= e($category['parent_name'] ? $category['parent_name'] . ' / ' . $category['name'] : $category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="catalog-sort">Sắp xếp</label>
            <select id="catalog-sort" class="form-select" name="sort">
                <?php foreach ($sortLabels as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $sort === $val ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label" for="catalog-min-price">Giá từ</label>
            <input id="catalog-min-price" type="number" class="form-control" name="min_price"
                   placeholder="0" min="0" step="10000"
                   value="<?= e($minPrice !== null ? (int)$minPrice : '') ?>">
        </div>
        <div>
            <label class="form-label" for="catalog-max-price">Giá đến</label>
            <input id="catalog-max-price" type="number" class="form-control" name="max_price"
                   placeholder="Không giới hạn" min="0" step="10000"
                   value="<?= e($maxPrice !== null ? (int)$maxPrice : '') ?>">
        </div>
        <div class="catalog-filter-actions">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a class="btn btn-outline-secondary" href="<?= url('/products') ?>">Xóa</a>
        </div>
    </div>
</form>

<?php
$_removeParam = static function (string $param): string {
    $params = $_GET;
    unset($params[$param], $params['page']);
    return url('/products' . ($params ? '?' . http_build_query($params) : ''));
};
$activeChips = [];
if ($keyword !== null && $keyword !== '') {
    $activeChips[] = ['label' => 'Từ khóa: "' . $keyword . '"', 'url' => $_removeParam('q')];
}
if ($categoryId !== null) {
    $catLabel = '';
    foreach ($categories as $_c) {
        if ((int)$_c['id'] === $categoryId) {
            $catLabel = $_c['parent_name'] ? $_c['parent_name'] . ' / ' . $_c['name'] : $_c['name'];
            break;
        }
    }
    if ($catLabel !== '') {
        $activeChips[] = ['label' => 'Danh mục: ' . $catLabel, 'url' => $_removeParam('cat')];
    }
}
if ($sort !== 'newest') {
    $activeChips[] = ['label' => $sortLabels[$sort], 'url' => $_removeParam('sort')];
}
if ($minPrice !== null) {
    $activeChips[] = ['label' => 'Từ ' . number_format((int)$minPrice, 0, ',', '.') . 'đ', 'url' => $_removeParam('min_price')];
}
if ($maxPrice !== null) {
    $activeChips[] = ['label' => 'Đến ' . number_format((int)$maxPrice, 0, ',', '.') . 'đ', 'url' => $_removeParam('max_price')];
}
?>

<?php if (!empty($activeChips)): ?>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="text-muted small">Đang lọc:</span>
        <?php foreach ($activeChips as $chip): ?>
            <a href="<?= e($chip['url']) ?>" class="badge text-bg-light border text-dark text-decoration-none product-filter-chip">
                <?= e($chip['label']) ?> &times;
            </a>
        <?php endforeach; ?>
        <a href="<?= url('/products') ?>" class="small text-muted ms-1">Xóa tất cả</a>
    </div>
<?php endif; ?>

<div class="catalog-result-bar mb-3">
    <div>
        <strong><?= number_format($totalProducts) ?></strong>
        <span class="text-muted">sản phẩm được tìm thấy</span>
    </div>
    <span class="text-muted small">Trang <?= number_format($currentPage) ?> / <?= number_format($lastPage) ?></span>
</div>

<?php if (empty($result['rows'])): ?>
    <div class="page-empty-state catalog-empty-state">
        <i class="bi bi-search"></i>
        <h2>Không có sản phẩm phù hợp</h2>
        <p>Thử đổi từ khóa, bỏ bớt bộ lọc hoặc mở rộng khoảng giá để xem thêm lựa chọn.</p>
        <a class="btn btn-primary" href="<?= url('/products') ?>">Xem tất cả sản phẩm</a>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($result['rows'] as $p): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <?php $isWishlisted = in_array((int)$p['id'], $wishlistedIds ?? [], true); ?>
                <article class="home-product-card catalog-product-card">
                    <a href="<?= url('/products/' . $p['id']) ?>" class="home-product-media">
                        <img src="<?= e($p['image_url'] ?: 'https://placehold.co/360x280?text=No+Image') ?>"
                             alt="<?= e($p['name']) ?>">
                        <?php if ((int)$p['stock_quantity'] <= 0): ?>
                            <span class="product-card-sold-out">Hết hàng</span>
                        <?php endif; ?>
                    </a>
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
                        <a href="<?= url('/products/' . $p['id']) ?>"
                           class="btn btn-sm btn-outline-primary w-100">Xem chi tiết</a>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary compare-btn w-100 mt-1"
                                data-compare-id="<?= e($p['id']) ?>"
                                data-compare-name="<?= e($p['name']) ?>"
                                data-compare-img="<?= e($p['image_url'] ?? '') ?>"
                                title="So sánh">
                            <i class="bi bi-columns-gap me-1"></i> So sánh
                        </button>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>
