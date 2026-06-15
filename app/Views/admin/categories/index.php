<?php
/**
 * @var array $result
 * @var array $filters
 */
$filters = $filters ?? ['q' => ''];

$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter([
        'q' => $filters['q'] ?? '',
        'page' => $page,
    ], static fn($value) => $value !== '' && $value !== null);

    return url('/admin/categories' . ($query ? '?' . http_build_query($query) : ''));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Quản lý danh mục</h2>
        <div class="text-muted">Quản lý nhóm sản phẩm dùng cho trang khách và form sản phẩm.</div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge text-bg-light border"><?= number_format((int)$result['total']) ?> danh mục</span>
        <a href="<?= url('/admin/categories/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
        </a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= url('/admin/categories') ?>">
            <div class="col-md-10">
                <label class="form-label">Tìm danh mục</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Tên hoặc slug">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button>
                <a class="btn btn-outline-secondary" href="<?= url('/admin/categories') ?>"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle admin-categories-table">
            <thead class="table-light">
                <tr>
                    <th>Danh mục</th>
                    <th>Slug</th>
                    <th>Danh mục cha</th>
                    <th class="text-center">Sản phẩm</th>
                    <th>Ngày tạo</th>
                    <th style="width:120px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['rows'])): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có danh mục phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($result['rows'] as $category): ?>
                        <?php $hasProducts = (int)$category['product_count'] > 0; ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($category['name']) ?></div>
                                <div class="text-muted small">#<?= e($category['id']) ?></div>
                            </td>
                            <td><code><?= e($category['slug']) ?></code></td>
                            <td><?= e($category['parent_name'] ?: 'Không có') ?></td>
                            <td class="text-center">
                                <span class="badge <?= $hasProducts ? 'text-bg-primary' : 'text-bg-light border' ?>">
                                    <?= number_format((int)$category['product_count']) ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$category['created_at']))) ?></td>
                            <td class="text-end">
                                <a href="<?= url('/admin/categories/' . $category['id'] . '/edit') ?>"
                                   class="btn btn-sm btn-outline-primary" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= url('/admin/categories/' . $category['id'] . '/delete') ?>"
                                      method="post" class="d-inline"
                                      data-confirm="Xóa danh mục này? Danh mục đang có sản phẩm sẽ không thể xóa.">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm <?= $hasProducts ? 'btn-outline-secondary' : 'btn-outline-danger' ?>"
                                            title="<?= $hasProducts ? 'Không thể xóa khi còn sản phẩm' : 'Xóa danh mục' ?>">
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
