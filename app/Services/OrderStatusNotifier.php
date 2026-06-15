<?php
declare(strict_types=1);

namespace App\Services;

final class OrderStatusNotifier
{
    private const LABELS = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];

    private const MESSAGES = [
        'pending' => 'Đơn hàng của bạn đang được TechMart tiếp nhận và chờ xử lý.',
        'confirmed' => 'TechMart đã xác nhận đơn hàng và sẽ chuẩn bị giao cho đơn vị vận chuyển.',
        'shipping' => 'Đơn hàng đang trên đường giao đến bạn. Vui lòng chú ý điện thoại.',
        'delivered' => 'Đơn hàng đã được giao thành công. Cảm ơn bạn đã mua sắm tại TechMart.',
        'cancelled' => 'Đơn hàng đã được hủy. Nếu cần hỗ trợ thêm, vui lòng liên hệ TechMart.',
    ];

    public function __construct(private readonly Mailer $mailer = new Mailer())
    {
    }

    public function notify(array $order, string $newStatus): array
    {
        $email = (string)($order['customer_email'] ?? '');
        if ($email === '') {
            return [
                'sent' => false,
                'recipient' => '',
                'subject' => '',
                'status' => $newStatus,
                'mail_file' => null,
                'error_message' => 'Order does not have a customer email.',
            ];
        }

        $label = self::LABELS[$newStatus] ?? $newStatus;
        $subject = 'TechMart cập nhật đơn hàng #' . $order['id'] . ': ' . $label;
        $html = $this->buildHtml($order, $newStatus, $label);
        $text = $this->buildText($order, $newStatus, $label);

        $sent = $this->mailer->send($email, $subject, $html, $text);

        return [
            'sent' => $sent,
            'recipient' => $email,
            'subject' => $subject,
            'status' => $newStatus,
            'mail_file' => $this->mailer->lastLogFile(),
            'error_message' => $sent ? null : 'Mail driver could not send or write the email.',
        ];
    }

    private function buildHtml(array $order, string $status, string $label): string
    {
        $customerName = htmlspecialchars((string)($order['customer_name'] ?? 'quý khách'), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars(self::MESSAGES[$status] ?? 'Đơn hàng của bạn vừa được cập nhật trạng thái.', ENT_QUOTES, 'UTF-8');
        $orderId = (int)$order['id'];
        $orderUrl = function_exists('url') ? \url('/my-orders/' . $orderId) : '#';

        return '<div style="font-family:Arial,sans-serif;color:#111827;line-height:1.55">'
            . '<h2 style="margin:0 0 12px;color:#0d6efd">Cập nhật trạng thái đơn hàng</h2>'
            . '<p>Xin chào <strong>' . $customerName . '</strong>,</p>'
            . '<p>' . $message . '</p>'
            . '<table style="border-collapse:collapse;width:100%;max-width:560px;margin:18px 0">'
            . $this->rowHtml('Mã đơn', '#' . $orderId)
            . $this->rowHtml('Trạng thái mới', $label)
            . $this->rowHtml('Tổng tiền', $this->formatMoney((float)$order['total_amount']))
            . $this->rowHtml('Ngày đặt', date('d/m/Y H:i', strtotime((string)$order['created_at'])))
            . '</table>'
            . '<p><a href="' . htmlspecialchars($orderUrl, ENT_QUOTES, 'UTF-8') . '" '
            . 'style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px">'
            . 'Xem đơn hàng</a></p>'
            . '<p style="color:#6b7280;font-size:13px">Email này được gửi tự động từ hệ thống TechMart.</p>'
            . '</div>';
    }

    private function rowHtml(string $label, string $value): string
    {
        return '<tr>'
            . '<td style="border:1px solid #e5e7eb;background:#f9fafb;padding:9px 11px;width:160px;color:#6b7280">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td style="border:1px solid #e5e7eb;padding:9px 11px;font-weight:600">'
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>'
            . '</tr>';
    }

    private function buildText(array $order, string $status, string $label): string
    {
        $orderId = (int)$order['id'];
        $orderUrl = function_exists('url') ? \url('/my-orders/' . $orderId) : '';

        return "TechMart cập nhật đơn hàng #{$orderId}\n"
            . "Trạng thái mới: {$label}\n"
            . (self::MESSAGES[$status] ?? "Đơn hàng của bạn vừa được cập nhật trạng thái.") . "\n"
            . "Tổng tiền: " . $this->formatMoney((float)$order['total_amount']) . "\n"
            . "Link xem đơn: {$orderUrl}\n";
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}
