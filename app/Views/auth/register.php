<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">Đăng ký tài khoản</h3>

                <form action="<?= url('/register') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Honeypot - đánh lừa bot, user thật không thấy -->
                    <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                        <label for="website">Trang web cá nhân</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Họ và tên *</label>
                        <input name="full_name" class="form-control" value="<?= old('full_name') ?>" required>
                        <?php if ($e = errors('full_name')): ?><div class="text-danger small"><?= e($e) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                        <div class="form-text">Sau khi đăng ký, hệ thống sẽ gửi email xác thực tới địa chỉ này.</div>
                        <?php if ($e = errors('email')): ?><div class="text-danger small"><?= e($e) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu * (tối thiểu 6 ký tự)</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password" class="form-control" required minlength="6">
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" title="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if ($e = errors('password')): ?><div class="text-danger small"><?= e($e) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input name="phone" class="form-control" value="<?= old('phone') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"><?= old('address') ?></textarea>
                    </div>

                    <button class="btn btn-primary w-100">Đăng ký</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Đã có tài khoản? <a href="<?= url('/login') ?>">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= csp_nonce() ?>">
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        if (!input) {
            return;
        }

        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
});
</script>

<?php clearFormState(); ?>
