<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Category;

final class CategoryController extends Controller
{
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword = trim((string)($_GET['q'] ?? ''));
        $category = new Category();

        $this->view('admin/categories/index', [
            'title' => 'Quản lý danh mục',
            'result' => $category->paginateWithProductCount(
                $page,
                10,
                $keyword !== '' ? $keyword : null
            ),
            'filters' => [
                'q' => $keyword,
            ],
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin/categories/create', [
            'title' => 'Thêm danh mục',
            'categories' => (new Category())->options(),
        ], 'admin');
    }

    public function store(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'name' => ['required', 'max:100'],
            'parent_id' => ['integer'],
        ]);

        $category = new Category();
        $slug = $this->uniqueSlug($category, $this->slugify($data['name']));
        $parentId = $this->normalizeParentId($data['parent_id'] ?? null);

        $category->create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
        ]);

        Flash::set('success', 'Thêm danh mục thành công.');
        $this->redirect('/admin/categories');
    }

    public function edit(int $id): void
    {
        $category = new Category();
        $row = $category->find($id);
        if ($row === null) {
            Flash::set('error', 'Không tìm thấy danh mục.');
            $this->redirect('/admin/categories');
        }

        $this->view('admin/categories/edit', [
            'title' => 'Sửa danh mục',
            'category' => $row,
            'categories' => $category->options($id),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Csrf::verify();

        $category = new Category();
        $row = $category->find($id);
        if ($row === null) {
            Flash::set('error', 'Không tìm thấy danh mục.');
            $this->redirect('/admin/categories');
        }

        $data = $this->validate($_POST, [
            'name' => ['required', 'max:100'],
            'parent_id' => ['integer'],
        ]);

        $parentId = $this->normalizeParentId($data['parent_id'] ?? null);
        if ($parentId === $id) {
            Flash::set('error', 'Danh mục không thể là cha của chính nó.');
            $this->redirect('/admin/categories/' . $id . '/edit');
        }

        $slug = $this->uniqueSlug($category, $this->slugify($data['name']), $id);

        $category->update($id, [
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
        ]);

        Flash::set('success', 'Cập nhật danh mục thành công.');
        $this->redirect('/admin/categories');
    }

    public function destroy(int $id): void
    {
        Csrf::verify();

        $category = new Category();
        if ($category->find($id) === null) {
            Flash::set('error', 'Không tìm thấy danh mục.');
            $this->redirect('/admin/categories');
        }

        if ($category->productCount($id) > 0) {
            Flash::set('error', 'Danh mục đang có sản phẩm nên không thể xóa.');
            $this->redirect('/admin/categories');
        }

        $category->delete($id);
        Flash::set('success', 'Đã xóa danh mục.');
        $this->redirect('/admin/categories');
    }

    private function normalizeParentId(mixed $value): ?int
    {
        $id = (int)($value ?? 0);
        return $id > 0 ? $id : null;
    }

    private function uniqueSlug(Category $category, string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'danh-muc';
        $slug = $baseSlug;
        $suffix = 2;

        while ($category->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
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
}
