<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Service ký số nội bộ cho hoá đơn TechMart.
 *
 * Tách khỏi Controller theo Single Responsibility Principle (chữ S trong SOLID):
 * Controller chỉ điều phối HTTP request/response, InvoiceSigner chỉ lo ký số.
 * Mọi thay đổi về thuật toán, định dạng payload, hay issuer đều nằm ở đây,
 * không cần đụng tới Controller hay View.
 */
final class InvoiceSigner
{
    public function __construct(
        private readonly string $secret,
        private readonly string $issuer    = 'TechMart',
        private readonly string $algorithm = 'sha256',
    ) {}

    /**
     * Ký hoá đơn và trả về mảng chữ ký để View hiển thị.
     *
     * @param  array $order  Một hàng orders (đã join thông tin customer)
     * @param  array $items  Các hàng order_details của đơn hàng
     * @return array{algorithm: string, issuer: string, signed_at: string, payload: string, hash: string, code: string}
     */
    public function sign(array $order, array $items): array
    {
        $payloadJson = $this->buildPayload($order, $items);
        $hash        = hash_hmac($this->algorithm, $payloadJson, $this->secret);

        return [
            'algorithm' => 'HMAC-' . strtoupper($this->algorithm),
            'issuer'    => $this->issuer,
            'signed_at' => (string)$order['created_at'],
            'payload'   => $payloadJson,
            'hash'      => $hash,
            // Mã xác thực rút gọn 24 ký tự hex, định dạng XXXXXX-XXXXXX-XXXXXX-XXXXXX
            'code'      => strtoupper(implode('-', str_split(substr($hash, 0, 24), 6))),
        ];
    }

    /**
     * Build chuỗi JSON payload từ dữ liệu đơn hàng.
     * Thứ tự field cố định → hash ổn định dù PHP thêm field vào array sau.
     */
    private function buildPayload(array $order, array $items): string
    {
        $payloadItems = array_map(static function (array $item): array {
            return [
                'product_id' => (int)$item['product_id'],
                'variant_id' => $item['variant_id'] !== null ? (int)$item['variant_id'] : null,
                'quantity'   => (int)$item['quantity'],
                'unit_price' => number_format((float)$item['unit_price'], 2, '.', ''),
            ];
        }, $items);

        $payload = [
            'issuer'         => $this->issuer,
            'invoice_no'     => '#' . $order['id'],
            'order_id'       => (int)$order['id'],
            'customer_email' => (string)$order['customer_email'],
            'total_amount'   => number_format((float)$order['total_amount'], 2, '.', ''),
            'payment_method' => (string)$order['payment_method'],
            'payment_reference_code' => (string)($order['payment_reference_code'] ?? ''),
            'payment_status' => (string)($order['payment_status'] ?? 'unpaid'),
            'status'         => (string)$order['status'],
            'created_at'     => (string)$order['created_at'],
            'items'          => $payloadItems,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json !== false ? $json : '';
    }
}
