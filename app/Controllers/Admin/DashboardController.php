<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $product = new Product();
        $user = new User();
        $order = new Order();

        $stats = [
            'products' => $product->count(),
            'customers' => $user->count(['role' => 'customer']),
            'staffs' => $user->count(['role' => 'staff']),
            'orders' => $order->count(),
            'pending_orders' => $order->count(['status' => 'pending']),
            'shipping_orders' => $order->count(['status' => 'shipping']),
            'low_stock' => $product->lowStockCount(),
            'revenue' => $order->deliveredRevenue(),
            'revenue_today' => $order->deliveredRevenueToday(),
            'revenue_month' => $order->deliveredRevenueThisMonth(),
            'new_customers_today' => $user->newCustomersToday(),
            'new_customers_month' => $user->newCustomersThisMonth(),
            'unverified_customers' => $user->unverifiedCustomersCount(),
        ];

        $this->view('admin/dashboard', [
            'title'              => 'Dashboard',
            'stats'              => $stats,
            'orderStatusCounts'  => $order->statusCounts(),
            'paymentStatusCounts' => $order->paymentStatusCounts(),
            'revenueLast7Days'   => $order->revenueLast7Days(),
            'ordersLast7Days'    => $order->ordersLast7Days(),
            'topProducts'        => $order->topProducts(5),
            'lowStockItems'      => $product->lowStockItems(8),
            'voucherStats'       => (new Voucher())->stats(),
            'totalDiscountGiven' => $order->totalDiscountGiven(),
        ], 'admin');
    }
}
