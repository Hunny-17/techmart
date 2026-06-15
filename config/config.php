<?php
declare(strict_types=1);

/**
 * Cấu hình ứng dụng
 *
 * File này load biến môi trường từ .env và expose ra qua hằng config()
 * Tham khảo .env.example để biết cần khai báo những gì
 */

// Đọc .env nếu tồn tại
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$env = static function (string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || trim((string)$value) === '') {
        return $default;
    }

    return (string)$value;
};

return [
    'app' => [
        'name'  => $_ENV['APP_NAME']  ?? 'TechMart',
        'env'   => $_ENV['APP_ENV']   ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        'url'   => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),
    ],
    'db' => [
        'host'    => $_ENV['DB_HOST'] ?? 'localhost',
        'port'    => (int)($_ENV['DB_PORT'] ?? 3306),
        'name'    => $_ENV['DB_NAME'] ?? 'techmart',
        'user'    => $_ENV['DB_USER'] ?? 'root',
        'pass'    => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 1800),
    ],
    'mail' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'log',
        'from_email' => $_ENV['MAIL_FROM_EMAIL'] ?? 'no-reply@techmart.test',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'TechMart',
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'timeout' => (int)($_ENV['MAIL_TIMEOUT'] ?? 15),
        'api_key' => $_ENV['MAIL_API_KEY'] ?? '',
        'log_path' => dirname(__DIR__) . '/storage/mail',
    ],
    'payment' => [
        'bank_name' => $env('PAYMENT_BANK_NAME', 'MB Bank'),
        'bank_id' => $env('PAYMENT_BANK_ID', 'MB'),
        'bank_account_no' => $env('PAYMENT_BANK_ACCOUNT_NO', '100612200517'),
        'bank_account_name' => $env('PAYMENT_BANK_ACCOUNT_NAME', 'TRAN QUOC HUY'),
        'bank_branch' => $env('PAYMENT_BANK_BRANCH', ''),
        'wallet_name' => $env('PAYMENT_WALLET_NAME', 'TechMart Pay'),
        'wallet_account' => $env('PAYMENT_WALLET_ACCOUNT', 'TECHMARTPAY'),
        'wallet_qr_url' => $env('PAYMENT_WALLET_QR_URL', ''),
    ],
    'upload' => [
        'max_size'   => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 2_097_152),
        'allowed'    => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'path'       => dirname(__DIR__) . '/public/assets/uploads',
        'public_url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/') . '/assets/uploads',
    ],
    'paths' => [
        'root'    => dirname(__DIR__),
        'app'     => dirname(__DIR__) . '/app',
        'views'   => dirname(__DIR__) . '/app/Views',
        'storage' => dirname(__DIR__) . '/storage',
    ],
];
