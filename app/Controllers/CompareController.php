<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Product;

class CompareController
{
    public function index(): void
    {
        $rawIds = trim($_GET['ids'] ?? '');
        $ids = [];
        if ($rawIds !== '') {
            foreach (explode(',', $rawIds) as $raw) {
                $n = (int)$raw;
                if ($n > 0) {
                    $ids[] = $n;
                }
            }
        }
        $ids = array_unique(array_slice($ids, 0, 3));

        $model    = new Product();
        $products = [];
        foreach ($ids as $id) {
            $p = $model->withCategory($id);
            if ($p && $p['status'] === 'active') {
                $products[] = $p;
            }
        }

        View::render('compare/index', [
            'title'    => 'So sánh sản phẩm',
            'products' => $products,
        ], 'customer');
    }
}
