<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Session;

if (!function_exists('e')) {
    /**
     * Escape HTML — DÙNG MỌI NƠI output user input ra view
     * <?= e($user['name']) ?>
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Tạo URL tuyệt đối từ path
     * url('/products') → http://localhost/techmart-web/public/products
     */
    function url(string $path = ''): string
    {
        $base = App::$config['app']['url'] ?? '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Đường dẫn tới file trong public/assets
     * asset('css/app.css') → http://.../public/assets/css/app.css
     */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $assetUrl = url('assets/' . $path);
        $assetFile = (App::$config['paths']['root'] ?? dirname(__DIR__, 2)) . '/public/assets/' . $path;

        if (is_file($assetFile)) {
            return $assetUrl . '?v=' . filemtime($assetFile);
        }

        return $assetUrl;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect và kết thúc execution
     */
    function redirect(string $path): never
    {
        // Path có thể là tuyệt đối hoặc relative
        $target = str_starts_with($path, 'http') ? $path : url($path);
        header("Location: $target");
        exit;
    }
}

if (!function_exists('old')) {
    /**
     * Lấy giá trị cũ của form sau khi validate fail
     * <input value="<?= old('email') ?>">
     */
    function old(string $key, string $default = ''): string
    {
        $data = Session::get('_old', []);
        return e($data[$key] ?? $default);
    }
}

if (!function_exists('errors')) {
    /**
     * Lấy validation errors
     */
    function errors(?string $field = null): mixed
    {
        $errs = Session::get('_errors', []);
        return $field === null ? $errs : ($errs[$field] ?? null);
    }
}

if (!function_exists('clearFormState')) {
    /**
     * Xoá _old và _errors sau khi đã hiển thị
     * Gọi cuối form view
     */
    function clearFormState(): void
    {
        Session::forget('_old');
        Session::forget('_errors');
    }
}

if (!function_exists('format_vnd')) {
    /**
     * Format giá tiền VND
     */
    function format_vnd(float|int $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        return App::$nonce;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * In sẵn input hidden token
     * <?= csrf_field() ?>
     */
    function csrf_field(): string
    {
        $token = \App\Core\Csrf::token();
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}
