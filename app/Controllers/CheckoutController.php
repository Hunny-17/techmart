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
use App\Models\OrderEmailLog;
use App\Models\Voucher;
use App\Services\OrderCustomerNotifier;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $items = $this->buildItems(Session::get('cart', []));
        if ($items === []) {
            Flash::set('error', 'Giỏ hàng đang trống.');
            $this->redirect('/cart');
        }

        $subtotal = array_sum(array_map(static fn($item) => $item['subtotal'], $items));
        $this->view('cart/checkout', [
            'title'                => 'Đặt hàng',
            'items'                => $items,
            'total'                => $subtotal,
            'user'                 => Auth::user(),
            'paymentReferenceCode' => $this->paymentReferenceCode(),
            'availableVouchers'    => (new Voucher())->availableForCustomer((float)$subtotal),
        ]);
    }

    public function placeOrder(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'shipping_address' => ['required', 'max:1000'],
            'phone'            => ['required'],
            'payment_method'   => ['required', 'in:cod,bank_transfer,e_wallet'],
            'note'             => ['max:1000'],
            'voucher_code'     => [],
        ]);

        $cart = Session::get('cart', []);
        if ($cart === []) {
            Flash::set('error', 'Giỏ hàng đang trống.');
            $this->redirect('/cart');
        }

        $db = Database::pdo();

        try {
            $db->beginTransaction();

            $items = $this->buildItemsForCheckout($cart);
            if ($items === []) {
                throw new \RuntimeException('Giỏ hàng không hợp lệ.');
            }

            $subtotal = array_sum(array_map(static fn($item) => $item['subtotal'], $items));

            // Validate và áp dụng voucher
            $voucherId      = null;
            $discountAmount = 0.0;
            $voucherCode    = strtoupper(trim($data['voucher_code'] ?? ''));
            if ($voucherCode !== '') {
                $vResult = (new Voucher())->validate($voucherCode, $subtotal);
                if (!$vResult['valid']) {
                    throw new \RuntimeException('Voucher không hợp lệ: ' . $vResult['error']);
                }
                $voucherId      = (int)$vResult['voucher']['id'];
                $discountAmount = (float)$vResult['discount'];
            }
            $total = $subtotal - $discountAmount;

            $paymentReferenceCode = $data['payment_method'] === 'cod'
                ? null
                : $this->paymentReferenceCode();
            $paymentStatus = $data['payment_method'] === 'cod' ? 'unpaid' : 'awaiting_review';

            $orderStmt = $db->prepare("
                INSERT INTO orders (user_id, total_amount, voucher_id, discount_amount, status, shipping_address, payment_method, payment_reference_code, payment_status, note)
                VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)
            ");
            $orderStmt->execute([
                Auth::id(),
                $total,
                $voucherId,
                $discountAmount,
                $data['shipping_address'],
                $data['payment_method'],
                $paymentReferenceCode,
                $paymentStatus,
                $data['note'] ?? '',
            ]);

            $orderId = (int)$db->lastInsertId();

            $profileStmt = $db->prepare("
                UPDATE users
                SET phone = ?, address = ?
                WHERE id = ?
            ");
            $profileStmt->execute([
                $data['phone'],
                $data['shipping_address'],
                Auth::id(),
            ]);

            $detailStmt = $db->prepare("
                INSERT INTO order_details (order_id, product_id, variant_id, quantity, unit_price)
                VALUES (?, ?, ?, ?, ?)
            ");
            $productStockStmt = $db->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");
            $variantStockStmt = $db->prepare("
                UPDATE product_variants
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");

            foreach ($items as $item) {
                $detailStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['variant_id'],
                    $item['quantity'],
                    $item['price'],
                ]);

                if ($item['variant_id'] !== null) {
                    $variantStockStmt->execute([$item['quantity'], $item['variant_id'], $item['quantity']]);
                    if ($variantStockStmt->rowCount() !== 1) {
                        throw new \RuntimeException('Mẫu "' . $item['variant_name'] . '" không đủ tồn kho.');
                    }
                } else {
                    $productStockStmt->execute([$item['quantity'], $item['id'], $item['quantity']]);
                    if ($productStockStmt->rowCount() !== 1) {
                        throw new \RuntimeException('Sản phẩm "' . $item['name'] . '" không đủ tồn kho.');
                    }
                }
            }

            if ($voucherId !== null) {
                (new Voucher())->incrementUsed($voucherId);
            }

            $db->commit();
            Session::forget('cart');
            Session::forget('payment_reference_code');
            $this->sendOrderPlacedEmail($orderId);

            Flash::set('success', 'Đặt hàng thành công. Mã đơn hàng #' . $orderId . '.');
            $this->redirect('/my-orders/' . $orderId);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Flash::set('error', $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    private function parseCartKey(string|int $key): array
    {
        $parts = explode(':', (string)$key, 2);
        return [
            'product_id' => (int)($parts[0] ?? 0),
            'variant_id' => isset($parts[1]) && (int)$parts[1] > 0 ? (int)$parts[1] : null,
        ];
    }

    private function paymentReferenceCode(): string
    {
        $existing = Session::get('payment_reference_code');
        if (is_string($existing) && preg_match('/^TM[0-9]{8}[A-Z0-9]{6}$/', $existing)) {
            return $existing;
        }

        $code = 'TM' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        Session::set('payment_reference_code', $code);

        return $code;
    }

    private function buildItems(array $cart): array
    {
        if ($cart === []) {
            return [];
        }

        $stmt = Database::pdo()->prepare("
            SELECT p.id, p.name, p.price, p.image_url, p.stock_quantity,
                   v.id AS variant_id, v.name AS variant_name, v.price AS variant_price,
                   v.stock_quantity AS variant_stock, v.image_url AS variant_image
            FROM products p
            LEFT JOIN product_variants v ON v.id = ? AND v.product_id = p.id AND v.status = 'active'
            WHERE p.id = ? AND p.status = 'active'
            LIMIT 1
        ");

        $items = [];
        foreach ($cart as $key => $qty) {
            $parsed = $this->parseCartKey($key);
            $stmt->execute([$parsed['variant_id'], $parsed['product_id']]);
            $row = $stmt->fetch();
            if (!$row) {
                continue;
            }
            if ($parsed['variant_id'] !== null && empty($row['variant_id'])) {
                continue;
            }

            $price = $row['variant_id'] ? (float)$row['variant_price'] : (float)$row['price'];
            $stock = $row['variant_id'] ? (int)$row['variant_stock'] : (int)$row['stock_quantity'];
            $quantity = max(1, (int)$qty);

            $items[] = [
                'id' => (int)$row['id'],
                'variant_id' => $row['variant_id'] ? (int)$row['variant_id'] : null,
                'name' => $row['name'],
                'variant_name' => $row['variant_name'],
                'price' => $price,
                'image_url' => $row['variant_image'] ?: $row['image_url'],
                'stock_quantity' => $stock,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
            ];
        }

        return $items;
    }

    private function buildItemsForCheckout(array $cart): array
    {
        $items = $this->buildItems($cart);
        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $name = $item['variant_name'] ?: $item['name'];
                throw new \RuntimeException('Sản phẩm "' . $name . '" không đủ tồn kho.');
            }
        }

        return $items;
    }

    private function sendOrderPlacedEmail(int $orderId): void
    {
        try {
            $order = (new Order())->withCustomer($orderId);
            if ($order === null) {
                return;
            }

            $items = (new OrderDetail())->byOrder($orderId);
            $result = (new OrderCustomerNotifier())->orderPlaced($order, $items);
            $this->recordEmailLog($orderId, $result);

            if (!$result['sent']) {
                Flash::set('warning', 'Đơn đã đặt thành công nhưng email xác nhận chưa gửi được.');
            }
        } catch (\Throwable $e) {
            error_log('Order placed email failed: ' . $e->getMessage());
            $this->recordEmailLog($orderId, [
                'sent' => false,
                'recipient' => '',
                'subject' => 'Order placed confirmation',
                'status' => 'order_placed',
                'mail_file' => null,
                'error_message' => $e->getMessage(),
            ]);
            Flash::set('warning', 'Đơn đã đặt thành công nhưng email xác nhận bị lỗi.');
        }
    }

    private function recordEmailLog(int $orderId, array $result): void
    {
        (new OrderEmailLog())->create([
            'order_id' => $orderId,
            'recipient' => (string)($result['recipient'] ?? ''),
            'subject' => (string)($result['subject'] ?? ''),
            'status' => (string)($result['status'] ?? ''),
            'send_status' => !empty($result['sent']) ? 'sent' : 'failed',
            'mail_file' => $this->relativeMailPath($result['mail_file'] ?? null),
            'error_message' => $result['error_message'] ?? null,
        ]);
    }

    private function relativeMailPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $root = str_replace('\\', '/', dirname(__DIR__, 2));
        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, $root . '/')) {
            return substr($normalized, strlen($root) + 1);
        }

        return $normalized;
    }
}
