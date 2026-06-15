<?php
/**
 * @var array $rows
 * @var array $filters
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
    'unpaid' => 'Chưa thanh toán',
    'awaiting_review' => 'Chờ đối soát',
    'paid' => 'Đã thanh toán',
    'refunded' => 'Đã hoàn tiền',
];
$query = array_filter($filters, static fn($value) => $value !== '' && $value !== null);
$ordersUrl = url('/admin/orders' . ($query ? '?' . http_build_query($query) : ''));
$csvUrl = url('/admin/orders/export' . ($query ? '?' . http_build_query($query) : ''));
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Bảng xuất đơn hàng</h2>
        <div class="text-muted">Xem trước dữ liệu theo bộ lọc hiện tại trước khi tải file CSV.</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= $ordersUrl ?>">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
        <a class="btn btn-success" href="<?= $csvUrl ?>" target="_blank" rel="noopener">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Tải CSV
        </a>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge text-bg-light border"><?= number_format(count($rows)) ?> đơn hàng</span>
    <?php if ($filters['q'] !== ''): ?>
        <span class="badge text-bg-light border">Từ khóa: <?= e($filters['q']) ?></span>
    <?php endif; ?>
    <?php if ($filters['status'] !== ''): ?>
        <span class="badge <?= e($statusBadges[$filters['status']] ?? 'bg-secondary') ?>">
            <?= e($statusLabels[$filters['status']] ?? $filters['status']) ?>
        </span>
    <?php endif; ?>
    <?php if ($filters['payment'] !== ''): ?>
        <span class="badge text-bg-light border"><?= e($paymentLabels[$filters['payment']] ?? $filters['payment']) ?></span>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th class="text-center">Dòng</th>
                    <th class="text-center">Tổng SL</th>
                    <th class="text-end">Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Mã thanh toán</th>
                    <th>Trạng thái thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Địa chỉ giao hàng</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">Không có đơn hàng phù hợp để xuất.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="fw-semibold">#<?= e($row['id']) ?></td>
                            <td><?= e($row['customer_name']) ?></td>
                            <td><?= e($row['customer_email']) ?></td>
                            <td><?= e($row['customer_phone'] ?: '-') ?></td>
                            <td class="text-center"><?= number_format((int)$row['item_count']) ?></td>
                            <td class="text-center"><?= number_format((int)$row['total_quantity']) ?></td>
                            <td class="text-end fw-semibold"><?= format_vnd((float)$row['total_amount']) ?></td>
                            <td><?= e($paymentLabels[$row['payment_method']] ?? $row['payment_method']) ?></td>
                            <td>
                                <?php if (!empty($row['payment_reference_code'])): ?>
                                    <code><?= e($row['payment_reference_code']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($paymentStatusLabels[$row['payment_status'] ?? 'unpaid'] ?? ($row['payment_status'] ?? 'unpaid')) ?></td>
                            <td>
                                <span class="badge <?= e($statusBadges[$row['status']] ?? 'bg-secondary') ?>">
                                    <?= e($statusLabels[$row['status']] ?? $row['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$row['created_at']))) ?></td>
                            <td class="text-break" style="min-width: 220px;"><?= e($row['shipping_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
