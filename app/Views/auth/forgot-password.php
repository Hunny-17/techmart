<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3"
                         style="width:56px;height:56px">
                        <i class="bi bi-shield-lock fs-3"></i>
                    </div>
                    <h3 class="card-title mb-2">Quên mật khẩu?</h3>
                    <p class="text-muted mb-0">
                        Nhập email đã đăng ký. TechMart sẽ gửi cho bạn một liên kết để tạo mật khẩu mới.
                    </p>
                </div>

                <form action="<?= url('/forgot-password') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Email tài khoản</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="you@example.com"
                               value="<?= old('email') ?>" required autofocus>
                        <?php if ($err = errors('email')): ?>
                            <div class="text-danger small mt-1"><?= e($err) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Liên kết đặt lại mật khẩu có hiệu lực trong 1 giờ và chỉ sử dụng được một lần.
                    </div>

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Gửi liên kết đặt lại mật khẩu
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Đã nhớ mật khẩu? <a href="<?= url('/login') ?>">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php clearFormState(); ?>
