<?php
/**
 * @var array $result
 * @var array $filters
 * @var array $admins
 * @var array $actions
 * @var array $entityTypes
 */
$filters     = $filters     ?? [];
$admins      = $admins      ?? [];
$actions     = $actions     ?? [];
$entityTypes = $entityTypes ?? [];

$actionColors = [
    'create'              => 'success',
    'update'              => 'primary',
    'delete'              => 'danger',
    'lock'                => 'warning',
    'unlock'              => 'info',
    'hide'                => 'secondary',
    'show'                => 'info',
    'change_status'       => 'primary',
    'change_payment_status' => 'success',
    'resend_verification' => 'secondary',
];

$actionLabels = [
    'create'              => 'Tạo mới',
    'update'              => 'Cập nhật',
    'delete'              => 'Xoá',
    'lock'                => 'Khoá',
    'unlock'              => 'Mở khoá',
    'hide'                => 'Ẩn',
    'show'                => 'Hiện',
    'change_status'       => 'Đổi TT',
    'change_payment_status' => 'Đổi TT thanh toán',
    'resend_verification' => 'Gửi lại XN',
];

$entityLabels = [
    'product'  => 'Sản phẩm',
    'order'    => 'Đơn hàng',
    'customer' => 'Khách hàng',
    'employee' => 'Nhân viên',
    'review'   => 'Đánh giá',
];

// Giữ filter khi chuyển trang
$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter(
        array_merge($filters, ['page' => $page]),
        static fn($v) => $v !== '' && $v !== null
    );
    return url('/admin/logs' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Lịch sử thao tác admin</h2>
        <div class="text-muted">Audit trail toàn bộ hành động có side-effect — ai làm gì, lúc nào, từ IP nào.</div>
    </div>
    <div class="text-muted small">Tổng: <?= number_format($result['total']) ?> bản ghi</div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/logs') ?>">
            <div class="col-md-2">
                <label class="form-label">Admin</label>
                <select name="user_id" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?= e($admin['id']) ?>"
                            <?= (string)($filters['user_id'] ?? '') === (string)$admin['id'] ? 'selected' : '' ?>>
                            <?= e($admin['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hành động</label>
                <select name="action" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>"
                            <?= ($filters['action'] ?? '') === $act ? 'selected' : '' ?>>
                            <?= e($actionLabels[$act] ?? $act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Đối tượng</label>
                <select name="entity_type" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($entityTypes as $et): ?>
                        <option value="<?= e($et) ?>"
                            <?= ($filters['entity_type'] ?? '') === $et ? 'selected' : '' ?>>
                            <?= e($entityLabels[$et] ?? $et) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" class="form-control"
                       value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" class="form-control"
                       value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit">
                    <i class="bi bi-search"></i>
                </button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/logs') ?>">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width:55px">STT</th>
                    <th style="width:155px">Thời gian</th>
                    <th style="width:180px">Admin</th>
                    <th class="text-center" style="width:110px">Hành động</th>
                    <th style="width:130px">Đối tượng</th>
                    <th>Mô tả</th>
                    <th style="width:115px">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Chưa có lịch sử thao tác phù hợp với bộ lọc.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $i => $log): ?>
                        <tr>
                            <td class="text-center text-muted small">
                                <?= ($result['page'] - 1) * $result['perPage'] + $i + 1 ?>
                            </td>
                            <td class="small text-nowrap">
                                <?= e(date('d/m/Y H:i:s', strtotime((string)$log['created_at']))) ?>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= e($log['admin_name']) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= e($log['admin_email']) ?></div>
                            </td>
                            <td class="text-center">
                                <?php $color = $actionColors[$log['action']] ?? 'secondary'; ?>
                                <span class="badge text-bg-<?= e($color) ?>">
                                    <?= e($actionLabels[$log['action']] ?? $log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-light border">
                                    <?= e($entityLabels[$log['entity_type']] ?? $log['entity_type']) ?>
                                </span>
                                <?php if ($log['entity_id'] !== null): ?>
                                    <span class="text-muted small"> #<?= e($log['entity_id']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= e((string)($log['description'] ?? '')) ?></td>
                            <td class="small font-monospace text-muted">
                                <?= e((string)($log['ip_address'] ?? '')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($result['lastPage'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination">
            <?php if ($result['page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pageUrl($result['page'] - 1) ?>">«</a>
                </li>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $result['lastPage']; $p++): ?>
                <li class="page-item <?= $p === $result['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $pageUrl($p) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($result['page'] < $result['lastPage']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pageUrl($result['page'] + 1) ?>">»</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
