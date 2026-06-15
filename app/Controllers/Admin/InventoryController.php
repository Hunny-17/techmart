<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;

final class InventoryController extends Controller
{
    private const STOCK_FILTERS = ['', 'low', 'out'];
    private const LOW_STOCK_THRESHOLD = 5;

    public function index(): void
    {
        $keyword = trim((string)($_GET['q'] ?? ''));
        $stock = trim((string)($_GET['stock'] ?? ''));
        if (!in_array($stock, self::STOCK_FILTERS, true)) {
            $stock = '';
        }

        $product = new Product();

        $this->view('admin/inventory/index', [
            'title' => 'Quản lý tồn kho',
            'items' => $product->inventoryItems(
                $keyword !== '' ? $keyword : null,
                $stock !== '' ? $stock : null,
                self::LOW_STOCK_THRESHOLD
            ),
            'summary' => $product->inventorySummary(self::LOW_STOCK_THRESHOLD),
            'filters' => [
                'q' => $keyword,
                'stock' => $stock,
            ],
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ], 'admin');
    }
}
