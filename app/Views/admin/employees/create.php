<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Thêm nhân viên</h2>
        <div class="text-muted">Tạo tài khoản staff để hỗ trợ quản trị đơn hàng, sản phẩm và khách hàng.</div>
    </div>
    <a href="<?= url('/admin/employees') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/admin/employees') ?>" method="post" class="admin-employee-form">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Họ tên</label>
                    <input id="full_name" name="full_name" class="form-control"
                           value="<?= old('full_name') ?>" autocomplete="name" required>
                    <?php if (errors('full_name')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('full_name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email đăng nhập</label>
                    <input id="email" name="email" type="email" class="form-control"
                           value="<?= old('email') ?>" autocomplete="email" required>
                    <?php if (errors('email')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('email')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input id="phone" name="phone" class="form-control"
                           value="<?= old('phone') ?>" autocomplete="tel">
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Mật khẩu tạm</label>
                    <div class="input-group">
                        <input id="password" name="password" type="password" class="form-control"
                               minlength="6" autocomplete="new-password" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" type="button" id="generate-password" title="Tạo mật khẩu">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                    <?php if (errors('password')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('password')) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Mật khẩu tối thiểu 6 ký tự. Có thể tạo nhanh rồi gửi riêng cho nhân viên.</div>
                </div>
            </div>

            <div class="admin-form-actions mt-4">
                <a href="<?= url('/admin/employees') ?>" class="btn btn-outline-secondary">Hủy</a>
                <button class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu nhân viên
                </button>
            </div>
        </form>
        <?php clearFormState(); ?>
    </div>
</div>

<script nonce="<?= csp_nonce() ?>">
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('toggle-password');
const generatePassword = document.getElementById('generate-password');

togglePassword?.addEventListener('click', () => {
    const visible = passwordInput.type === 'text';
    passwordInput.type = visible ? 'password' : 'text';
    togglePassword.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
});

generatePassword?.addEventListener('click', () => {
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    let password = '';
    for (let i = 0; i < 10; i++) {
        password += alphabet[Math.floor(Math.random() * alphabet.length)];
    }
    passwordInput.value = password;
    passwordInput.type = 'text';
    togglePassword.innerHTML = '<i class="bi bi-eye-slash"></i>';
    passwordInput.focus();
});
</script>
