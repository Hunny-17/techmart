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

    return url('/admin/employees' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý nhân viên</h2>
        <div class="text-muted">Quản lý tài khoản nhân viên vận hành hệ thống.</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge text-bg-light border"><?= number_format((int)$result['total']) ?> nhân viên</span>
        <a href="<?= url('/admin/employees/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Thêm nhân viên
        </a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/employees') ?>">
            <div class="col-md-7">
                <label class="form-label">Tìm nhân viên</label>
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
                <a class="btn btn-outline-secondary" href="<?= url('/admin/employees') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle admin-employees-table">
            <thead class="table-light">
                <tr>
                    <th>Nhân viên</th>
                    <th>Liên hệ</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width:90px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có nhân viên phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $employee): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($employee['full_name']) ?></div>
                                <div class="text-muted small">#<?= e($employee['id']) ?> · Staff</div>
                            </td>
                            <td>
                                <div><?= e($employee['email']) ?></div>
                                <div class="text-muted small"><?= e($employee['phone'] ?: 'Chưa có SĐT') ?></div>
                            </td>
                            <td class="text-center">
                                <?php if ($employee['status'] === 'active'): ?>
                                    <span class="badge bg-success">Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$employee['created_at']))) ?></td>
                            <td class="text-end">
                                <?php if ($employee['status'] === 'active'): ?>
                                    <form action="<?= url('/admin/employees/' . $employee['id'] . '/lock') ?>"
                                          method="post" class="d-inline"
                                          data-confirm="Khóa tài khoản nhân viên này?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-warning" title="Khóa">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('/admin/employees/' . $employee['id'] . '/unlock') ?>"
                                          method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-success" title="Mở khóa">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?= url('/admin/employees/' . $employee['id'] . '/delete') ?>"
                                      method="post" class="d-inline"
                                      data-confirm="Xóa nhân viên này? Nếu có dữ liệu liên quan, hệ thống sẽ khóa tài khoản thay vì xóa cứng.">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" title="Xóa hoặc khóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pageUrlFn = $pageUrl; require __DIR__ . '/../../partials/pagination.php'; ?>
