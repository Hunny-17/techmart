<?php
declare(strict_types=1);

/**
 * Khai báo route
 *
 * Format: $router->verb(path, [Controller::class, 'method'], ?middleware)
 *
 * Path hỗ trợ tham số động: '/products/{id}' → $id là đối số method
 * Middleware: 'auth' (cần đăng nhập) | 'admin' (cần là admin)
 */

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\MyOrderController;
use App\Controllers\ProfileController;
use App\Controllers\ReviewController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ReviewController as AdminReviewController;
use App\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\AdminLogController;
use App\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Controllers\WishlistController;
use App\Controllers\VoucherController;
use App\Controllers\CompareController;
use App\Controllers\Admin\VoucherController as AdminVoucherController;

/** @var App\Core\Router $router */

// ===== Public =====
$router->get('/',              [HomeController::class, 'index']);
$router->get('/compare',          [CompareController::class, 'index']);
$router->get('/products',         [HomeController::class, 'products']);
$router->get('/products/suggest', [HomeController::class, 'suggest']); // AJAX — phải đứng trước {id}
$router->get('/products/{id}',    [HomeController::class, 'show']);
$router->get('/products/{id}/review',  [ReviewController::class, 'create'], 'auth');
$router->post('/products/{id}/review', [ReviewController::class, 'store'],  'auth');

// Auth
$router->get('/login',     [AuthController::class, 'loginForm']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
$router->get('/reset-password', [AuthController::class, 'resetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/register',  [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/logout',   [AuthController::class, 'logout']);

// Cart (session-based, có thể truy cập khi chưa login)
$router->get('/cart',         [CartController::class, 'index']);
$router->post('/cart/add',    [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->get('/cart/count',   [CartController::class, 'count']); // AJAX

// Wishlist
$router->get('/my-wishlist',      [WishlistController::class, 'index'],  'auth');
$router->get('/wishlist/count',   [WishlistController::class, 'count']); // AJAX
$router->post('/wishlist/toggle', [WishlistController::class, 'toggle']); // AJAX

// Voucher
$router->get('/voucher/validate', [VoucherController::class, 'check']); // AJAX

// Checkout + don hang cua khach
$router->get('/checkout',       [CheckoutController::class, 'index'],      'auth');
$router->post('/checkout',      [CheckoutController::class, 'placeOrder'], 'auth');
$router->get('/profile',        [ProfileController::class, 'edit'],        'auth');
$router->post('/profile',       [ProfileController::class, 'update'],      'auth');
$router->post('/profile/password', [ProfileController::class, 'updatePassword'], 'auth');
$router->get('/my-orders',      [MyOrderController::class, 'index'],       'auth');
$router->get('/my-orders/{id}', [MyOrderController::class, 'show'],        'auth');
$router->post('/my-orders/{id}/cancel',  [MyOrderController::class, 'cancel'],  'auth');
$router->post('/my-orders/{id}/reorder', [MyOrderController::class, 'reorder'], 'auth');

// ===== Admin =====
$router->get('/admin', [DashboardController::class, 'index'], 'admin');

// Quan ly danh muc
$router->get('/admin/categories',              [AdminCategoryController::class, 'index'],   'admin');
$router->get('/admin/categories/create',       [AdminCategoryController::class, 'create'],  'admin');
$router->post('/admin/categories',             [AdminCategoryController::class, 'store'],   'admin');
$router->get('/admin/categories/{id}/edit',    [AdminCategoryController::class, 'edit'],    'admin');
$router->post('/admin/categories/{id}',        [AdminCategoryController::class, 'update'],  'admin');
$router->post('/admin/categories/{id}/delete', [AdminCategoryController::class, 'destroy'], 'admin');

// Quản lý sản phẩm
$router->get('/admin/products',             [AdminProductController::class, 'index'],   'admin');
$router->get('/admin/products/create',      [AdminProductController::class, 'create'],  'admin');
$router->post('/admin/products',            [AdminProductController::class, 'store'],   'admin');
$router->get('/admin/products/{id}/stock',  [AdminProductController::class, 'stock'],   'admin');
$router->post('/admin/products/{id}/stock', [AdminProductController::class, 'updateStock'], 'admin');
$router->get('/admin/products/{id}/edit',   [AdminProductController::class, 'edit'],    'admin');
$router->post('/admin/products/{id}',       [AdminProductController::class, 'update'],  'admin');
$router->post('/admin/products/{id}/delete',[AdminProductController::class, 'destroy'], 'admin');

// Quan ly ton kho
$router->get('/admin/inventory', [AdminInventoryController::class, 'index'], 'admin');

// Quan ly don hang
$router->get('/admin/orders',              [AdminOrderController::class, 'index'],        'admin');
$router->get('/admin/orders/export',       [AdminOrderController::class, 'export'],       'admin');
$router->get('/admin/orders/export-preview', [AdminOrderController::class, 'exportPreview'], 'admin');
$router->get('/admin/orders/{id}/invoice', [AdminOrderController::class, 'invoice'],      'admin');
$router->get('/admin/orders/{id}/emails/{logId}', [AdminOrderController::class, 'email'], 'admin');
$router->get('/admin/orders/{id}',         [AdminOrderController::class, 'show'],         'admin');
$router->post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'], 'admin');
$router->post('/admin/orders/{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'], 'admin');

// Quan ly danh gia
$router->get('/admin/reviews',              [AdminReviewController::class, 'index'],   'admin');
$router->post('/admin/reviews/{id}/hide',   [AdminReviewController::class, 'hide'],    'admin');
$router->post('/admin/reviews/{id}/show',   [AdminReviewController::class, 'show'],    'admin');
$router->post('/admin/reviews/{id}/delete', [AdminReviewController::class, 'destroy'], 'admin');

// Quan ly nhan vien
$router->get('/admin/employees',              [AdminEmployeeController::class, 'index'],   'admin');
$router->get('/admin/employees/create',       [AdminEmployeeController::class, 'create'],  'admin');
$router->post('/admin/employees',             [AdminEmployeeController::class, 'store'],   'admin');
$router->post('/admin/employees/{id}/lock',   [AdminEmployeeController::class, 'lock'],    'admin');
$router->post('/admin/employees/{id}/unlock', [AdminEmployeeController::class, 'unlock'],  'admin');
$router->post('/admin/employees/{id}/delete', [AdminEmployeeController::class, 'destroy'], 'admin');

// Lich su thao tac admin
$router->get('/admin/logs', [AdminLogController::class, 'index'], 'admin');

// Quan ly khach hang
$router->get('/admin/customers',              [AdminCustomerController::class, 'index'],  'admin');
$router->get('/admin/customers/export',       [AdminCustomerController::class, 'export'], 'admin');
$router->get('/admin/customers/{id}',         [AdminCustomerController::class, 'show'],   'admin');
$router->post('/admin/customers/{id}/resend-verification', [AdminCustomerController::class, 'resendVerification'], 'admin');
$router->post('/admin/customers/{id}/lock',   [AdminCustomerController::class, 'lock'],   'admin');
$router->post('/admin/customers/{id}/unlock', [AdminCustomerController::class, 'unlock'], 'admin');

// Quan ly voucher
$router->get('/admin/vouchers',              [AdminVoucherController::class, 'index'],   'admin');
$router->get('/admin/vouchers/create',       [AdminVoucherController::class, 'create'],  'admin');
$router->post('/admin/vouchers',             [AdminVoucherController::class, 'store'],   'admin');
$router->get('/admin/vouchers/{id}/edit',    [AdminVoucherController::class, 'edit'],    'admin');
$router->post('/admin/vouchers/{id}',        [AdminVoucherController::class, 'update'],  'admin');
$router->post('/admin/vouchers/{id}/delete', [AdminVoucherController::class, 'destroy'], 'admin');
$router->post('/admin/vouchers/{id}/toggle', [AdminVoucherController::class, 'toggleActive'], 'admin');

// TODO: Codex scaffold các module còn lại theo pattern Admin\ProductController:
// - Admin\EmployeeController  (route /admin/employees/...)
// - Admin\CustomerController  (route /admin/customers/...)
// - Admin\ReviewController    (route /admin/reviews/...)
// - Admin\OrderController     (route /admin/orders/...)
