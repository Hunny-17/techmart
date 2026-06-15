<?php /** @var array $result */ /** @var array $filters */ ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý Voucher</h2>
        <div class="text-muted">Tạo và quản lý mã giảm giá cho khách hàng.</div>
    </div>
    <a href="<?= url('/admin/vouchers/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Thêm voucher
    </a>
</div>

<form class="card card-body shadow-sm mb-4" method="get">
    <div class="row g-2">
        <div class="col-md-6">
            <input class="form-control" name="q" placeholder="Tìm theo mã voucher..."
                   value="<?= e($filters['q'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <select class="form-select" name="active">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= ($filters['active'] ?? '') === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                <option value="0" <?= ($filters['active'] ?? '') === '0' ? 'selected' : '' ?>>Đã tắt</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100"><i class="bi bi-search"></i> Tìm</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mã</th>
                    <th>Loại / Giá trị</th>
                    <th>Đơn tối thiểu</th>
                    <th>Đã dùng / Giới hạn</th>
                    <th>Hết hạn</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có voucher nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $v): ?>
                        <tr>
                            <td>
                                <code class="fw-bold"><?= e($v['code']) ?></code>
                                <button type="button"
                                        class="btn btn-sm btn-link p-0 ms-1 text-muted"
                                        data-copy="<?= e($v['code']) ?>"
                                        title="Copy mã">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </td>
                            <td>
                                <?php if ($v['discount_type'] === 'percent'): ?>
                                    <span class="badge text-bg-info">Giảm <?= e($v['discount_value']) ?>%</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Giảm <?= format_vnd((float)$v['discount_value']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= (float)$v['min_order'] > 0 ? format_vnd((float)$v['min_order']) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?= number_format((int)$v['used_count']) ?>
                                <?= $v['max_uses'] !== null ? ' / ' . number_format((int)$v['max_uses']) : ' / ∞' ?>
                            </td>
                            <td>
                                <?php if ($v['expires_at']): ?>
                                    <?php $expired = strtotime((string)$v['expires_at']) < time(); ?>
                                    <span class="<?= $expired ? 'text-danger' : '' ?>">
                                        <?= e(date('d/m/Y H:i', strtotime((string)$v['expires_at']))) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Không giới hạn</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?= url('/admin/vouchers/' . $v['id'] . '/toggle') ?>"
                                      method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <?php if ((bool)$v['is_active']): ?>
                                        <button type="submit"
                                                class="badge text-bg-success border-0 bg-success"
                                                title="Đang hoạt động — click để tắt">
                                            Hoạt động ▾
                                        </button>
                                    <?php else: ?>
                                        <button type="submit"
                                                class="badge text-bg-secondary border-0"
                                                title="Đã tắt — click để bật">
                                            Đã tắt ▸
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/vouchers/' . $v['id'] . '/edit') ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= url('/admin/vouchers/' . $v['id'] . '/delete') ?>"
                                      method="post" class="d-inline"
                                      data-confirm="Xóa voucher <?= e($v['code']) ?>?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
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

<?php require __DIR__ . '/../../partials/pagination.php'; ?>
