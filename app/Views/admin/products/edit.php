<?php /** @var array $product */ /** @var array $categories */ /** @var array $extraImages */ /** @var array $variants */ ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Sửa sản phẩm</h2>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('/admin/products/' . $product['id'] . '/stock') ?>" class="btn btn-outline-success">
            <i class="bi bi-box-arrow-in-down"></i> Nhập kho
        </a>
        <a href="<?= url('/admin/products') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<form action="<?= url('/admin/products/' . $product['id']) ?>" method="post" enctype="multipart/form-data" class="card shadow-sm">
    <div class="card-body">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label">Tên sản phẩm *</label>
            <input name="name" class="form-control" value="<?= e($product['name']) ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Danh mục *</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c['id']) ?>"
                            <?= (int)$c['id'] === (int)$product['category_id'] ? 'selected' : '' ?>>
                            <?= e(!empty($c['parent_name']) ? $c['parent_name'] . ' / ' . $c['name'] : $c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Trạng thái *</label>
                <select name="status" class="form-select" required>
                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Đang bán</option>
                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Ngừng bán</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Giá mặc định (VND) *</label>
                <input type="number" name="price" id="product-price" class="form-control" min="0" step="1000" value="<?= e($product['price']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tồn kho mặc định *</label>
                <input type="number" name="stock_quantity" class="form-control" min="0" value="<?= e($product['stock_quantity']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea name="description" rows="7" class="form-control"><?= e($product['description']) ?></textarea>
            <div class="form-text">Nội dung này hiển thị ở trang chi tiết sản phẩm, ngay phía trên phần đánh giá.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh chính</label>
            <div class="admin-image-preview mb-2">
                <img id="main-image-preview" src="<?= e($product['image_url'] ?: 'https://placehold.co/160?text=No+Image') ?>" alt="">
            </div>
            <input type="file" name="image" class="form-control image-preview-input" accept="image/*" data-preview="#main-image-preview">
            <div class="form-text">Để trống nếu không đổi ảnh chính.</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Ảnh phụ / gallery hiện tại</label>
            <div id="existing-extra-images" class="admin-gallery-grid mb-2">
                <?php foreach (($extraImages ?? []) as $image): ?>
                    <div class="admin-gallery-item" data-url="<?= e($image['image_url']) ?>">
                        <img src="<?= e($image['image_url']) ?>" alt="">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-extra-image" title="Xóa ảnh">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <textarea name="existing_extra_images" id="existing-extra-images-input" class="d-none"><?=
                e(implode("\n", array_map(static fn($img) => $img['image_url'], $extraImages ?? [])))
            ?></textarea>
            <input type="file" name="extra_images[]" id="extra-images-input" class="form-control" accept="image/*" multiple>
            <div id="extra-images-preview" class="admin-gallery-grid mt-2"></div>
            <div class="form-text">Chọn thêm ảnh phụ mới. Bấm thùng rác trên ảnh cũ nếu muốn bỏ khỏi gallery.</div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Mẫu sản phẩm</label>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-variant">
                    <i class="bi bi-plus-lg"></i> Thêm mẫu
                </button>
            </div>
            <div id="variant-list" class="vstack gap-3">
                <?php foreach (($variants ?? []) as $variant): ?>
                    <div class="border rounded p-3 variant-row">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Tên mẫu</label>
                                <input name="variant_names[]" class="form-control" value="<?= e($variant['name']) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Giá</label>
                                <input type="number" name="variant_prices[]" class="form-control variant-price" min="0" step="1000" value="<?= e($variant['price'] ?? '') ?>">
                                <div class="invalid-feedback variant-price-feedback"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tồn kho</label>
                                <input type="number" name="variant_stocks[]" class="form-control variant-stock" min="0" value="<?= e($variant['stock_quantity']) ?>">
                                <div class="invalid-feedback variant-stock-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ảnh mẫu</label>
                                <div class="variant-image-field">
                                    <div class="admin-image-preview">
                                        <img class="variant-image-preview" src="<?= e($variant['image_url'] ?: 'https://placehold.co/96?text=No+Image') ?>" alt="">
                                    </div>
                                    <div class="variant-image-actions">
                                        <label class="btn btn-sm btn-outline-secondary mb-0">
                                            <i class="bi bi-image"></i> Chọn ảnh
                                            <input type="file" name="variant_images[]" class="visually-hidden variant-image-input" accept="image/*">
                                        </label>
                                        <span class="variant-file-name text-muted small">Chưa chọn file</span>
                                        <input type="hidden" name="variant_existing_images[]" value="<?= e($variant['image_url'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 text-md-end">
                                <button type="button" class="btn btn-outline-danger remove-variant" title="Xóa mẫu">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-text">Giá mẫu tối đa bằng 3 lần giá mặc định; để trống nếu mẫu dùng giá mặc định.</div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Cập nhật</button>
            <a href="<?= url('/admin/products') ?>" class="btn btn-outline-secondary">Hủy</a>
        </div>
    </div>
</form>

<template id="variant-template">
    <div class="border rounded p-3 variant-row">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tên mẫu</label>
                <input name="variant_names[]" class="form-control" placeholder="Đen / 128GB">
            </div>
            <div class="col-md-2">
                <label class="form-label">Giá</label>
                <input type="number" name="variant_prices[]" class="form-control variant-price" min="0" step="1000">
                <div class="invalid-feedback variant-price-feedback"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tồn kho</label>
                <input type="number" name="variant_stocks[]" class="form-control variant-stock" min="0" value="0">
                <div class="invalid-feedback variant-stock-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ảnh mẫu</label>
                <div class="variant-image-field">
                    <div class="admin-image-preview">
                        <img class="variant-image-preview" src="https://placehold.co/96?text=No+Image" alt="">
                    </div>
                    <div class="variant-image-actions">
                        <label class="btn btn-sm btn-outline-secondary mb-0">
                            <i class="bi bi-image"></i> Chọn ảnh
                            <input type="file" name="variant_images[]" class="visually-hidden variant-image-input" accept="image/*">
                        </label>
                        <span class="variant-file-name text-muted small">Chưa chọn file</span>
                        <input type="hidden" name="variant_existing_images[]" value="">
                    </div>
                </div>
            </div>
            <div class="col-md-1 text-md-end">
                <button type="button" class="btn btn-outline-danger remove-variant" title="Xóa mẫu">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script nonce="<?= csp_nonce() ?>">
const variantList = document.getElementById('variant-list');
const template = document.getElementById('variant-template');
const productPrice = document.getElementById('product-price');
const variantPriceMultiplier = 3;
const existingImagesInput = document.getElementById('existing-extra-images-input');
const submitButton = document.querySelector('form button[type="submit"], form button:not([type])');

function syncVariantPriceLimits() {
    const max = Math.max(0, Number(productPrice.value || 0) * variantPriceMultiplier);
    document.querySelectorAll('.variant-price').forEach(input => {
        input.max = String(max);
    });
    validateVariantRows();
}

function setFieldError(input, feedback, message) {
    input.classList.toggle('is-invalid', message !== '');
    if (feedback) {
        feedback.textContent = message;
    }
}

function validateVariantRows() {
    const max = Math.max(0, Number(productPrice.value || 0) * variantPriceMultiplier);
    let hasError = false;

    document.querySelectorAll('.variant-row').forEach(row => {
        const priceInput = row.querySelector('.variant-price');
        const stockInput = row.querySelector('.variant-stock');
        const priceFeedback = row.querySelector('.variant-price-feedback');
        const stockFeedback = row.querySelector('.variant-stock-feedback');
        let priceError = '';
        let stockError = '';

        if (priceInput && priceInput.value !== '') {
            const value = Number(priceInput.value);
            if (Number.isNaN(value) || value < 0) {
                priceError = 'Giá mẫu phải là số không âm.';
            } else if (value > max) {
                priceError = `Tối đa ${new Intl.NumberFormat('vi-VN').format(max)}đ.`;
            }
        }

        if (stockInput && stockInput.value !== '') {
            const value = Number(stockInput.value);
            if (!Number.isInteger(value) || value < 0) {
                stockError = 'Tồn kho phải là số nguyên không âm.';
            }
        }

        setFieldError(priceInput, priceFeedback, priceError);
        setFieldError(stockInput, stockFeedback, stockError);
        hasError = hasError || priceError !== '' || stockError !== '';
    });

    if (submitButton) {
        submitButton.disabled = hasError;
    }
}

function updateExistingImagesInput() {
    existingImagesInput.value = Array.from(document.querySelectorAll('#existing-extra-images .admin-gallery-item'))
        .map(item => item.dataset.url)
        .filter(Boolean)
        .join('\n');
}

function previewFile(input, image) {
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith('image/')) {
        return;
    }

    const reader = new FileReader();
    reader.onload = event => {
        image.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

document.getElementById('add-variant').addEventListener('click', () => {
    variantList.appendChild(template.content.cloneNode(true));
    syncVariantPriceLimits();
});

variantList.addEventListener('click', event => {
    const button = event.target.closest('.remove-variant');
    if (button) {
        button.closest('.variant-row').remove();
        validateVariantRows();
    }
});

variantList.addEventListener('input', event => {
    if (event.target.closest('.variant-price, .variant-stock')) {
        validateVariantRows();
    }
});

variantList.addEventListener('change', event => {
    const input = event.target.closest('.variant-image-input');
    if (!input) {
        return;
    }
    const preview = input.closest('.variant-row').querySelector('.variant-image-preview');
    const fileName = input.closest('.variant-row').querySelector('.variant-file-name');
    if (fileName) {
        fileName.textContent = input.files && input.files[0] ? input.files[0].name : 'Chưa chọn file';
    }
    previewFile(input, preview);
});

document.getElementById('existing-extra-images').addEventListener('click', event => {
    const button = event.target.closest('.remove-extra-image');
    if (!button) {
        return;
    }
    button.closest('.admin-gallery-item').remove();
    updateExistingImagesInput();
});

document.querySelectorAll('.image-preview-input').forEach(input => {
    input.addEventListener('change', () => {
        const preview = document.querySelector(input.dataset.preview);
        previewFile(input, preview);
    });
});

document.getElementById('extra-images-input').addEventListener('change', event => {
    const preview = document.getElementById('extra-images-preview');
    preview.innerHTML = '';
    Array.from(event.target.files || []).forEach(file => {
        if (!file.type.startsWith('image/')) {
            return;
        }
        const item = document.createElement('div');
        item.className = 'admin-gallery-item';
        const image = document.createElement('img');
        item.appendChild(image);
        preview.appendChild(item);
        const reader = new FileReader();
        reader.onload = e => {
            image.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
});

productPrice.addEventListener('input', syncVariantPriceLimits);
syncVariantPriceLimits();
</script>
