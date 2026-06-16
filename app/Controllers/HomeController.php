<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Wishlist;

final class HomeController extends Controller
{
    public function index(): void
    {
        $product       = new Product();
        $wishlistedIds = Auth::check() ? (new Wishlist())->productIdsByUser((int)Auth::id()) : [];

        $recentIds      = array_slice((array)Session::get('recently_viewed', []), 0, 4);
        $recentProducts = !empty($recentIds) ? $product->findByIds($recentIds) : [];

        $this->view('home/index', [
            'title'          => 'Trang chủ',
            'featured'       => $product->featured(8),
            'newArrivals'    => $product->newArrivals(4),
            'categories'     => array_slice((new Category())->options(), 0, 6),
            'wishlistedIds'  => $wishlistedIds,
            'recentProducts' => $recentProducts,
        ]);
    }

    public function products(): void
    {
        $product    = new Product();
        $keyword    = $_GET['q'] ?? null;
        $categoryId = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int)$_GET['cat'] : null;
        $page       = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $sort     = in_array($_GET['sort'] ?? '', ['price_asc', 'price_desc', 'name_asc'], true)
                        ? $_GET['sort']
                        : 'newest';
        $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? max(0, (float)$_GET['min_price']) : null;
        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? max(0, (float)$_GET['max_price']) : null;

        $result        = $product->search($keyword, $categoryId, $page, sort: $sort, minPrice: $minPrice, maxPrice: $maxPrice);
        $wishlistedIds = Auth::check() ? (new Wishlist())->productIdsByUser((int)Auth::id()) : [];

        $this->view('home/products', [
            'title'         => 'Sản phẩm',
            'result'        => $result,
            'keyword'       => $keyword,
            'categories'    => (new Category())->options(),
            'categoryId'    => $categoryId,
            'sort'          => $sort,
            'minPrice'      => $minPrice,
            'maxPrice'      => $maxPrice,
            'wishlistedIds' => $wishlistedIds,
        ]);
    }

    public function suggest(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (mb_strlen($q) < 2) {
            $this->json([]);
            return;
        }

        $rows = (new Product())->suggest($q, 6);
        $this->json(array_map(static fn($p) => [
            'id'        => (int)$p['id'],
            'name'      => $p['name'],
            'price'     => (float)$p['price'],
            'image_url' => $p['image_url'] ?? '',
        ], $rows));
    }

    public function show(int $id): void
    {
        $productModel = new Product();
        $product = $productModel->withCategory($id);
        if ($product === null) {
            http_response_code(404);
            $this->view('errors/404', [
                'title'     => 'Không tìm thấy sản phẩm',
                'suggested' => (new Product())->featured(4),
            ], 'customer');
            return;
        }

        $review = new Review();
        $reviews = $review->visibleByProduct($id);
        $reviewableOrders = Auth::check()
            ? $review->reviewableOrders((int)Auth::id(), $id)
            : [];

        $wishlist      = new Wishlist();
        $wishlisted    = Auth::check() ? $wishlist->isWishlisted((int)Auth::id(), $id) : false;
        $wishlistedIds = Auth::check() ? $wishlist->productIdsByUser((int)Auth::id()) : [];

        $related = (int)($product['category_id'] ?? 0) > 0
            ? $productModel->related($id, (int)$product['category_id'], 4)
            : [];

        // Recently viewed — session-based
        $recent = Session::get('recently_viewed', []);
        $recent = array_values(array_filter($recent, fn($rid) => (int)$rid !== $id));
        array_unshift($recent, $id);
        Session::set('recently_viewed', array_slice($recent, 0, 7));
        $recentIds      = array_slice(array_filter($recent, fn($rid) => (int)$rid !== $id), 0, 4);
        $recentProducts = !empty($recentIds) ? $productModel->findByIds($recentIds) : [];

        $this->view('home/show', [
            'title'            => $product['name'],
            'product'          => $product,
            'images'           => (new ProductImage())->byProduct($id),
            'variants'         => (new ProductVariant())->activeByProduct($id),
            'reviews'          => $reviews,
            'reviewableOrders' => $reviewableOrders,
            'wishlisted'        => $wishlisted,
            'wishlistedIds'     => $wishlistedIds,
            'related'           => $related,
            'recentProducts'    => $recentProducts,
        ]);
    }
}
