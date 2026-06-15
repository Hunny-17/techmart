<?php
/**
 * @var array $customer
 * @var array $orders
 * @var array $stats
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
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1"><?= e($customer['full_name']) ?></h2>
        <div class="text-muted"><?= e($customer['email']) ?></div>
    </div>
    <a href="<?= url('/admin/customers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Thông tin tài khoản</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Họ tên</dt>
                    <dd class="col-7"><?= e($customer['full_name']) ?></dd>

                    <dt class="col-5 text-muted">Email</dt>
                    <dd class="col-7"><?= e($customer['email']) ?></dd>

                    <dt class="col-5 text-muted">Điện thoại</dt>
                    <dd class="col-7"><?= e($customer['phone'] ?: '—') ?></dd>

                    <dt class="col-5 text-muted">Địa chỉ</dt>
                    <dd class="col-7"><?= nl2br(e($customer['address'] ?: '—')) ?></dd>

                    <dt class="col-5 text-muted">Ngày đăng ký</dt>
                    <dd class="col-7"><?= e(date('d/m/Y', strtotime((string)$customer['created_at']))) ?></dd>

                    <dt class="col-5 text-muted">Xác thực email</dt>
                    <dd class="col-7">
                        <?php if (!empty($customer['email_verified_at'])): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Đã xác thực</span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Chưa xác thực</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-5 text-muted">Trạng thái</dt>
                    <dd class="col-7">
                        <?php if ($customer['status'] === 'active'): ?>
                            <span class="badge bg-success">Đang hoạt động</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Đã khóa</span>
                        <?php endif; ?>
                    </dd>
                </dl>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php if ($customer['status'] === 'active'): ?>
                        <form action="<?= url('/admin/customers/' . $customer['id'] . '/lock') ?>"
                              method="post" data-confirm="Khóa tài khoản này?">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-lock me-1"></i> Khóa
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?= url('/admin/customers/' . $customer['id'] . '/unlock') ?>"
                              method="post">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-success">
                                <i class="bi bi-unlock me-1"></i> Mở khóa
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-4 fw-bold"><?= number_format((int)$stats['order_count']) ?></div>
                        <div class="text-muted small">Tổng đơn hàng</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-6 fw-bold text-success"><?= format_vnd((float)$stats['total_spent']) ?></div>
                        <div class="text-muted small">Đã chi tiêu (đã giao)</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-3">
                        <div class="fs-6 fw-bold text-danger"><?= format_vnd((float)$stats['total_savings']) ?></div>
                        <div class="text-muted small">Đã tiết kiệm</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Lịch sử đơn hàng</div>
            <div class="card-body p-0">
                <?php if (empty($orders)): ?>
                    <div class="text-center text-muted py-4">Khách hàng chưa có đơn hàng nào.</div>
                <?php else: ?>
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th class="text-center">SP</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="fw-semibold">#<?= e($order['id']) ?></td>
                                    <td class="text-center text-muted small">
                                        <?= number_format((int)$order['total_quantity']) ?> sp
                                    </td>
                                    <td class="text-end fw-semibold">
                                        <?= format_vnd((float)$order['total_amount']) ?>
                                        <?php if ((float)($order['discount_amount'] ?? 0) > 0): ?>
                                            <div class="small text-success">
                                                <i class="bi bi-ticket-perforated"></i>
                                                -<?= format_vnd((float)$order['discount_amount']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= e($statusBadges[$order['status']] ?? 'bg-secondary') ?>">
                                            <?= e($statusLabels[$order['status']] ?? $order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="small"><?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?></td>
                                    <td>
                                        <a href="<?= url('/admin/orders/' . $order['id']) ?>"
                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
