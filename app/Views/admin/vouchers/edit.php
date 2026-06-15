<?php /** @var array $voucher */ ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Sửa voucher <code><?= e($voucher['code']) ?></code></h2>
        <div class="text-muted">Cập nhật thông tin mã giảm giá.</div>
    </div>
    <a href="<?= url('/admin/vouchers') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/admin/vouchers/' . $voucher['id']) ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="code">Mã voucher <span class="text-danger">*</span></label>
                    <input id="code" name="code" class="form-control text-uppercase"
                           value="<?= old('code', $voucher['code']) ?>" required maxlength="50">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="discount_type">Loại giảm giá <span class="text-danger">*</span></label>
                    <select id="discount_type" name="discount_type" class="form-select" required>
                        <?php $dt = old('discount_type', $voucher['discount_type']); ?>
                        <option value="percent" <?= $dt === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                        <option value="fixed"   <?= $dt === 'fixed'   ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="discount_value">Giá trị giảm <span class="text-danger">*</span></label>
                    <input id="discount_value" name="discount_value" type="number"
                           class="form-control" value="<?= old('discount_value', $voucher['discount_value']) ?>"
                           min="1" step="0.01" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="min_order">Đơn tối thiểu (đ)</label>
                    <input id="min_order" name="min_order" type="number"
                           class="form-control" value="<?= old('min_order', $voucher['min_order']) ?>"
                           min="0" step="1000">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="max_uses">Giới hạn lượt dùng</label>
                    <input id="max_uses" name="max_uses" type="number"
                           class="form-control" value="<?= old('max_uses', $voucher['max_uses'] ?? '') ?>"
                           min="1" step="1" placeholder="Để trống = không giới hạn">
                    <div class="form-text text-muted">Đã dùng: <?= number_format((int)$voucher['used_count']) ?> lần</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="expires_at">Ngày hết hạn</label>
                    <?php
                        $expiresVal = old('expires_at', $voucher['expires_at'] ?? '');
                        if ($expiresVal && strlen($expiresVal) > 16) {
                            $expiresVal = substr($expiresVal, 0, 16);
                        }
                    ?>
                    <input id="expires_at" name="expires_at" type="datetime-local"
                           class="form-control" value="<?= e($expiresVal) ?>">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" <?= old('is_active') !== '' ? (old('is_active') ? 'checked' : '') : ((bool)$voucher['is_active'] ? 'checked' : '') ?>>
                        <label class="form-check-label" for="is_active">Đang hoạt động</label>
                    </div>
                </div>
            </div>

            <div class="admin-form-actions mt-4">
                <a href="<?= url('/admin/vouchers') ?>" class="btn btn-outline-secondary">Hủy</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                </button>
            </div>
        </form>
        <?php clearFormState(); ?>
    </div>
</div>
