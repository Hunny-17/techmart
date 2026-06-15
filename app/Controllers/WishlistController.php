<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Wishlist;

final class WishlistController extends Controller
{
    /** GET /my-wishlist */
    public function index(): void
    {
        $this->view('wishlist/index', [
            'title' => 'Sản phẩm yêu thích',
            'items' => (new Wishlist())->byUserWithProducts((int)Auth::id()),
        ]);
    }

    /** GET /wishlist/count — AJAX */
    public function count(): void
    {
        if (!Auth::check()) {
            $this->json(['count' => 0]);
            return;
        }
        $this->json(['count' => (new Wishlist())->countByUser((int)Auth::id())]);
    }

    /** POST /wishlist/toggle */
    public function toggle(): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'Vui lòng đăng nhập.'], 401);
            return;
        }

        Csrf::verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            $this->json(['error' => 'Sản phẩm không hợp lệ.'], 422);
            return;
        }

        $wishlist   = new Wishlist();
        $userId     = (int)Auth::id();
        $wishlisted = $wishlist->toggle($userId, $productId);
        $count      = $wishlist->countByUser($userId);

        $this->json(['wishlisted' => $wishlisted, 'count' => $count]);
    }
}
