<?php
/**
 * @var array $result
 * @var array $filters
 * @var array $statusCounts
 * @var array $statuses
 * @var array $paymentMethods
 */
$filters = $filters ?? ['q' => '', 'status' => '', 'payment' => ''];
$statusLabels = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];
$statusBadges = [
    'pending' => 'bg-warning text-dark',
    'confirmed' => 'bg-info text-dark',
    'shipping' => 'bg-primary',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-secondary',
];
$paymentLabels = [
    'cod' => 'COD',
    'bank_transfer' => 'Chuyển khoản',
    'e_wallet' => 'Ví điện tử',
];
$paymentStatusLabels = [
    'unpaid' => 'Chưa TT',
    'awaiting_review' => 'Chờ đối soát',
    'paid' => 'Đã TT',
    'refunded' => 'Hoàn tiền',
];
$paymentStatusBadges = [
    'unpaid' => 'text-bg-secondary',
    'awaiting_review' => 'text-bg-warning',
    'paid' => 'text-bg-success',
    'refunded' => 'text-bg-info',
];

$buildUrl = static function (array $overrides = []) use ($filters): string {
    $query = array_merge($filters, $overrides);
    $query = array_filter($query, static fn($value) => $value !== '' && $value !== null);

    return url('/admin/orders' . ($query ? '?' . http_build_query($query) : ''));
};

$exportUrl = static function (array $overrides = []) use ($filters): string {
    $query = array_merge($filters, $overrides);
    $query = array_filter($query, static fn($value) => $value !== '' && $value !== null);

    return url('/admin/orders/export' . ($query ? '?' . http_build_query($query) : ''));
};

$exportPreviewUrl = static function () use ($filters): string {
    $query = array_filter($filters, static fn($value) => $value !== '' && $value !== null);

    return url('/admin/orders/export-preview' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý đơn hàng</h2>
        <div class="text-muted">Tìm kiếm, lọc và xử lý các đơn hàng mới nhất.</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge text-bg-light border"><?= number_format((int)$result['total']) ?> đơn hàng</span>
        <a class="btn btn-outline-success" href="<?= $exportUrl() ?>" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Xuất CSV
        </a>
        <a class="btn btn-outline-secondary" href="<?= $exportPreviewUrl() ?>">
            <i class="bi bi-table me-1"></i> Xem bảng
        </a>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= $buildUrl(['status' => '', 'page' => null]) ?>"
       class="btn btn-sm <?= $filters['status'] === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
        Tất cả
    </a>
    <?php foreach ($statuses as $item): ?>
        <a href="<?= $buildUrl(['status' => $item, 'page' => null]) ?>"
           class="btn btn-sm <?= $filters['status'] === $item ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <span class="badge <?= e($statusBadges[$item] ?? 'bg-secondary') ?> me-1"><?= number_format((int)($statusCounts[$item] ?? 0)) ?></span>
            <?= e($statusLabels[$item] ?? $item) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/orders') ?>">
            <div class="col-md-5">
                <label class="form-label">Tìm đơn hàng</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Mã đơn, khách hàng, email hoặc địa chỉ">
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
                <label class="form-label">Thanh toán</label>
                <select name="payment" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($paymentMethods as $item): ?>
                        <option value="<?= e($item) ?>" <?= $filters['payment'] === $item ? 'selected' : '' ?>>
                            <?= e($paymentLabels[$item] ?? $item) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/orders') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle admin-orders-table">
            <thead class="table-light">
                <tr>
                    <th>Đơn hàng</th>
                    <th>Khách hàng</th>
                    <th class="text-center">Sản phẩm</th>
                    <th class="text-end">Tổng tiền</th>
                    <th class="text-center">Thanh toán</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th style="width:90px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có đơn hàng phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $order): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold">#<?= e($order['id']) ?></div>
                                <div class="small text-muted"><?= e(date('d/m/Y', strtotime((string)$order['created_at']))) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
                                <div class="small text-muted"><?= e($order['customer_email']) ?></div>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold"><?= number_format((int)$order['total_quantity']) ?></div>
                                <div class="small text-muted"><?= number_format((int)$order['item_count']) ?> dòng</div>
                            </td>
                            <td class="text-end fw-semibold">
                                <?= format_vnd((float)$order['total_amount']) ?>
                                <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                                    <div class="small text-success">
                                        <i class="bi bi-ticket-perforated"></i> -<?= format_vnd((float)$order['discount_amount']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-light border"><?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?></span>
                                <div class="mt-1">
                                    <span class="badge <?= e($paymentStatusBadges[$order['payment_status'] ?? 'unpaid'] ?? 'text-bg-secondary') ?>">
                                        <?= e($paymentStatusLabels[$order['payment_status'] ?? 'unpaid'] ?? ($order['payment_status'] ?? 'unpaid')) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= e($statusBadges[$order['status']] ?? 'bg-secondary') ?>">
                                    <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/admin/orders/' . $order['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pageUrlFn = fn($p) => $buildUrl(['page' => $p]); require __DIR__ . '/../../partials/pagination.php'; ?>
