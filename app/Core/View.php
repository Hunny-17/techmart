<?php
declare(strict_types=1);

namespace App\Core;

/**
 * View - render PHP template
 *
 * Tách content (file view) và layout (header/footer chung)
 * Ví dụ:
 *   View::render('admin/products/index', ['products' => $list], 'admin')
 *   → render Views/admin/products/index.php trong Views/layouts/admin.php
 */
final class View
{
    /**
     * @param string                $view    Tên view (không có .php), vd 'home/index'
     * @param array<string,mixed>   $data    Biến truyền vào view
     * @param string|null           $layout  Layout name (null = render trực tiếp không layout)
     */
    public static function render(string $view, array $data = [], ?string $layout = 'customer'): void
    {
        $viewsPath = App::$config['paths']['views'];
        $viewFile  = $viewsPath . '/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException("View không tồn tại: $view");
        }

        // Render content vào buffer
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Không có layout → in luôn
        if ($layout === null) {
            echo $content;
            return;
        }

        // Có layout → render layout với $content
        $layoutFile = $viewsPath . '/layouts/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout không tồn tại: $layout");
        }
        require $layoutFile;
    }

    /**
     * Trả JSON (cho AJAX endpoint)
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
