<?php /** @var array $category */ /** @var array $categories */ ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Sửa danh mục</h2>
        <div class="text-muted">Cập nhật tên hoặc danh mục cha.</div>
    </div>
    <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/admin/categories/' . $category['id']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Tên danh mục</label>
                    <input id="name" name="name" class="form-control" value="<?= old('name', $category['name']) ?>" required>
                    <?php if (errors('name')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="parent_id">Danh mục cha</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="">Không có</option>
                        <?php foreach ($categories as $item): ?>
                            <?php $selected = old('parent_id', (string)($category['parent_id'] ?? '')) === (string)$item['id']; ?>
                            <option value="<?= e($item['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= e($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-text">Slug hiện tại: <code><?= e($category['slug']) ?></code>. Slug sẽ tự cập nhật theo tên mới.</div>
                </div>
            </div>

            <div class="admin-form-actions mt-4">
                <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary">Hủy</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                </button>
            </div>
        </form>
        <?php clearFormState(); ?>
    </div>
</div>
