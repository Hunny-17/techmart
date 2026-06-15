<?php /** @var array $categories */ ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Thêm danh mục</h2>
        <div class="text-muted">Tạo danh mục để phân nhóm sản phẩm.</div>
    </div>
    <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/admin/categories') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Tên danh mục</label>
                    <input id="name" name="name" class="form-control" value="<?= old('name') ?>" required>
                    <?php if (errors('name')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="parent_id">Danh mục cha</label>
                    <select id="parent_id" name="parent_id" class="form-select">
                        <option value="">Không có</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['id']) ?>" <?= old('parent_id') === (string)$category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-form-actions mt-4">
                <a href="<?= url('/admin/categories') ?>" class="btn btn-outline-secondary">Hủy</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu danh mục
                </button>
            </div>
        </form>
        <?php clearFormState(); ?>
    </div>
</div>
