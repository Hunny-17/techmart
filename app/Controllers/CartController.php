<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Session;
use App\Models\Product;
use App\Models\ProductVariant;

final class CartController extends Controller
{
    public function index(): void
    {
        $cart = Session::get('cart', []);
        $items = $this->buildItems($cart);
        $total = array_sum(array_map(static fn($i) => $i['subtotal'], $items));

        $this->view('cart/index', [
            'title' => 'Giỏ hàng',
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(): void
    {
        Csrf::verify();
        $id = (int)($_POST['product_id'] ?? 0);
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $product = (new Product())->find($id);
        if ($product === null || $product['status'] !== 'active') {
            Flash::set('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/products');
        }

        $variant = $variantId > 0 ? (new ProductVariant())->findForProduct($variantId, $id) : null;
        if ($variantId > 0 && ($variant === null || $variant['status'] !== 'active')) {
            Flash::set('error', 'Mẫu sản phẩm không hợp lệ.');
            $this->redirect('/products/' . $id);
        }

        $key = $this->cartKey($id, $variantId > 0 ? $variantId : null);
        $cart = Session::get('cart', []);
        $stock = $variantId > 0 ? (int)$variant['stock_quantity'] : (int)$product['stock_quantity'];
        $nextQty = ($cart[$key] ?? 0) + $qty;
        if ($nextQty > $stock) {
            Flash::set('error', 'Số lượng vượt quá tồn kho hiện có.');
            $this->redirect('/products/' . $id);
        }

        $cart[$key] = $nextQty;
        Session::set('cart', $cart);

        Flash::set('success', 'Đã thêm vào giỏ hàng.');
        $this->redirect('/cart');
    }

    public function update(): void
    {
        Csrf::verify();
        $key = (string)($_POST['cart_key'] ?? '');
        $qty = (int)($_POST['quantity'] ?? 0);

        $cart = Session::get('cart', []);
        if (!array_key_exists($key, $cart)) {
            Flash::set('error', 'Dòng sản phẩm trong giỏ hàng không hợp lệ.');
            $this->redirect('/cart');
        }

        if ($qty <= 0) {
            unset($cart[$key]);
            Session::set('cart', $cart);
            Flash::set('success', 'Đã cập nhật giỏ hàng.');
            $this->redirect('/cart');
        }

        $item = $this->findCartItem($key);
        if ($item === null) {
            unset($cart[$key]);
            Session::set('cart', $cart);
            Flash::set('error', 'Sản phẩm hoặc mẫu sản phẩm không còn khả dụng.');
            $this->redirect('/cart');
        }

        if ($qty > (int)$item['stock_quantity']) {
            Flash::set('error', 'Số lượng "' . $item['display_name'] . '" vượt quá tồn kho hiện có.');
            $this->redirect('/cart');
        }

        if ($key !== '') {
            $cart[$key] = $qty;
        }
        Session::set('cart', $cart);

        Flash::set('success', 'Đã cập nhật giỏ hàng.');
        $this->redirect('/cart');
    }

    public function remove(): void
    {
        Csrf::verify();
        $key = (string)($_POST['cart_key'] ?? '');
        $cart = Session::get('cart', []);
        unset($cart[$key]);
        Session::set('cart', $cart);

        Flash::set('success', 'Đã xóa khỏi giỏ hàng.');
        $this->redirect('/cart');
    }

    public function count(): void
    {
        $cart = Session::get('cart', []);
        $this->json(['count' => array_sum($cart)]);
    }

    private function cartKey(int $productId, ?int $variantId = null): string
    {
        return $productId . ':' . ($variantId ?? 0);
    }

    private function parseCartKey(string|int $key): array
    {
        $parts = explode(':', (string)$key, 2);
        return [
            'product_id' => (int)($parts[0] ?? 0),
            'variant_id' => isset($parts[1]) && (int)$parts[1] > 0 ? (int)$parts[1] : null,
        ];
    }

    private function buildItems(array $cart): array
    {
        if ($cart === []) {
            return [];
        }

        $items = [];
        $stmt = Database::pdo()->prepare("
            SELECT p.id, p.name, p.price, p.image_url, p.stock_quantity,
                   v.id AS variant_id, v.name AS variant_name, v.price AS variant_price,
                   v.stock_quantity AS variant_stock, v.image_url AS variant_image
            FROM products p
            LEFT JOIN product_variants v ON v.id = ? AND v.product_id = p.id AND v.status = 'active'
            WHERE p.id = ? AND p.status = 'active'
            LIMIT 1
        ");

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
            $image = $row['variant_image'] ?: $row['image_url'];
            $quantity = max(1, (int)$qty);

            $items[] = [
                'cart_key' => (string)$key,
                'id' => (int)$row['id'],
                'variant_id' => $row['variant_id'] ? (int)$row['variant_id'] : null,
                'name' => $row['name'],
                'variant_name' => $row['variant_name'],
                'price' => $price,
                'image_url' => $image,
                'stock_quantity' => $stock,
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
            ];
        }

        return $items;
    }

    private function findCartItem(string $key): ?array
    {
        $items = $this->buildItems([$key => 1]);
        if ($items === []) {
            return null;
        }

        $item = $items[0];
        $item['display_name'] = $item['variant_name'] ?: $item['name'];

        return $item;
    }
}
