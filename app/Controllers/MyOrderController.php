<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Session;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Voucher;

final class MyOrderController extends Controller
{
    public function index(): void
    {
        $validStatuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
        $status = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : null;
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $userId = (int)Auth::id();
        $order  = new Order();
        $result = $order->paginateByUser($userId, $page, 8, $status);
        $statusCounts = $order->statusCountsByUser($userId);

        $this->view('my-orders/index', [
            'title'        => 'Đơn hàng của tôi',
            'result'       => $result,
            'status'       => $status,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(int $id): void
    {
        $order = (new Order())->find($id);
        if ($order === null || (int)$order['user_id'] !== Auth::id()) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/my-orders');
        }

        $items = (new OrderDetail())->byOrder($id);

        $this->view('my-orders/show', [
            'title' => 'Đơn hàng #' . $id,
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function reorder(int $id): void
    {
        Csrf::verify();

        $order = (new Order())->find($id);
        if ($order === null || (int)$order['user_id'] !== Auth::id()) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/my-orders');
        }

        $items = (new OrderDetail())->byOrder($id);
        if (empty($items)) {
            Flash::set('error', 'Đơn hàng không có sản phẩm.');
            $this->redirect('/my-orders/' . $id);
        }

        $cart = Session::get('cart', []);
        $productModel = new Product();
        $variantModel = new ProductVariant();

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $variantId = (int)($item['variant_id'] ?? 0);
            $quantity = max(1, (int)$item['quantity']);

            $product = $productModel->find($productId);
            if ($product === null || ($product['status'] ?? '') !== 'active') {
                Flash::set('error', 'Một sản phẩm trong đơn cũ hiện không còn bán.');
                $this->redirect('/my-orders/' . $id);
            }

            $stock = (int)$product['stock_quantity'];
            if ($variantId > 0) {
                $variant = $variantModel->findForProduct($variantId, $productId);
                if ($variant === null || ($variant['status'] ?? '') !== 'active') {
                    Flash::set('error', 'Một mẫu sản phẩm trong đơn cũ hiện không còn bán.');
                    $this->redirect('/my-orders/' . $id);
                }
                $stock = (int)$variant['stock_quantity'];
            }

            $key = $productId . ':' . $variantId;
            $nextQty = ($cart[$key] ?? 0) + $quantity;
            if ($nextQty > $stock) {
                Flash::set('error', 'Số lượng mua lại vượt quá tồn kho hiện có.');
                $this->redirect('/my-orders/' . $id);
            }

            $cart[$key] = $nextQty;
        }
        Session::set('cart', $cart);

        Flash::set('success', 'Đã thêm ' . count($items) . ' sản phẩm vào giỏ hàng.');
        $this->redirect('/cart');
    }

    public function cancel(int $id): void
    {
        Csrf::verify();

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if ($order === null || (int)$order['user_id'] !== Auth::id()) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/my-orders');
        }

        if ($order['status'] !== 'pending') {
            Flash::set('error', 'Chỉ có thể hủy đơn hàng đang chờ xử lý.');
            $this->redirect('/my-orders/' . $id);
        }

        $db = Database::pdo();
        try {
            $db->beginTransaction();

            $items = (new OrderDetail())->byOrder($id);
            foreach ($items as $item) {
                if (!empty($item['variant_id'])) {
                    $stmt = $db->prepare("
                        UPDATE product_variants
                        SET stock_quantity = stock_quantity + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$item['quantity'], (int)$item['variant_id']]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE products
                        SET stock_quantity = stock_quantity + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$item['quantity'], (int)$item['product_id']]);
                }
            }

            if (!empty($order['voucher_id'])) {
                (new Voucher())->decrementUsed((int)$order['voucher_id']);
            }

            $orderModel->changeStatus($id, 'cancelled');
            $db->commit();

            Flash::set('success', 'Đã hủy đơn hàng và hoàn lại tồn kho.');
            $this->redirect('/my-orders/' . $id);
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Flash::set('error', 'Không thể hủy đơn hàng lúc này.');
            $this->redirect('/my-orders/' . $id);
        }
    }
}
