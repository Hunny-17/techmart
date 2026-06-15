<?php /** @var string $token */ /** @var string $email */ ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3"
                         style="width:56px;height:56px">
                        <i class="bi bi-key fs-3"></i>
                    </div>
                    <h3 class="card-title mb-2">Đặt lại mật khẩu</h3>
                    <?php if (!empty($email)): ?>
                        <p class="text-muted mb-0"><?= e($email) ?></p>
                    <?php endif; ?>
                </div>

                <form action="<?= url('/reset-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label" for="password">Mật khẩu mới</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password" class="form-control" minlength="6" required autofocus>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" title="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if ($err = errors('password')): ?>
                            <div class="text-danger small mt-1"><?= e($err) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password_confirmation">Nhập lại mật khẩu mới</label>
                        <div class="input-group">
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="6" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation" title="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if ($err = errors('password_confirmation')): ?>
                            <div class="text-danger small mt-1"><?= e($err) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-1"></i> Cập nhật mật khẩu
                    </button>
                </form>
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
