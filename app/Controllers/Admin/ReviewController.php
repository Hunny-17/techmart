<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Review;
use App\Services\AdminLogger;

final class ReviewController extends Controller
{
    private const STATUSES = ['visible', 'hidden'];

    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = $_GET['status'] ?? null;
        if ($status !== null && $status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }
        $rating = isset($_GET['rating']) && $_GET['rating'] !== '' ? (int)$_GET['rating'] : null;
        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            $rating = null;
        }
        $keyword = trim((string)($_GET['q'] ?? ''));

        $this->view('admin/reviews/index', [
            'title' => 'Quản lý đánh giá',
            'result' => (new Review())->paginateWithDetails(
                $page,
                10,
                $status,
                $rating,
                $keyword !== '' ? $keyword : null
            ),
            'filters' => [
                'q' => $keyword,
                'status' => $status ?? '',
                'rating' => $rating !== null ? (string)$rating : '',
            ],
            'statuses' => self::STATUSES,
        ], 'admin');
    }

    public function hide(int $id): void
    {
        Csrf::verify();
        $review = new Review();

        if ($review->find($id) === null) {
            Flash::set('error', 'Không tìm thấy đánh giá.');
            $this->redirect('/admin/reviews');
        }

        $review->hide($id);
        Flash::set('success', 'Đã ẩn đánh giá.');
        (new AdminLogger())->log('hide', 'review', $id, "Ẩn đánh giá #$id");
        $this->redirect('/admin/reviews');
    }

    public function show(int $id): void
    {
        Csrf::verify();
        $review = new Review();

        if ($review->find($id) === null) {
            Flash::set('error', 'Không tìm thấy đánh giá.');
            $this->redirect('/admin/reviews');
        }

        $review->show($id);
        Flash::set('success', 'Đã hiển thị đánh giá.');
        (new AdminLogger())->log('show', 'review', $id, "Hiển thị đánh giá #$id");
        $this->redirect('/admin/reviews');
    }

    public function destroy(int $id): void
    {
        Csrf::verify();
        $review = new Review();

        $existing = $review->find($id);
        if ($existing === null) {
            Flash::set('error', 'Không tìm thấy đánh giá.');
            $this->redirect('/admin/reviews');
        }

        if ($existing['status'] === 'visible') {
            $review->hide($id);
            Flash::set('warning', 'Đánh giá đang hiển thị nên đã được ẩn thay vì xóa. Bấm xóa lần nữa khi đã ẩn nếu muốn xóa vĩnh viễn.');
            (new AdminLogger())->log('hide', 'review', $id, "Ẩn đánh giá #$id thay vì xoá (đang hiển thị)");
            $this->redirect('/admin/reviews');
        }

        $review->delete($id);
        Flash::set('success', 'Đã xóa đánh giá đã ẩn.');
        (new AdminLogger())->log('delete', 'review', $id, "Xoá vĩnh viễn đánh giá #$id");
        $this->redirect('/admin/reviews');
    }
}
