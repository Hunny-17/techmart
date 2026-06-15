<?php
/**
 * @var array       $result
 * @var string|null $status
 * @var array       $statusCounts
 */
$statusLabels = [
    'pending'   => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
$statusBadges = [
    'pending'   => 'bg-warning text-dark',
    'confirmed' => 'bg-info text-dark',
    'shipping'  => 'bg-primary',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-secondary',
];
$total = array_sum($statusCounts);

$tabUrl = static function (?string $s): string {
    $q = $s !== null ? '?status=' . $s : '';
    return url('/my-orders' . $q);
};
?>

<section class="page-hero orders-hero mb-4">
    <div>
        <span class="store-eyebrow">Tài khoản TechMart</span>
        <h1>Đơn hàng của tôi</h1>
        <p>Theo dõi trạng thái, xem chi tiết và đặt lại các đơn hàng đã mua trước đây.</p>
    </div>
    <div class="orders-hero-stats">
        <div>
            <strong><?= number_format($total) ?></strong>
            <span>tổng đơn hàng</span>
        </div>
        <?php if (($statusCounts['delivered'] ?? 0) > 0): ?>
            <div>
                <strong><?= number_format($statusCounts['delivered']) ?></strong>
                <span>đã giao thành công</span>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="orders-tab-bar mb-4">
    <ul class="nav orders-nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?= $status === null ? 'active' : '' ?>" href="<?= $tabUrl(null) ?>">
                Tất cả
                <?php if ($total > 0): ?>
                    <span class="badge text-bg-secondary ms-1"><?= number_format($total) ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php foreach ($statusLabels as $key => $label): ?>
            <?php $count = $statusCounts[$key] ?? 0; ?>
            <?php if ($count > 0 || $status === $key): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $status === $key ? 'active' : '' ?>" href="<?= $tabUrl($key) ?>">
                        <?= e($label) ?>
                        <?php if ($count > 0): ?>
                            <span class="badge ms-1 <?= $status === $key ? 'text-bg-primary' : 'text-bg-secondary' ?>">
                                <?= number_format($count) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <a href="<?= url('/products') ?>" class="btn btn-outline-primary btn-sm flex-shrink-0">
        <i class="bi bi-bag me-1"></i> Tiếp tục mua sắm
    </a>
</div>

<?php if (empty($result['rows'])): ?>
    <div class="page-empty-state orders-empty-state">
        <i class="bi bi-receipt"></i>
        <h2><?= $status !== null ? 'Không có đơn hàng nào' : 'Chưa có đơn hàng nào' ?></h2>
        <p>
            <?php if ($status !== null): ?>
                Không có đơn nào ở trạng thái "<?= e($statusLabels[$status] ?? $status) ?>".
                Thử xem tất cả đơn hoặc bắt đầu mua sắm mới.
            <?php else: ?>
                Bạn chưa đặt đơn hàng nào. Khám phá sản phẩm và đặt đơn đầu tiên ngay hôm nay.
            <?php endif; ?>
        </p>
        <?php if ($status !== null): ?>
            <a href="<?= url('/my-orders') ?>" class="btn btn-outline-primary">Xem tất cả đơn</a>
        <?php endif; ?>
        <a href="<?= url('/products') ?>" class="btn btn-primary">
            Mua sắm ngay <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
<?php else: ?>
    <section class="panel-card">
        <div class="panel-header">
            <div>
                <h2>Danh sách đơn hàng</h2>
                <p>
                    <?= number_format(count($result['rows'])) ?> đơn
                    · trang <?= number_format($result['page'] ?? 1) ?>/<?= number_format($result['lastPage'] ?? 1) ?>
                </p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle my-orders-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-center">Trạng thái</th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $order): ?>
                        <tr>
                            <td class="fw-semibold">#<?= e($order['id']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                            <td class="text-end">
                                <?= format_vnd((float)$order['total_amount']) ?>
                                <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                                    <div class="small text-success">
                                        <i class="bi bi-tag-fill"></i> -<?= format_vnd((float)$order['discount_amount']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= e($statusBadges[$order['status']] ?? 'bg-secondary') ?>">
                                    <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/my-orders/' . $order['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php
    $pageUrlFn = static fn(int $p): string =>
        url('/my-orders?' . http_build_query(array_filter(['status' => $status, 'page' => $p])));
    require __DIR__ . '/../partials/pagination.php';
    ?>
<?php endif; ?>
