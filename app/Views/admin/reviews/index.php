<?php
/**
 * @var array $result
 * @var array $filters
 * @var array $statuses
 */
$filters = $filters ?? ['q' => '', 'status' => '', 'rating' => ''];
$statusLabels = [
    'visible' => 'Đang hiển thị',
    'hidden' => 'Đã ẩn',
];
$statusBadges = [
    'visible' => 'bg-success',
    'hidden' => 'bg-secondary',
];

$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter([
        'q' => $filters['q'] ?? '',
        'status' => $filters['status'] ?? '',
        'rating' => $filters['rating'] ?? '',
        'page' => $page,
    ], static fn($value) => $value !== '' && $value !== null);

    return url('/admin/reviews' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý đánh giá</h2>
        <div class="text-muted">Lọc, ẩn hoặc xóa các đánh giá không phù hợp. Đánh giá đang hiển thị sẽ được ẩn trước khi xóa vĩnh viễn.</div>
    </div>
    <span class="badge text-bg-light border"><?= number_format((int)$result['total']) ?> đánh giá</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/reviews') ?>">
            <div class="col-md-5">
                <label class="form-label">Tìm đánh giá</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Sản phẩm, khách hàng, email hoặc nội dung">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($statuses as $item): ?>
                        <option value="<?= e($item) ?>" <?= $filters['status'] === $item ? 'selected' : '' ?>>
                            <?= e($statusLabels[$item] ?? $item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Số sao</label>
                <select name="rating" class="form-select">
                    <option value="">Tất cả</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= $filters['rating'] === (string)$i ? 'selected' : '' ?>><?= $i ?> sao</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/reviews') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle admin-reviews-table">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Khách hàng</th>
                    <th class="text-center" style="width:120px">Sao</th>
                    <th>Nội dung</th>
                    <th class="text-center" style="width:130px">Trạng thái</th>
                    <th style="width:150px">Ngày</th>
                    <th style="width:110px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đánh giá phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $review): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/products/' . $review['product_id']) ?>"
                                   class="fw-semibold text-decoration-none" target="_blank">
                                    <?= e($review['product_name']) ?>
                                </a>
                                <div class="text-muted small">#<?= e($review['id']) ?> · Đơn #<?= e($review['order_id']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($review['customer_name']) ?></div>
                                <div class="small text-muted"><?= e($review['customer_email']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="review-stars"><?= str_repeat('★', (int)$review['rating']) ?></span>
                                <span class="review-stars-muted"><?= str_repeat('☆', 5 - (int)$review['rating']) ?></span>
                            </td>
                            <td class="review-comment">
                                <?= nl2br(e($review['comment'] ?: 'Không có nội dung.')) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= e($statusBadges[$review['status']] ?? 'bg-secondary') ?>">
                                    <?= e($statusLabels[$review['status']] ?? $review['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$review['created_at']))) ?></td>
                            <td class="text-end">
                                <?php if ($review['status'] === 'visible'): ?>
                                    <form action="<?= url('/admin/reviews/' . $review['id'] . '/hide') ?>"
                                          method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-secondary" title="Ẩn">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/reviews/' . $review['id'] . '/show') ?>"
                                          method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-success" title="Hiển thị">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="<?= url('/admin/reviews/' . $review['id'] . '/delete') ?>"
                                      method="post" class="d-inline"
                                      data-confirm="<?= $review['status'] === 'visible' ? 'Đánh giá đang hiển thị sẽ được ẩn trước thay vì xóa. Tiếp tục?' : 'Xóa vĩnh viễn đánh giá đã ẩn này?' ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" title="<?= $review['status'] === 'visible' ? 'Ẩn trước khi xóa' : 'Xóa vĩnh viễn' ?>">
                                        <i class="bi bi-trash"></i>
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
