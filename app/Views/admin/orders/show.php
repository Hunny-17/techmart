<?php
/**
 * @var array $order
 * @var array $items
 * @var array $statuses
 * @var array $paymentStatuses
 * @var array $emailLogs
 */
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
$paymentStatusBadges = [
    'unpaid' => 'bg-secondary',
    'awaiting_review' => 'bg-warning text-dark',
    'paid' => 'bg-success',
    'refunded' => 'bg-info text-dark',
];
$statusHints = [
    'pending' => 'Đơn mới. Có thể xác nhận hoặc hủy nếu khách yêu cầu.',
    'confirmed' => 'Đơn đã xác nhận. Bước tiếp theo là chuyển sang đang giao.',
    'shipping' => 'Đơn đang giao. Bước tiếp theo là đánh dấu đã giao.',
    'delivered' => 'Đơn đã hoàn tất, không nên đổi trạng thái nữa.',
    'cancelled' => 'Đơn đã hủy, không nên đổi trạng thái nữa.',
];
$allowedNextStatuses = [
    'pending' => ['pending', 'confirmed', 'cancelled'],
    'confirmed' => ['confirmed', 'shipping', 'cancelled'],
    'shipping' => ['shipping', 'delivered', 'cancelled'],
    'delivered' => ['delivered'],
    'cancelled' => ['cancelled'],
];
$allowedStatuses = $allowedNextStatuses[$order['status']] ?? [$order['status']];
$isTerminalStatus = in_array($order['status'], ['delivered', 'cancelled'], true);
$isCodPayment = ($order['payment_method'] ?? '') === 'cod';
$codCanBePaid = !$isCodPayment || $order['status'] === 'delivered';
$paymentHint = $isCodPayment
    ? 'COD chỉ nên đánh dấu đã thanh toán sau khi đơn đã giao và shipper đã thu tiền.'
    : 'Dùng để đối soát chuyển khoản hoặc ví điện tử trước khi xử lý đơn.';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Đơn hàng #<?= e($order['id']) ?></h2>
        <div class="text-muted"><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
        <a href="<?= url('/admin/orders/' . $order['id'] . '/invoice') ?>" class="btn btn-outline-primary" target="_blank" rel="noopener">
            <i class="bi bi-printer me-1"></i> In hóa đơn
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Sản phẩm trong đơn</div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= e(($item['variant_image'] ?: $item['image_url']) ?: 'https://placehold.co/56') ?>"
                                             class="line-item-image" alt="<?= e($item['product_name']) ?>">
                                        <span>
                                            <?= e($item['product_name']) ?>
                                            <?php if (!empty($item['variant_name'])): ?>
                                                <span class="d-block small text-muted">Mẫu: <?= e($item['variant_name']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center"><?= e($item['quantity']) ?></td>
                                <td class="text-end"><?= format_vnd((float)$item['unit_price']) ?></td>
                                <td class="text-end">
                                    <?= format_vnd((float)$item['unit_price'] * (int)$item['quantity']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                            <?php $subtotal = (float)$order['total_amount'] + (float)$order['discount_amount']; ?>
                            <tr>
                                <td colspan="3" class="text-end text-muted small">Tạm tính</td>
                                <td class="text-end text-muted small"><?= format_vnd($subtotal) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-success small">
                                    <i class="bi bi-ticket-perforated me-1"></i>Voucher giảm giá
                                </td>
                                <td class="text-end text-success small fw-semibold">-<?= format_vnd((float)$order['discount_amount']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th colspan="3" class="text-end">Tổng cộng</th>
                            <th class="text-end"><?= format_vnd((float)$order['total_amount']) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Trạng thái</div>
            <div class="card-body">
                <p class="mb-3">
                    <span class="badge <?= e($statusBadges[$order['status']] ?? 'bg-secondary') ?>">
                        <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
                    </span>
                </p>
                <form action="<?= url('/admin/orders/' . $order['id'] . '/status') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="status">Cập nhật trạng thái</label>
                        <select name="status" id="status" class="form-select">
                            <?php foreach ($statuses as $item): ?>
                                <option value="<?= e($item) ?>"
                                    <?= $order['status'] === $item ? 'selected' : '' ?>
                                    <?= in_array($item, $allowedStatuses, true) ? '' : 'disabled' ?>>
                                    <?= e($statusLabels[$item] ?? $item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?= e($statusHints[$order['status']] ?? '') ?></div>
                    </div>
                    <button class="btn btn-primary w-100" <?= $isTerminalStatus ? 'disabled' : '' ?>>
                        <i class="bi bi-check2-circle"></i> Lưu trạng thái
                    </button>
                </form>

                <hr>

                <p class="mb-3">
                    <span class="badge <?= e($paymentStatusBadges[$order['payment_status'] ?? 'unpaid'] ?? 'bg-secondary') ?>">
                        <?= e($paymentStatusLabels[$order['payment_status'] ?? 'unpaid'] ?? ($order['payment_status'] ?? 'unpaid')) ?>
                    </span>
                </p>
                <form action="<?= url('/admin/orders/' . $order['id'] . '/payment-status') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="payment_status">Trạng thái thanh toán</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <?php foreach ($paymentStatuses as $item): ?>
                                <option value="<?= e($item) ?>"
                                    <?= ($order['payment_status'] ?? 'unpaid') === $item ? 'selected' : '' ?>
                                    <?= $item === 'paid' && !$codCanBePaid ? 'disabled' : '' ?>>
                                    <?= e($paymentStatusLabels[$item] ?? $item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text"><?= e($paymentHint) ?></div>
                    </div>
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-wallet2"></i> Lưu thanh toán
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Lịch sử email</div>
            <div class="card-body">
                <?php if (empty($emailLogs ?? [])): ?>
                    <div class="text-muted small">Chưa có email trạng thái nào được ghi nhận cho đơn này.</div>
                <?php else: ?>
                    <div class="vstack gap-3">
                        <?php foreach ($emailLogs as $log): ?>
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <span class="fw-semibold"><?= e($statusLabels[$log['status']] ?? $log['status']) ?></span>
                                    <span class="badge <?= $log['send_status'] === 'sent' ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= $log['send_status'] === 'sent' ? 'Đã tạo/gửi' : 'Lỗi' ?>
                                    </span>
                                </div>
                                <div class="small text-muted"><?= e(date('d/m/Y H:i', strtotime((string)$log['created_at']))) ?></div>
                                <div class="small mt-2">
                                    <div><span class="text-muted">Người nhận:</span> <?= e($log['recipient'] ?: '-') ?></div>
                                    <div><span class="text-muted">Tiêu đề:</span> <?= e($log['subject'] ?: '-') ?></div>
                                    <?php if (!empty($log['mail_file'])): ?>
                                        <div><span class="text-muted">File demo:</span> <?= e($log['mail_file']) ?></div>
                                        <a class="btn btn-sm btn-outline-primary mt-2"
                                           href="<?= url('/admin/orders/' . $order['id'] . '/emails/' . $log['id']) ?>"
                                           target="_blank" rel="noopener">
                                            <i class="bi bi-envelope-open me-1"></i> Xem email
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($log['error_message'])): ?>
                                        <div class="text-danger"><?= e($log['error_message']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Thông tin giao hàng</div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Khách hàng</dt>
                    <dd>
                        <a href="<?= url('/admin/customers/' . $order['user_id']) ?>">
                            <?= e($order['customer_name']) ?>
                        </a>
                    </dd>
                    <dt>Email</dt>
                    <dd><?= e($order['customer_email']) ?></dd>
                    <dt>Số điện thoại</dt>
                    <dd><?= e($order['customer_phone']) ?></dd>
                    <dt>Địa chỉ</dt>
                    <dd><?= nl2br(e($order['shipping_address'])) ?></dd>
                    <dt>Thanh toán</dt>
                    <dd><?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?></dd>
                    <?php if (!empty($order['payment_reference_code'])): ?>
                        <dt>Mã thanh toán</dt>
                        <dd><code><?= e($order['payment_reference_code']) ?></code></dd>
                    <?php endif; ?>
                    <?php if (!empty($order['note'])): ?>
                        <dt>Ghi chú</dt>
                        <dd><?= nl2br(e($order['note'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>
