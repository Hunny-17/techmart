<?php
/**
 * @var array $result
 * @var array $filters
 */
$filters = $filters ?? ['q' => '', 'status' => ''];

$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter([
        'q' => $filters['q'] ?? '',
        'status' => $filters['status'] ?? '',
        'page' => $page,
    ], static fn($value) => $value !== '' && $value !== null);

    return url('/admin/customers' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý khách hàng</h2>
        <div class="text-muted">Tìm kiếm, theo dõi hoạt động mua hàng và khóa tài khoản khi cần.</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-light border"><?= number_format((int)$result['total']) ?> khách hàng</span>
        <a href="<?= url('/admin/customers/export?' . http_build_query(array_filter($filters))) ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i> Xuất CSV
        </a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/customers') ?>">
            <div class="col-md-7">
                <label class="form-label">Tìm khách hàng</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Tên, email hoặc số điện thoại">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="locked" <?= $filters['status'] === 'locked' ? 'selected' : '' ?>>Đã khóa</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/customers') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle admin-customers-table">
            <thead class="table-light">
                <tr>
                    <th>Khách hàng</th>
                    <th>Liên hệ</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Đơn</th>
                    <th class="text-end">Đã chi</th>
                    <th>Đơn gần nhất</th>
                    <th class="text-center">Trạng thái</th>
                    <th style="width:130px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có khách hàng phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $customer): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($customer['full_name']) ?></div>
                                <div class="text-muted small">#<?= e($customer['id']) ?> · Tham gia <?= e(date('d/m/Y', strtotime((string)$customer['created_at']))) ?></div>
                            </td>
                            <td>
                                <div><?= e($customer['email']) ?></div>
                                <div class="text-muted small"><?= e($customer['phone'] ?: 'Chưa có SĐT') ?></div>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($customer['email_verified_at'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Đã xác thực
                                    </span>
                                    <div class="text-muted small mt-1">
                                        <?= e(date('d/m/Y', strtotime((string)$customer['email_verified_at']))) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="bi bi-exclamation-circle me-1"></i> Chưa xác thực
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold"><?= number_format((int)$customer['order_count']) ?></div>
                                <div class="text-muted small"><?= number_format((int)$customer['delivered_count']) ?> đã giao</div>
                            </td>
                            <td class="text-end fw-semibold"><?= format_vnd((float)$customer['total_spent']) ?></td>
                            <td>
                                <?php if (!empty($customer['last_order_at'])): ?>
                                    <?= e(date('d/m/Y H:i', strtotime((string)$customer['last_order_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($customer['status'] === 'active'): ?>
                                    <span class="badge bg-success">Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/customers/' . $customer['id']) ?>"
                                   class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (empty($customer['email_verified_at']) && $customer['status'] === 'active'): ?>
                                    <form action="<?= url('/admin/customers/' . $customer['id'] . '/resend-verification') ?>"
                                          method="post" class="d-inline"
                                          data-confirm="Gửi lại email xác thực cho khách hàng này?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-primary" title="Gửi lại email xác thực">
                                            <i class="bi bi-envelope-arrow-up"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($customer['status'] === 'active'): ?>
                                    <form action="<?= url('/admin/customers/' . $customer['id'] . '/lock') ?>"
                                          method="post" class="d-inline"
                                          data-confirm="Khóa tài khoản khách hàng này?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-warning" title="Khóa">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/customers/' . $customer['id'] . '/unlock') ?>"
                                          method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-success" title="Mở khóa">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pageUrlFn = $pageUrl; require __DIR__ . '/../../partials/pagination.php'; ?>
