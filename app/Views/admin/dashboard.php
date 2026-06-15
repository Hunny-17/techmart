<?php
/**
 * @var array $stats
 * @var array $orderStatusCounts
 * @var array $paymentStatusCounts
 * @var array $revenueLast7Days
 * @var array $ordersLast7Days
 * @var array $topProducts
 * @var array $lowStockItems
 */
$statusLabels = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];

$statusClasses = [
    'pending' => 'warning',
    'confirmed' => 'primary',
    'shipping' => 'info',
    'delivered' => 'success',
    'cancelled' => 'secondary',
];

$awaitingPayment = (int)($paymentStatusCounts['awaiting_review'] ?? 0);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Dashboard</h2>
        <div class="text-muted">Tổng quan nhanh để xử lý đơn, kho và sản phẩm.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="<?= url('/admin/orders?status=pending') ?>">
            <i class="bi bi-receipt me-1"></i> Đơn chờ xử lý
        </a>
        <a class="btn btn-outline-primary" href="<?= url('/admin/products/create') ?>">
            <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Doanh thu hôm nay</div>
                <div class="fs-4 fw-bold text-success"><?= format_vnd((float)$stats['revenue_today']) ?></div>
                <div class="small text-muted">Tính đơn đã giao trong ngày</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Doanh thu tháng này</div>
                <div class="fs-4 fw-bold text-success"><?= format_vnd((float)$stats['revenue_month']) ?></div>
                <div class="small text-muted">Tính đơn đã giao trong tháng</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Khách mới hôm nay</div>
                <div class="fs-4 fw-bold"><?= number_format((int)$stats['new_customers_today']) ?></div>
                <div class="small text-muted"><?= number_format((int)$stats['new_customers_month']) ?> khách mới trong tháng</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Thanh toán chờ đối soát</div>
                <div class="fs-4 fw-bold text-warning"><?= number_format($awaitingPayment) ?></div>
                <div class="small text-muted"><?= number_format((int)$stats['unverified_customers']) ?> khách chưa xác thực email</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Voucher đang hoạt động</div>
                <div class="fs-4 fw-bold text-primary">
                    <?= number_format((int)($voucherStats['active_count'] ?? 0)) ?>
                    <small class="fs-6 text-muted fw-normal">/ <?= number_format((int)($voucherStats['total_count'] ?? 0)) ?></small>
                </div>
                <div class="small text-muted">Đã dùng <?= number_format((int)($voucherStats['total_used'] ?? 0)) ?> lần · <a href="<?= url('/admin/vouchers') ?>">Quản lý</a></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng giảm giá đã phát</div>
                <div class="fs-4 fw-bold text-danger"><?= format_vnd((float)($totalDiscountGiven ?? 0)) ?></div>
                <div class="small text-muted">Tính trên tất cả đơn hàng có dùng voucher</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-primary border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Sản phẩm</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($stats['products']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-primary"><i class="bi bi-box-seam"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-success border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Khách hàng</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($stats['customers']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-success"><i class="bi bi-people"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-warning border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Nhân viên</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($stats['staffs']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-warning"><i class="bi bi-person-badge"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-danger border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Đơn hàng</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($stats['orders']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-danger"><i class="bi bi-bag-check"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-info border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Chờ xử lý</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($stats['pending_orders']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-info"><i class="bi bi-hourglass-split"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-stat-card shadow-sm border-start border-dark border-4 h-100">
            <div class="card-body">
                <div>
                    <h6 class="text-muted">Doanh thu</h6>
                    <h2 class="fw-bold mb-0 fs-4"><?= format_vnd((float)$stats['revenue']) ?></h2>
                </div>
                <span class="dashboard-stat-icon text-dark"><i class="bi bi-cash-stack"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">Cần chú ý</h5>
                        <div class="text-muted">Các mục nên kiểm tra trước trong ngày.</div>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/orders') ?>">Xem tất cả</a>
                </div>
                <div class="dashboard-action-grid">
                    <a class="dashboard-action text-decoration-none" href="<?= url('/admin/orders?status=pending') ?>">
                        <span class="dashboard-action-icon text-warning"><i class="bi bi-clock-history"></i></span>
                        <span>
                            <strong><?= number_format($stats['pending_orders']) ?> đơn chờ xử lý</strong>
                            <small>Cần xác nhận trước khi giao</small>
                        </span>
                    </a>
                    <a class="dashboard-action text-decoration-none" href="<?= url('/admin/orders?status=shipping') ?>">
                        <span class="dashboard-action-icon text-info"><i class="bi bi-truck"></i></span>
                        <span>
                            <strong><?= number_format($stats['shipping_orders']) ?> đơn đang giao</strong>
                            <small>Theo dõi để cập nhật hoàn tất</small>
                        </span>
                    </a>
                    <a class="dashboard-action text-decoration-none" href="<?= url('/admin/products') ?>">
                        <span class="dashboard-action-icon text-danger"><i class="bi bi-exclamation-triangle"></i></span>
                        <span>
                            <strong><?= number_format($stats['low_stock']) ?> mục tồn kho thấp</strong>
                            <small>Tính cả sản phẩm chính và mẫu</small>
                        </span>
                    </a>
                    <a class="dashboard-action text-decoration-none" href="<?= url('/admin/orders') ?>">
                        <span class="dashboard-action-icon text-warning"><i class="bi bi-credit-card"></i></span>
                        <span>
                            <strong><?= number_format($awaitingPayment) ?> thanh toán chờ đối soát</strong>
                            <small>Ưu tiên kiểm tra đơn chuyển khoản và ví điện tử</small>
                        </span>
                    </a>
                    <a class="dashboard-action text-decoration-none" href="<?= url('/admin/customers') ?>">
                        <span class="dashboard-action-icon text-secondary"><i class="bi bi-envelope-exclamation"></i></span>
                        <span>
                            <strong><?= number_format((int)$stats['unverified_customers']) ?> khách chưa xác thực</strong>
                            <small>Có thể gửi lại email xác thực trong quản lý khách hàng</small>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5 class="mb-3">Trạng thái đơn</h5>
                <div class="dashboard-status-list">
                    <?php foreach ($statusLabels as $status => $label): ?>
                        <?php $count = (int)($orderStatusCounts[$status] ?? 0); ?>
                        <a class="dashboard-status-row text-decoration-none" href="<?= url('/admin/orders?status=' . $status) ?>">
                            <span class="badge text-bg-<?= $statusClasses[$status] ?>"><?= $label ?></span>
                            <strong><?= number_format($count) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Doanh thu 7 ngày gần nhất</div>
            <div class="card-body">
                <canvas id="revenueChart" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Top sản phẩm bán chạy</div>
            <div class="card-body">
                <canvas id="topProductsChart" height="190"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Số đơn 7 ngày gần nhất</div>
            <div class="card-body">
                <canvas id="ordersChart" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Tồn kho thấp</span>
                <a class="btn btn-sm btn-outline-danger" href="<?= url('/admin/products?low_stock=1') ?>">Xem kho</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockItems ?? [])): ?>
                    <div class="p-3 text-muted small">Chưa có sản phẩm hoặc mẫu nào dưới ngưỡng tồn kho.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <tbody>
                                <?php foreach ($lowStockItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($item['product_name']) ?></div>
                                            <?php if (!empty($item['variant_name'])): ?>
                                                <div class="small text-muted">Mẫu: <?= e($item['variant_name']) ?></div>
                                            <?php else: ?>
                                                <div class="small text-muted">Sản phẩm chính</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge <?= (int)$item['stock_quantity'] <= 0 ? 'text-bg-danger' : 'text-bg-warning' ?>">
                                                <?= number_format((int)$item['stock_quantity']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Lối tắt quản trị</h5>
        <div class="dashboard-shortcuts">
            <a class="btn btn-outline-primary" href="<?= url('/admin/products') ?>"><i class="bi bi-box-seam me-1"></i> Sản phẩm</a>
            <a class="btn btn-outline-primary" href="<?= url('/admin/orders') ?>"><i class="bi bi-receipt me-1"></i> Đơn hàng</a>
            <a class="btn btn-outline-primary" href="<?= url('/admin/customers') ?>"><i class="bi bi-people me-1"></i> Khách hàng</a>
            <a class="btn btn-outline-primary" href="<?= url('/admin/reviews') ?>"><i class="bi bi-star me-1"></i> Đánh giá</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js" nonce="<?= csp_nonce() ?>"></script>
<script nonce="<?= csp_nonce() ?>">
const revenueLast7Days = <?= json_encode($revenueLast7Days, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const ordersLast7Days = <?= json_encode($ordersLast7Days, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const topProducts = <?= json_encode($topProducts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueLast7Days.map(item => item.date),
        datasets: [{
            label: 'Doanh thu',
            data: revenueLast7Days.map(item => item.amount),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.12)',
            fill: true,
            tension: 0.35
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => new Intl.NumberFormat('vi-VN').format(value) + 'đ'
                }
            }
        }
    }
});

new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topProducts.map(item => item.name),
        datasets: [{
            label: 'Số lượng',
            data: topProducts.map(item => item.quantity),
            backgroundColor: '#198754'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

new Chart(document.getElementById('ordersChart'), {
    type: 'bar',
    data: {
        labels: ordersLast7Days.map(item => item.date),
        datasets: [{
            label: 'Số đơn',
            data: ordersLast7Days.map(item => item.total),
            backgroundColor: '#0dcaf0'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>
