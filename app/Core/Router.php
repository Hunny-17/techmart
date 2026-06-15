<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router đơn giản hỗ trợ:
 *  - HTTP methods: GET, POST
 *  - Path param dạng {id}
 *  - Middleware: 'auth' | 'admin'
 *
 * Cách thêm route: xem config/routes.php
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:array, middleware:?string}> */
    private array $routes = [];

    public function get(string $path, array $handler, ?string $middleware = null): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, ?string $middleware = null): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, ?string $middleware): void
    {
        // Chuyển {param} thành regex named group
        $pattern = preg_replace('#\{([a-z_][a-z0-9_]*)\}#i', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Bỏ base path nếu có (vd: /techmart-web/public/products → /products)
        $base = $this->detectBasePath();
if ($base !== '' && $base !== '/' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
$uri = '/' . ltrim($uri, '/');

// Debug: bật khi cần troubleshoot routing
// echo "DEBUG: method=$method | uri=$uri | base=$base"; exit;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Middleware check
            if ($route['middleware'] !== null) {
                $this->runMiddleware($route['middleware']);
            }

            // Lấy named params
            $params = array_filter(
                $matches,
                static fn($k) => !is_int($k),
                ARRAY_FILTER_USE_KEY
            );

            [$class, $method2] = $route['handler'];
            $controller = new $class();
            call_user_func_array([$controller, $method2], array_values($params));
            return;
        }

        // Không match → 404
        http_response_code(404);
        try {
            $suggested = (new \App\Models\Product())->featured(4);
        } catch (\Throwable $e) {
            $suggested = [];
        }
        View::render('errors/404', ['suggested' => $suggested, 'title' => 'Không tìm thấy trang'], 'customer');
    }

    private function runMiddleware(string $name): void
    {
        switch ($name) {
            case 'auth':
                if (!Auth::check()) {
                    Flash::set('error', 'Vui lòng đăng nhập để tiếp tục.');
                    redirect('/login');
                }
                break;
            case 'admin':
                if (!Auth::check() || !Auth::isAdmin()) {
                    Flash::set('error', 'Bạn không có quyền truy cập.');
                    redirect('/login');
                }
                break;
        }
    }

    /**
     * Tự dò base path khi project nằm trong subfolder
     * Ví dụ http://localhost/techmart-web/public/ → '/techmart-web/public'
     */
    private function detectBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($script));
        // Loại bỏ "/public" cuối nếu có (vì .htaccess rewrite ngầm)
        $dir = preg_replace('#/public$#', '', $dir);
        return rtrim($dir, '/');
    }
}
