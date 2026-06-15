<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">Đăng nhập</h3>

                <form action="<?= url('/login') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= old('email') ?>" required autofocus>
                        <?php if ($err = errors('email')): ?>
                            <div class="text-danger small mt-1"><?= e($err) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                        <?php if ($err = errors('password')): ?>
                            <div class="text-danger small mt-1"><?= e($err) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <a class="small text-decoration-none" href="<?= url('/forgot-password') ?>">Quên mật khẩu?</a>
                    </div>

                    <button class="btn btn-primary w-100">Đăng nhập</button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Chưa có tài khoản? <a href="<?= url('/register') ?>">Đăng ký</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php clearFormState(); ?>
