<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$basePath = parse_url(url('/'), PHP_URL_PATH) ?: '';
$adminPath = '/' . trim(substr($currentPath, strlen(rtrim($basePath, '/'))), '/');
$adminPath = $adminPath === '/' ? '/admin' : $adminPath;

$navItems = [
    ['/admin', 'Dashboard', 'bi-speedometer2'],
    ['/admin/categories', 'Danh mục', 'bi-tags'],
    ['/admin/products', 'Sản phẩm', 'bi-box-seam'],
    ['/admin/inventory', 'Tồn kho', 'bi-boxes'],
    ['/admin/orders', 'Đơn hàng', 'bi-receipt'],
    ['/admin/customers', 'Khách hàng', 'bi-person-lines-fill'],
    ['/admin/employees', 'Nhân viên', 'bi-people'],
    ['/admin/reviews',  'Đánh giá',          'bi-star'],
    ['/admin/vouchers', 'Voucher',            'bi-ticket-perforated'],
    ['/admin/logs',     'Lịch sử thao tác',  'bi-clock-history'],
];

$isActive = static function (string $path) use ($adminPath): bool {
    if ($path === '/admin') {
        return $adminPath === '/admin';
    }

    return $adminPath === $path || str_starts_with($adminPath, $path . '/');
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> - TechMart Admin</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary admin-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= url('/admin') ?>">
            <i class="bi bi-shield-lock me-1"></i> TechMart Admin
        </a>
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 text-white">
            <span class="admin-user-label">
                <i class="bi bi-person-circle me-1"></i> <?= e(\App\Core\Auth::user()['full_name']) ?>
            </span>
            <a href="<?= url('/') ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-house me-1"></i> Trang chủ
            </a>
            <form action="<?= url('/logout') ?>" method="post" class="m-0">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 bg-white border-end min-vh-100 p-0 admin-sidebar">
            <div class="list-group list-group-flush">
                <?php foreach ($navItems as [$path, $label, $icon]): ?>
                    <a class="list-group-item list-group-item-action <?= $isActive($path) ? 'active' : '' ?>"
                       href="<?= url($path) ?>">
                        <i class="bi <?= e($icon) ?>"></i> <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="col-md-10 p-4">
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <?php /** @var string $content */ echo $content; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" nonce="<?= csp_nonce() ?>"></script>
<script src="<?= asset('js/app.js') ?>" nonce="<?= csp_nonce() ?>"></script>
</body>
</html>
