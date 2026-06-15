<?php
/**
 * @var array $order
 * @var array $items
 * @var array $signature
 */
$statusLabels = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
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
$createdAt = date('d/m/Y H:i', strtotime((string)$order['created_at']));
$signedAt = date('d/m/Y H:i', strtotime((string)($signature['signed_at'] ?? $order['created_at'])));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?= e($order['id']) ?> - TechMart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f6f9;
            color: #1f2937;
            font-size: 14px;
        }

        .invoice-page {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .08);
            padding: 36px;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #0d6efd;
            color: #fff;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .invoice-title {
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .table th {
            white-space: nowrap;
        }

        .signature-card {
            border: 1px dashed #0d6efd;
            background: #f8fbff;
            border-radius: 14px;
            padding: 16px;
        }

        .signature-code {
            font-family: Consolas, Monaco, monospace;
            font-size: 18px;
            letter-spacing: .08em;
            color: #0d6efd;
            word-break: break-word;
        }

        .signature-hash {
            font-family: Consolas, Monaco, monospace;
            font-size: 11px;
            word-break: break-all;
        }

        .print-toolbar {
            max-width: 900px;
            margin: 24px auto 0;
        }

        @media print {
            body {
                background: #fff;
            }

            .print-toolbar {
                display: none !important;
            }

            .invoice-page {
                margin: 0;
                max-width: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }

            a[href]::after {
                content: "";
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar d-flex justify-content-between gap-2">
        <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="btn btn-outline-secondary">
            ← Quay lại đơn hàng
        </a>
        <button type="button" class="btn btn-primary" id="invoice-print-btn">
            In / Lưu PDF
        </button>
    </div>

    <main class="invoice-page">
        <div class="d-flex flex-wrap justify-content-between gap-4 mb-4">
            <div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="brand-mark">TM</span>
                    <div>
                        <h1 class="h4 mb-0">TechMart</h1>
                        <div class="text-muted">Thiết bị công nghệ chính hãng</div>
                    </div>
                </div>
                <div class="text-muted">
                    Website: techmart.local<br>
                    Email: support@techmart.test<br>
                    Hotline: 1900 0000
                </div>
            </div>
            <div class="text-md-end">
                <div class="invoice-title text-muted fw-semibold mb-1">Hóa đơn bán hàng</div>
                <h2 class="h3 mb-2">#<?= e($order['id']) ?></h2>
                <div>Ngày đặt: <strong><?= e($createdAt) ?></strong></div>
                <div>Trạng thái: <strong><?= e($statusLabels[$order['status']] ?? $order['status']) ?></strong></div>
                <div>Thanh toán: <strong><?= e($paymentLabels[$order['payment_method']] ?? $order['payment_method']) ?></strong></div>
                <div>Trạng thái thanh toán: <strong><?= e($paymentStatusLabels[$order['payment_status'] ?? 'unpaid'] ?? ($order['payment_status'] ?? 'unpaid')) ?></strong></div>
                <?php if (!empty($order['payment_reference_code'])): ?>
                    <div>Mã thanh toán: <strong><?= e($order['payment_reference_code']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>

        <hr>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <h3 class="h6 text-uppercase text-muted">Khách hàng</h3>
                <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
                <div><?= e($order['customer_email']) ?></div>
                <div><?= e($order['customer_phone'] ?: '-') ?></div>
            </div>
            <div class="col-md-6">
                <h3 class="h6 text-uppercase text-muted">Địa chỉ giao hàng</h3>
                <div><?= nl2br(e($order['shipping_address'])) ?></div>
                <?php if (!empty($order['note'])): ?>
                    <div class="mt-2"><span class="text-muted">Ghi chú:</span> <?= nl2br(e($order['note'])) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 48px;">#</th>
                        <th>Sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($item['product_name']) ?></div>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <div class="small text-muted">Mẫu: <?= e($item['variant_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= number_format((int)$item['quantity']) ?></td>
                            <td class="text-end"><?= format_vnd((float)$item['unit_price']) ?></td>
                            <td class="text-end"><?= format_vnd((float)$item['unit_price'] * (int)$item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                        <?php $subtotal = (float)$order['total_amount'] + (float)$order['discount_amount']; ?>
                        <tr>
                            <td colspan="4" class="text-end text-muted">Tạm tính</td>
                            <td class="text-end text-muted"><?= format_vnd($subtotal) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-success">Giảm giá (Voucher)</td>
                            <td class="text-end text-success fw-semibold">-<?= format_vnd((float)$order['discount_amount']) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th colspan="4" class="text-end">Tổng thanh toán</th>
                        <th class="text-end fs-5"><?= format_vnd((float)$order['total_amount']) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="signature-card mt-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <div class="fw-semibold text-primary mb-1">Chữ ký số nội bộ</div>
                    <div class="text-muted small">
                        Thuật toán: <?= e($signature['algorithm'] ?? 'HMAC-SHA256') ?> ·
                        Đơn vị ký: <?= e($signature['issuer'] ?? 'TechMart') ?> ·
                        Thời điểm ký: <?= e($signedAt) ?>
                    </div>
                </div>
                <div class="text-md-end">
                    <div class="text-muted small">Mã xác thực</div>
                    <div class="signature-code fw-bold"><?= e($signature['code'] ?? '') ?></div>
                </div>
            </div>
            <hr class="my-3">
            <div class="small text-muted mb-1">Hash xác thực đầy đủ:</div>
            <div class="signature-hash"><?= e($signature['hash'] ?? '') ?></div>
            <div class="small text-muted mt-2">
                Ghi chú: chữ ký này dùng để xác minh nội dung hóa đơn trong hệ thống TechMart. Nếu mã đơn,
                tổng tiền hoặc danh sách sản phẩm thay đổi, mã xác thực sẽ thay đổi.
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-6 text-center">
                <div class="fw-semibold mb-5">Người lập phiếu</div>
                <div class="text-muted">(Ký, ghi rõ họ tên)</div>
            </div>
            <div class="col-6 text-center">
                <div class="fw-semibold mb-5">Khách hàng</div>
                <div class="text-muted">(Ký, ghi rõ họ tên)</div>
            </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
            Cảm ơn quý khách đã mua sắm tại TechMart.
        </p>
    </main>
<script nonce="<?= csp_nonce() ?>">
document.getElementById('invoice-print-btn').addEventListener('click', () => window.print());
</script>
</body>
</html>
