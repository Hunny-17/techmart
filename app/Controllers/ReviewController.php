<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Product;
use App\Models\Review;

final class ReviewController extends Controller
{
    public function create(int $id): void
    {
        $product = (new Product())->find($id);
        if ($product === null) {
            http_response_code(404);
            $this->view('errors/404', [], null);
            return;
        }

        $review = new Review();
        $orders = $review->reviewableOrders((int)Auth::id(), $id);
        if ($orders === []) {
            Flash::set('error', 'Bạn chỉ có thể đánh giá sản phẩm đã mua và đã giao.');
            $this->redirect('/products/' . $id);
        }

        $this->view('reviews/create', [
            'title' => 'Đánh giá sản phẩm',
            'product' => $product,
            'orders' => $orders,
            'selectedOrderId' => isset($_GET['order_id']) ? (int)$_GET['order_id'] : (int)$orders[0]['id'],
        ]);
    }

    public function store(int $id): void
    {
        Csrf::verify();

        $product = (new Product())->find($id);
        if ($product === null) {
            http_response_code(404);
            $this->view('errors/404', [], null);
            return;
        }

        $data = $this->validate($_POST, [
            'order_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['max:2000'],
        ]);

        $orderId = (int)$data['order_id'];
        $review = new Review();

        if (!$review->canUserReview((int)Auth::id(), $id, $orderId)) {
            Flash::set('error', 'Bạn không thể đánh giá sản phẩm này hoặc đã đánh giá rồi.');
            $this->redirect('/products/' . $id);
        }

        $review->create([
            'product_id' => $id,
            'user_id' => Auth::id(),
            'order_id' => $orderId,
            'rating' => (int)$data['rating'],
            'comment' => $data['comment'] ?? '',
            'status' => 'visible',
        ]);

        Flash::set('success', 'Cảm ơn bạn đã đánh giá sản phẩm.');
        $this->redirect('/products/' . $id);
    }
}
