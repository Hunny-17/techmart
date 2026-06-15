<?php /** @var array $user */ /** @var array $stats */ ?>

<?php
    $_pWords = preg_split('/\s+/', trim($user['full_name'] ?? ''));
    $_pInitials = mb_strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1, 'UTF-8'), array_slice($_pWords, 0, 2))), 'UTF-8');
?>
<section class="page-hero account-hero mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="profile-avatar" aria-hidden="true"><?= e($_pInitials) ?></span>
        <div>
            <span class="store-eyebrow">Tài khoản TechMart</span>
            <h1><?= e($user['full_name'] ?? 'Tài khoản của tôi') ?></h1>
            <p>Cập nhật thông tin nhận hàng, theo dõi lịch sử mua sắm và bảo vệ tài khoản bằng mật khẩu mạnh.</p>
        </div>
    </div>
    <div class="account-hero-actions">
        <a href="<?= url('/my-orders') ?>" class="btn btn-primary">
            <i class="bi bi-receipt me-1"></i> Đơn hàng
        </a>
        <a href="<?= url('/my-wishlist') ?>" class="btn btn-outline-primary">
            <i class="bi bi-heart me-1"></i> Yêu thích
        </a>
    </div>
</section>

<div class="account-stat-grid mb-4">
    <div class="account-stat-card">
        <span class="account-stat-icon"><i class="bi bi-bag-check"></i></span>
        <div>
            <strong><?= number_format((int)($stats['order_count'] ?? 0)) ?></strong>
            <span>Đơn hàng</span>
        </div>
    </div>
    <div class="account-stat-card">
        <span class="account-stat-icon"><i class="bi bi-cash-stack"></i></span>
        <div>
            <strong><?= format_vnd((float)($stats['total_spent'] ?? 0)) ?></strong>
            <span>Đã chi tiêu</span>
        </div>
    </div>
    <div class="account-stat-card">
        <span class="account-stat-icon"><i class="bi bi-ticket-perforated"></i></span>
        <div>
            <strong><?= format_vnd((float)($stats['total_savings'] ?? 0)) ?></strong>
            <span>Đã tiết kiệm</span>
        </div>
    </div>
    <a href="<?= url('/my-wishlist') ?>" class="account-stat-card account-stat-link">
        <span class="account-stat-icon"><i class="bi bi-heart"></i></span>
        <div>
            <strong><?= number_format((int)($stats['wishlist_count'] ?? 0)) ?></strong>
            <span>Yêu thích</span>
        </div>
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel-card account-panel h-100">
            <div class="panel-header account-panel-header">
                <div>
                    <h2>Thông tin cá nhân</h2>
                    <p>Dùng cho liên hệ và giao hàng khi đặt mua sản phẩm.</p>
                </div>
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <form action="<?= url('/profile') ?>" method="post" class="account-form">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="full_name">Họ tên</label>
                    <input id="full_name" name="full_name" class="form-control"
                           value="<?= old('full_name', $user['full_name'] ?? '') ?>" required>
                    <?php if (errors('full_name')): ?>
                        <div class="text-danger small mt-1"><?= e(errors('full_name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" disabled>
                    <div class="form-text">Email dùng để đăng nhập, hiện chưa cho đổi trực tiếp.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">Số điện thoại</label>
                    <input id="phone" name="phone" class="form-control"
                           value="<?= old('phone', $user['phone'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label" for="address">Địa chỉ</label>
                    <textarea id="address" name="address" rows="4" class="form-control"><?= old('address', $user['address'] ?? '') ?></textarea>
                </div>

                <div class="account-form-actions">
                    <button class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel-card account-panel h-100">
            <div class="panel-header account-panel-header">
                <div>
                    <h2>Bảo mật</h2>
                    <p>Đổi mật khẩu định kỳ để bảo vệ đơn hàng và thông tin cá nhân.</p>
                </div>
                <i class="bi bi-shield-lock"></i>
            </div>
            <form action="<?= url('/profile/password') ?>" method="post" class="account-form">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                    <div class="input-group">
                        <input id="current_password" name="current_password" type="password" class="form-control" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="current_password" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Mật khẩu mới</label>
                    <div class="input-group">
                        <input id="password" name="password" type="password" class="form-control" minlength="6" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Nhập lại mật khẩu mới</label>
                    <div class="input-group">
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="6" required>
                        <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="account-form-actions">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-key me-1"></i> Đổi mật khẩu
                    </button>
                </div>
            </form>
            <?php clearFormState(); ?>
        </section>
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
