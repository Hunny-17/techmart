<?php
/**
 * @var array $order
 * @var array $items
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
$paymentLabels = [
    'cod'           => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'e_wallet'      => 'Ví điện tử',
];
$paymentStatusLabels = [
    'unpaid'          => 'Chưa thanh toán',
    'awaiting_review' => 'Chờ đối soát',
    'paid'            => 'Đã thanh toán',
    'refunded'        => 'Đã hoàn tiền',
];
$paymentConfig      = \App\Core\App::$config['payment'] ?? [];
$paymentQrUrl       = '';
$paymentReceiverText = '';
if (!empty($order['payment_reference_code']) && $order['payment_method'] === 'bank_transfer') {
    $paymentReceiverText = trim(($paymentConfig['bank_name'] ?? '') . ' · STK: ' . ($paymentConfig['bank_account_no'] ?? '') . ' · Chủ TK: ' . ($paymentConfig['bank_account_name'] ?? ''));
    if (!empty($paymentConfig['bank_id']) && !empty($paymentConfig['bank_account_no'])) {
        $paymentQrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            rawurlencode((string)$paymentConfig['bank_id']),
            rawurlencode((string)$paymentConfig['bank_account_no']),
            (int)round((float)$order['total_amount']),
            rawurlencode((string)$order['payment_reference_code']),
            rawurlencode((string)$paymentConfig['bank_account_name'])
        );
    }
} elseif (!empty($order['payment_reference_code']) && $order['payment_method'] === 'e_wallet') {
    $paymentReceiverText = trim(($paymentConfig['wallet_name'] ?? '') . ' · Tài khoản ví: ' . ($paymentConfig['wallet_account'] ?? ''));
    $paymentQrUrl        = (string)($paymentConfig['wallet_qr_url'] ?? '');
}
$canCancel = $order['status'] === 'pending';
?>

<section class="page-hero orders-hero mb-4">
    <div>
        <span class="store-eyebrow">Đơn hàng #<?= e($order['id']) ?></span>
        <h1><?= e($statusLabels[$order['status']] ?? $order['status']) ?></h1>
        <p>
            Đặt lúc <?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?>
            · <?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?>
            · <?= format_vnd((float)$order['total_amount']) ?>
        </p>
    </div>
    <div class="orders-show-hero-right">
        <span class="badge orders-status-badge <?= e($statusBadges[$order['status']] ?? 'bg-secondary') ?>">
            <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
        </span>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($canCancel): ?>
                <form action="<?= url('/my-orders/' . $order['id'] . '/cancel') ?>"
                      method="post"
                      data-confirm="Hủy đơn hàng này? Tồn kho sẽ được hoàn lại.">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i> Hủy đơn
                    </button>
                </form>
            <?php endif; ?>
            <form action="<?= url('/my-orders/' . $order['id'] . '/reorder') ?>"
                  method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-repeat me-1"></i> Đặt lại
                </button>
            </form>
            <a href="<?= url('/my-orders') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>
</section>

<?php if ($canCancel): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-clock me-1"></i>
        Đơn hàng đang chờ xử lý. Bạn có thể hủy đơn trước khi shop xác nhận.
    </div>
<?php endif; ?>

<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <h2>Sản phẩm trong đơn</h2>
            <p><?= number_format(count($items)) ?> sản phẩm</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle my-order-detail-table">
            <thead>
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
                                     class="line-item-image line-item-image-sm" alt="<?= e($item['product_name']) ?>">
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
                        <td colspan="3" class="text-end text-muted">Tạm tính</td>
                        <td class="text-end text-muted"><?= format_vnd($subtotal) ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end text-success">
                            <i class="bi bi-ticket-perforated me-1"></i>Giảm giá (voucher)
                        </td>
                        <td class="text-end text-success fw-semibold">-<?= format_vnd((float)$order['discount_amount']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th colspan="3" class="text-end">Tổng cộng</th>
                    <th class="text-end text-danger"><?= format_vnd((float)$order['total_amount']) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel-card h-100">
            <div class="panel-header">
                <div>
                    <h2>Thông tin giao hàng</h2>
                    <p>Địa chỉ nhận hàng và ghi chú đơn.</p>
                </div>
                <i class="bi bi-truck"></i>
            </div>
            <div class="order-info-body">
                <div class="order-info-row">
                    <span class="order-info-label">Địa chỉ</span>
                    <span class="order-info-value"><?= nl2br(e($order['shipping_address'])) ?></span>
                </div>
                <?php if (!empty($order['note'])): ?>
                    <div class="order-info-row">
                        <span class="order-info-label">Ghi chú</span>
                        <span class="order-info-value"><?= nl2br(e($order['note'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel-card h-100">
            <div class="panel-header">
                <div>
                    <h2>Thanh toán</h2>
                    <p>Phương thức và trạng thái thanh toán.</p>
                </div>
                <i class="bi bi-credit-card"></i>
            </div>
            <div class="order-info-body">
                <div class="order-info-row">
                    <span class="order-info-label">Phương thức</span>
                    <span class="order-info-value">
                        <?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?>
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Trạng thái TT</span>
                    <span class="order-info-value">
                        <?= e($paymentStatusLabels[$order['payment_status'] ?? 'unpaid'] ?? ($order['payment_status'] ?? 'unpaid')) ?>
                    </span>
                </div>
                <?php if (!empty($order['payment_reference_code'])): ?>
                    <div class="order-info-row">
                        <span class="order-info-label">Mã TT</span>
                        <span class="order-info-value">
                            <code><?= e($order['payment_reference_code']) ?></code>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($paymentReceiverText !== '' && ($order['payment_status'] ?? '') !== 'paid'): ?>
                    <div class="order-payment-panel mt-3">
                        <div class="fw-semibold mb-2 small">Thông tin chuyển tiền</div>
                        <?php if ($paymentQrUrl !== ''): ?>
                            <img src="<?= e($paymentQrUrl) ?>"
                                 class="img-fluid border rounded bg-white p-2 mb-2 d-block"
                                 style="max-width: 200px" alt="QR thanh toán">
                        <?php endif; ?>
                        <div class="small text-muted mb-1"><?= e($paymentReceiverText) ?></div>
                        <div class="small text-muted mb-1">
                            Nội dung: <code><?= e($order['payment_reference_code']) ?></code>
                        </div>
                        <div class="small">
                            Số tiền: <strong class="text-danger"><?= format_vnd((float)$order['total_amount']) ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
