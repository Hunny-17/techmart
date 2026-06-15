<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'TechMart') ?> - TechMart</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg store-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('/') ?>">
            <i class="bi bi-bag-check-fill me-1"></i> TechMart
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('/products') ?>">Sản phẩm</a></li>
            </ul>

            <form class="store-nav-search d-flex me-3" action="<?= url('/products') ?>" method="get">
                <div class="position-relative me-2">
                    <input class="form-control form-control-sm"
                           id="nav-search-input"
                           name="q" placeholder="Tìm sản phẩm..."
                           value="<?= e($_GET['q'] ?? '') ?>"
                           autocomplete="off">
                    <div id="search-dropdown" class="search-autocomplete-dropdown" style="display:none"></div>
                </div>
                <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <ul class="navbar-nav">
                <?php if (\App\Core\Auth::check()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= url('/my-wishlist') ?>">
                            <i class="bi bi-heart"></i> Yêu thích
                            <span id="wishlist-badge"
                                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  style="display:none">0</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= url('/cart') ?>">
                        <i class="bi bi-cart3"></i> Giỏ hàng
                        <span id="cart-badge"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="display:none">0</span>
                    </a>
                </li>

                <?php if (\App\Core\Auth::check()): ?>
                    <li class="nav-item dropdown">
                        <?php
                            $_nm = \App\Core\Auth::user()['full_name'] ?? '';
                            $_words = preg_split('/\s+/', trim($_nm));
                            $_initials = mb_strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1, 'UTF-8'), array_slice($_words, 0, 2))), 'UTF-8');
                        ?>
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="nav-avatar" aria-hidden="true"><?= e($_initials) ?></span>
                            <?= e($_nm) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= url('/admin') ?>">Trang quản trị</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= url('/profile') ?>">Tài khoản của tôi</a></li>
                            <li><a class="dropdown-item" href="<?= url('/my-orders') ?>">Đơn hàng của tôi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?= url('/logout') ?>" method="post" class="m-0">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item" type="submit">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/login') ?>">Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/register') ?>">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <?php /** @var string $content */ echo $content; ?>
</main>

<footer class="store-footer mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="store-footer-brand" href="<?= url('/') ?>">
                    <i class="bi bi-bag-check-fill"></i> TechMart
                </a>
                <p class="store-footer-text">
                    TechMart cung cấp thiết bị công nghệ chính hãng, phụ kiện và giải pháp mua sắm trực tuyến
                    dành cho khách hàng cá nhân, văn phòng và cửa hàng nhỏ.
                </p>
                <div class="store-footer-social">
                    <a href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.youtube.com/" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="https://www.instagram.com/" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-md-6 col-lg-2">
                <h6>Hỗ trợ</h6>
                <ul class="store-footer-links">
                    <li><a href="<?= url('/products') ?>">Sản phẩm</a></li>
                    <li><a href="<?= url('/cart') ?>">Giỏ hàng</a></li>
                    <li><a href="<?= url('/my-orders') ?>">Theo dõi đơn hàng</a></li>
                    <li><a href="<?= url('/profile') ?>">Tài khoản</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-lg-3">
                <h6>Liên hệ</h6>
                <ul class="store-footer-contact">
                    <li><i class="bi bi-geo-alt"></i> 123 Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh</li>
                    <li><i class="bi bi-telephone"></i> 0901 234 567</li>
                    <li><i class="bi bi-envelope"></i> support@techmart.test</li>
                    <li><i class="bi bi-clock"></i> 08:00 - 21:00, Thứ 2 - Chủ nhật</li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Bản đồ cửa hàng</h6>
                <div class="store-map">
                    <iframe
                        title="Bản đồ TechMart"
                        src="https://www.google.com/maps?q=123%20Nguyen%20Trai%2C%20District%201%2C%20Ho%20Chi%20Minh%20City&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="store-footer-bottom">
            <span>&copy; <?= date('Y') ?> TechMart. All rights reserved.</span>
            <span>Website thương mại điện tử demo phục vụ học tập và phát triển sản phẩm.</span>
        </div>
    </div>
</footer>

<div id="compare-bar" class="compare-bar" aria-label="So sánh sản phẩm">
    <div class="container compare-bar-inner">
        <div class="compare-slots">
            <div class="compare-slot" data-slot="0"></div>
            <div class="compare-slot" data-slot="1"></div>
            <div class="compare-slot" data-slot="2"></div>
        </div>
        <div class="compare-bar-actions">
            <a href="#" class="btn btn-primary btn-sm compare-bar-go">
                <i class="bi bi-columns-gap me-1"></i> So sánh ngay
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm compare-bar-clear" title="Xóa tất cả">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

<button id="back-to-top" aria-label="Lên đầu trang">
    <i class="bi bi-chevron-up"></i>
</button>

<script nonce="<?= csp_nonce() ?>">window.APP_URL = '<?= rtrim(e(\App\Core\App::$config['app']['url'] ?? ''), '/') ?>'; window.CSRF_TOKEN = '<?= e(\App\Core\Csrf::token()) ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" nonce="<?= csp_nonce() ?>"></script>
<script src="<?= asset('js/app.js') ?>" nonce="<?= csp_nonce() ?>"></script>
<script src="<?= asset('js/cookie-banner.js') ?>" nonce="<?= csp_nonce() ?>"></script>

<div id="cookie-banner" role="dialog" aria-live="polite" style="
    position:fixed;bottom:0;left:0;right:0;z-index:9999;
    background:#0F172A;color:#fff;
    padding:1rem 1.5rem;
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;
    transform:translateY(100%);opacity:0;
    transition:transform .4s ease,opacity .4s ease;
    box-shadow:0 -2px 16px rgba(0,0,0,.3);
">
    <p style="margin:0;font-size:.9rem;flex:1;min-width:200px">
        <i class="bi bi-shield-lock-fill" style="color:#EF4444;margin-right:.4rem"></i>
        TechMart dùng <strong>cookie</strong> để cải thiện trải nghiệm mua sắm của bạn.
        Cookie phiên làm việc giúp duy trì đăng nhập và giỏ hàng.
    </p>
    <div style="display:flex;gap:.5rem;flex-shrink:0">
        <button id="cookie-decline" style="
            background:transparent;border:1px solid rgba(255,255,255,.3);
            color:#fff;padding:.4rem .9rem;border-radius:6px;cursor:pointer;font-size:.85rem;
        ">Từ chối</button>
        <button id="cookie-accept" style="
            background:#EF4444;border:none;
            color:#fff;padding:.4rem .9rem;border-radius:6px;cursor:pointer;font-size:.85rem;font-weight:600;
        ">Chấp nhận</button>
    </div>
</div>

<style>
#cookie-banner.cookie-banner--visible {
    transform: translateY(0);
    opacity: 1;
}
</style>
</body>
</html>
