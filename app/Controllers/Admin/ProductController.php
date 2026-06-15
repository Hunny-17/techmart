<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Models\Category;
use App\Models\InventoryStockLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\AdminLogger;

final class ProductController extends Controller
{
    private const VARIANT_PRICE_MAX_MULTIPLIER = 3.0;

    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $stock = trim((string)($_GET['stock'] ?? ''));
        $result = (new Product())->adminPaginate(
            $page,
            10,
            $keyword !== '' ? $keyword : null,
            $status !== '' ? $status : null,
            $stock === 'low'
        );

        $this->view('admin/products/index', [
            'title' => 'Quản lý sản phẩm',
            'result' => $result,
            'filters' => [
                'q' => $keyword,
                'status' => $status,
                'stock' => $stock,
            ],
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin/products/create', [
            'title' => 'Thêm sản phẩm',
            'categories' => $this->loadCategories(),
        ], 'admin');
    }

    public function store(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'name' => ['required', 'max:200'],
            'category_id' => ['required', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['max:5000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $this->validateVariants($_POST, (float)$data['price']);

        $imageUrl = $this->handleUpload($_FILES['image'] ?? null);
        $productId = (new Product())->create([
            'category_id' => (int)$data['category_id'],
            'name' => $data['name'],
            'slug' => $this->slugify($data['name']),
            'description' => $data['description'] ?? '',
            'price' => (float)$data['price'],
            'stock_quantity' => (int)$data['stock_quantity'],
            'image_url' => $imageUrl,
            'status' => $data['status'],
        ]);

        $this->syncExtraImages($productId, $_POST['existing_extra_images'] ?? '', $_FILES['extra_images'] ?? null);
        $this->syncVariants($productId, $_POST, $_FILES['variant_images'] ?? null);

        Flash::set('success', 'Thêm sản phẩm thành công.');
        (new AdminLogger())->log('create', 'product', $productId, "Thêm sản phẩm mới: {$data['name']}");
        $this->redirect('/admin/products');
    }

    public function edit(int $id): void
    {
        $product = (new Product())->find($id);
        if ($product === null) {
            Flash::set('error', 'Không tìm thấy sản phẩm.');
            $this->redirect('/admin/products');
        }

        $this->view('admin/products/edit', [
            'title' => 'Sửa sản phẩm',
            'product' => $product,
            'categories' => $this->loadCategories(),
            'extraImages' => (new ProductImage())->byProduct($id),
            'variants' => (new ProductVariant())->byProduct($id),
        ], 'admin');
    }

    public function stock(int $id): void
    {
        $product = (new Product())->withCategory($id);
        if ($product === null) {
            Flash::set('error', 'Không tìm thấy sản phẩm.');
            $this->redirect('/admin/products');
        }

        $this->view('admin/products/stock', [
            'title' => 'Nhập kho sản phẩm',
            'product' => $product,
            'variants' => (new ProductVariant())->byProduct($id),
            'stockLogs' => (new InventoryStockLog())->byProduct($id),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Csrf::verify();

        $existing = (new Product())->find($id);
        if ($existing === null) {
            Flash::set('error', 'Không tìm thấy sản phẩm.');
            $this->redirect('/admin/products');
        }

        $data = $this->validate($_POST, [
            'name' => ['required', 'max:200'],
            'category_id' => ['required', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['max:5000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $this->validateVariants($_POST, (float)$data['price']);

        $payload = [
            'category_id' => (int)$data['category_id'],
            'name' => $data['name'],
            'slug' => $this->slugify($data['name']),
            'description' => $data['description'] ?? '',
            'price' => (float)$data['price'],
            'stock_quantity' => (int)$data['stock_quantity'],
            'status' => $data['status'],
        ];

        if (!empty($_FILES['image']['tmp_name'])) {
            $payload['image_url'] = $this->handleUpload($_FILES['image']);
        }

        (new Product())->update($id, $payload);
        $this->syncExtraImages($id, $_POST['existing_extra_images'] ?? '', $_FILES['extra_images'] ?? null);
        $this->syncVariants($id, $_POST, $_FILES['variant_images'] ?? null);

        Flash::set('success', 'Cập nhật sản phẩm thành công.');
        (new AdminLogger())->log('update', 'product', $id, "Cập nhật sản phẩm: {$data['name']}");
        $this->redirect('/admin/products');
    }

    public function updateStock(int $id): void
    {
        Csrf::verify();

        $productModel = new Product();
        $product = $productModel->find($id);
        if ($product === null) {
            Flash::set('error', 'Không tìm thấy sản phẩm.');
            $this->redirect('/admin/products');
        }

        $variantModel = new ProductVariant();
        $variants = $variantModel->byProduct($id);
        $productAdd = $this->parseRestockQuantity($_POST['product_quantity'] ?? '0', 'sản phẩm chính');
        $variantAdds = [];
        $totalAdded = $productAdd;

        foreach ($variants as $variant) {
            $variantId = (int)$variant['id'];
            $quantity = $this->parseRestockQuantity(
                $_POST['variant_quantities'][$variantId] ?? '0',
                'mẫu ' . $variant['name']
            );
            $variantAdds[$variantId] = $quantity;
            $totalAdded += $quantity;
        }

        if ($totalAdded <= 0) {
            Flash::set('warning', 'Nhập ít nhất một số lượng lớn hơn 0 để cộng kho.');
            $this->redirect('/admin/products/' . $id . '/stock');
        }

        $pdo = Database::pdo();
        $stockLog = new InventoryStockLog();
        $details = [];
        $adminId = Auth::id();
        if ($adminId === null) {
            Flash::set('error', 'Phiên đăng nhập admin không hợp lệ.');
            $this->redirect('/login');
        }

        try {
            $pdo->beginTransaction();

            if ($productAdd > 0) {
                $oldStock = (int)$product['stock_quantity'];
                $newStock = $oldStock + $productAdd;
                $productModel->update($id, ['stock_quantity' => $newStock]);
                $stockLog->create([
                    'product_id' => $id,
                    'variant_id' => null,
                    'admin_user_id' => $adminId,
                    'quantity' => $productAdd,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                ]);
                $details[] = 'chính +' . $productAdd . ' (' . $newStock . ')';
            }

            foreach ($variants as $variant) {
                $variantId = (int)$variant['id'];
                $quantity = $variantAdds[$variantId] ?? 0;
                if ($quantity <= 0) {
                    continue;
                }

                $oldStock = (int)$variant['stock_quantity'];
                $newStock = $oldStock + $quantity;
                $variantModel->update($variantId, ['stock_quantity' => $newStock]);
                $stockLog->create([
                    'product_id' => $id,
                    'variant_id' => $variantId,
                    'admin_user_id' => $adminId,
                    'quantity' => $quantity,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                ]);
                $details[] = $variant['name'] . ' +' . $quantity . ' (' . $newStock . ')';
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Flash::set('error', 'Lỗi nhập kho: ' . $e->getMessage());
            $this->redirect('/admin/products/' . $id . '/stock');
        }

        Flash::set('success', 'Đã nhập kho thành công: ' . implode(', ', $details) . '.');
        (new AdminLogger())->log('restock', 'product', $id, 'Nhập kho sản phẩm ' . $product['name'] . ': ' . implode(', ', $details));
        $this->redirect('/admin/products/' . $id . '/stock');
    }

    public function destroy(int $id): void
    {
        Csrf::verify();
        $product  = new Product();
        $existing = $product->find($id);
        if ($existing === null) {
            Flash::set('error', 'Không tìm thấy sản phẩm.');
            $this->redirect('/admin/products');
        }

        if ($product->hasOrderDetails($id)) {
            $product->deactivate($id);
            Flash::set('warning', 'Sản phẩm đã có lịch sử đơn hàng nên đã được chuyển sang ngừng bán thay vì xóa.');
            (new AdminLogger())->log('delete', 'product', $id, "Ngừng bán sản phẩm (có đơn hàng): {$existing['name']}");
            $this->redirect('/admin/products');
        }

        $product->delete($id);
        Flash::set('success', 'Đã xóa sản phẩm chưa phát sinh đơn hàng.');
        (new AdminLogger())->log('delete', 'product', $id, "Xoá sản phẩm: {$existing['name']}");
        $this->redirect('/admin/products');
    }

    private function loadCategories(): array
    {
        return (new Category())->options();
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace('đ', 'd', $text);
        $text = preg_replace('/[^a-z0-9\p{L}]+/u', '-', $text) ?? $text;
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9]+/', '-', strtolower($text)) ?? $text;
        return trim($text, '-');
    }

    private function handleUpload(?array $file): ?string
    {
        if ($file === null || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $cfg = \App\Core\App::$config['upload'];
        if (!is_dir($cfg['path'])) {
            mkdir($cfg['path'], 0775, true);
        }

        if ($file['size'] > $cfg['max_size']) {
            Flash::set('error', 'File quá lớn, tối đa 2MB.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $cfg['allowed'], true)) {
            Flash::set('error', 'Định dạng file không hỗ trợ.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'bin',
        };
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $target = $cfg['path'] . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            Flash::set('error', 'Lỗi upload file.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
        }

        return $cfg['public_url'] . '/' . $filename;
    }

    private function syncExtraImages(int $productId, string $raw, ?array $files = null): void
    {
        $model = new ProductImage();
        $model->deleteByProduct($productId);

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $sort = 0;
        foreach ($lines as $line) {
            $url = trim($line);
            if ($url === '') {
                continue;
            }
            $model->create([
                'product_id' => $productId,
                'image_url' => $url,
                'sort_order' => $sort++,
            ]);
        }

        foreach ($this->normalizeUploadFiles($files) as $file) {
            $url = $this->handleUpload($file);
            if ($url === null) {
                continue;
            }
            $model->create([
                'product_id' => $productId,
                'image_url' => $url,
                'sort_order' => $sort++,
            ]);
        }
    }

    private function syncVariants(int $productId, array $post, ?array $imageFiles = null): void
    {
        $model = new ProductVariant();
        $model->deleteByProduct($productId);

        $names = $post['variant_names'] ?? [];
        $prices = $post['variant_prices'] ?? [];
        $stocks = $post['variant_stocks'] ?? [];
        $existingImages = $post['variant_existing_images'] ?? [];
        $files = $this->normalizeUploadFiles($imageFiles);

        foreach ($names as $index => $name) {
            $name = trim((string)$name);
            if ($name === '') {
                continue;
            }

            $imageUrl = trim((string)($existingImages[$index] ?? ''));
            if (isset($files[$index]) && !empty($files[$index]['tmp_name'])) {
                $uploaded = $this->handleUpload($files[$index]);
                if ($uploaded !== null) {
                    $imageUrl = $uploaded;
                }
            }

            $model->create([
                'product_id' => $productId,
                'name' => $name,
                'price' => isset($prices[$index]) && $prices[$index] !== '' ? (float)$prices[$index] : null,
                'stock_quantity' => isset($stocks[$index]) && $stocks[$index] !== '' ? (int)$stocks[$index] : 0,
                'image_url' => $imageUrl !== '' ? $imageUrl : null,
                'status' => 'active',
            ]);
        }
    }

    private function validateVariants(array $post, float $basePrice): void
    {
        $names = $post['variant_names'] ?? [];
        $prices = $post['variant_prices'] ?? [];
        $stocks = $post['variant_stocks'] ?? [];
        $maxVariantPrice = $basePrice * self::VARIANT_PRICE_MAX_MULTIPLIER;

        foreach ($names as $index => $name) {
            $name = trim((string)$name);
            if ($name === '') {
                continue;
            }

            $price = trim((string)($prices[$index] ?? ''));
            if ($price !== '') {
                if (!is_numeric($price)) {
                    Flash::set('error', "Giá mẫu \"{$name}\" phải là số.");
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
                }

                $numericPrice = (float)$price;
                if ($numericPrice < 0) {
                    Flash::set('error', "Giá mẫu \"{$name}\" không được âm.");
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
                }

                if ($numericPrice > $maxVariantPrice) {
                    Flash::set('error', "Giá mẫu \"{$name}\" không được vượt quá " . self::VARIANT_PRICE_MAX_MULTIPLIER . " lần giá mặc định.");
                    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
                }
            }

            $stock = trim((string)($stocks[$index] ?? ''));
            if ($stock !== '' && (!ctype_digit($stock) || (int)$stock < 0)) {
                Flash::set('error', "Tồn kho mẫu \"{$name}\" phải là số nguyên không âm.");
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
            }
        }
    }

    private function parseRestockQuantity(mixed $value, string $label): int
    {
        $raw = trim((string)$value);
        if ($raw === '') {
            return 0;
        }

        if (!ctype_digit($raw)) {
            Flash::set('error', 'Số lượng nhập kho cho ' . $label . ' phải là số nguyên không âm.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/products');
        }

        return (int)$raw;
    }

    private function normalizeUploadFiles(?array $files): array
    {
        if ($files === null || !isset($files['name'])) {
            return [];
        }

        if (!is_array($files['name'])) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            $normalized[$index] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }
}
