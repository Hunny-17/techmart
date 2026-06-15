<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Thêm voucher</h2>
        <div class="text-muted">Tạo mã giảm giá mới cho khách hàng.</div>
    </div>
    <a href="<?= url('/admin/vouchers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/admin/vouchers') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="code">Mã voucher <span class="text-danger">*</span></label>
                    <input id="code" name="code" class="form-control text-uppercase"
                           value="<?= old('code') ?>" required maxlength="50"
                           placeholder="VD: SALE10, FREESHIP">
                    <div class="form-text">Chỉ dùng chữ hoa và số, không dấu cách.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="discount_type">Loại giảm giá <span class="text-danger">*</span></label>
                    <select id="discount_type" name="discount_type" class="form-select" required>
                        <option value="percent" <?= old('discount_type') === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                        <option value="fixed"   <?= old('discount_type') === 'fixed'   ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="discount_value">Giá trị giảm <span class="text-danger">*</span></label>
                    <input id="discount_value" name="discount_value" type="number"
                           class="form-control" value="<?= old('discount_value') ?>"
                           min="1" step="0.01" required placeholder="VD: 10 (%) hoặc 50000 (đ)">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="min_order">Đơn tối thiểu (đ)</label>
                    <input id="min_order" name="min_order" type="number"
                           class="form-control" value="<?= old('min_order', '0') ?>"
                           min="0" step="1000" placeholder="0 = không giới hạn">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="max_uses">Giới hạn lượt dùng</label>
                    <input id="max_uses" name="max_uses" type="number"
                           class="form-control" value="<?= old('max_uses') ?>"
                           min="1" step="1" placeholder="Để trống = không giới hạn">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="expires_at">Ngày hết hạn</label>
                    <input id="expires_at" name="expires_at" type="datetime-local"
                           class="form-control" value="<?= old('expires_at') ?>">
                </div>
            </div>

            <div class="admin-form-actions mt-4">
                <a href="<?= url('/admin/vouchers') ?>" class="btn btn-outline-secondary">Hủy</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Tạo voucher
                </button>
            </div>
        </form>
        <?php clearFormState(); ?>
    </div>
</div>
