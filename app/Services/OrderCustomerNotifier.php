<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\App;

final class OrderCustomerNotifier
{
    private const PAYMENT_LABELS = [
        'cod' => 'COD',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'e_wallet' => 'Ví điện tử',
    ];

    public function __construct(private readonly Mailer $mailer = new Mailer())
    {
    }

    public function orderPlaced(array $order, array $items): array
    {
        $subject = 'TechMart xác nhận đơn hàng #' . $order['id'];
        return $this->sendOrderMail($order, $items, $subject, 'order_placed', $this->placedIntro($order));
    }

    public function paymentPaid(array $order, array $items): array
    {
        $subject = 'TechMart đã xác nhận thanh toán đơn #' . $order['id'];
        return $this->sendOrderMail(
            $order,
            $items,
            $subject,
            'payment_paid',
            'TechMart đã xác nhận nhận được thanh toán cho đơn hàng của bạn. Đơn sẽ tiếp tục được xử lý theo trạng thái hiện tại.'
        );
    }

    private function sendOrderMail(array $order, array $items, string $subject, string $status, string $intro): array
    {
        $email = (string)($order['customer_email'] ?? '');
        if ($email === '') {
            return [
                'sent' => false,
                'recipient' => '',
                'subject' => $subject,
                'status' => $status,
                'mail_file' => null,
                'error_message' => 'Order does not have a customer email.',
            ];
        }

        $html = $this->buildHtml($order, $items, $intro);
        $text = $this->buildText($order, $items, $intro);
        $sent = $this->mailer->send($email, $subject, $html, $text);

        return [
            'sent' => $sent,
            'recipient' => $email,
            'subject' => $subject,
            'status' => $status,
            'mail_file' => $this->mailer->lastLogFile(),
            'error_message' => $sent ? null : 'Mail driver could not send or write the email.',
        ];
    }

    private function placedIntro(array $order): string
    {
        if (($order['payment_method'] ?? 'cod') === 'cod') {
            return 'TechMart đã nhận đơn hàng của bạn. Bạn sẽ thanh toán khi nhận hàng.';
        }

        return 'TechMart đã nhận đơn hàng của bạn. Vui lòng thanh toán theo mã đối chiếu bên dưới để shop xác nhận nhanh hơn.';
    }

    private function buildHtml(array $order, array $items, string $intro): string
    {
        $orderId = (int)$order['id'];
        $customerName = htmlspecialchars((string)($order['customer_name'] ?? 'quý khách'), ENT_QUOTES, 'UTF-8');
        $orderUrl = function_exists('url') ? \url('/my-orders/' . $orderId) : '#';
        $paymentMethod = (string)($order['payment_method'] ?? 'cod');
        $paymentLabel = self::PAYMENT_LABELS[$paymentMethod] ?? $paymentMethod;

        $discount = (float)($order['discount_amount'] ?? 0);
        $finalTotal = (float)$order['total_amount'];

        $summaryRows = $this->rowHtml('Mã đơn', '#' . $orderId);
        if ($discount > 0) {
            $summaryRows .= $this->rowHtml('Tạm tính', $this->formatMoney($finalTotal + $discount));
            $summaryRows .= $this->rowHtml('Giảm giá (Voucher)', '-' . $this->formatMoney($discount));
        }
        $summaryRows .= $this->rowHtml('Tổng thanh toán', $this->formatMoney($finalTotal));
        $summaryRows .= $this->rowHtml('Thanh toán', $paymentLabel);
        $summaryRows .= $this->rowHtml('Ngày đặt', date('d/m/Y H:i', strtotime((string)$order['created_at'])));

        $savingsBanner = $discount > 0
            ? '<p style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;color:#166534;margin-bottom:4px">'
              . '🎉 Bạn đã tiết kiệm <strong>' . $this->formatMoney($discount) . '</strong> với mã giảm giá!</p>'
            : '';

        $html = '<div style="font-family:Arial,sans-serif;color:#111827;line-height:1.55">'
            . '<h2 style="margin:0 0 12px;color:#0d6efd">Thông tin đơn hàng TechMart</h2>'
            . '<p>Xin chào <strong>' . $customerName . '</strong>,</p>'
            . '<p>' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>'
            . $savingsBanner
            . '<table style="border-collapse:collapse;width:100%;max-width:620px;margin:18px 0">'
            . $summaryRows
            . '</table>';

        if (!empty($order['payment_reference_code'])) {
            $html .= '<div style="border:1px solid #e5e7eb;background:#f9fafb;border-radius:10px;padding:14px;margin:18px 0;max-width:620px">'
                . '<div style="font-size:13px;color:#6b7280">Mã thanh toán / nội dung chuyển tiền</div>'
                . '<div style="font-size:22px;font-weight:700;letter-spacing:.5px;color:#dc3545">'
                . htmlspecialchars((string)$order['payment_reference_code'], ENT_QUOTES, 'UTF-8')
                . '</div>'
                . '<div style="font-size:13px;color:#6b7280;margin-top:8px">'
                . htmlspecialchars($this->paymentReceiver($order), ENT_QUOTES, 'UTF-8')
                . '</div>'
                . '</div>';
        }

        $html .= '<h3 style="margin:18px 0 8px">Sản phẩm</h3>'
            . '<table style="border-collapse:collapse;width:100%;max-width:720px">'
            . '<thead><tr>'
            . '<th style="border:1px solid #e5e7eb;background:#f9fafb;padding:9px;text-align:left">Sản phẩm</th>'
            . '<th style="border:1px solid #e5e7eb;background:#f9fafb;padding:9px;text-align:center">SL</th>'
            . '<th style="border:1px solid #e5e7eb;background:#f9fafb;padding:9px;text-align:right">Đơn giá</th>'
            . '<th style="border:1px solid #e5e7eb;background:#f9fafb;padding:9px;text-align:right">Thành tiền</th>'
            . '</tr></thead><tbody>';

        foreach ($items as $item) {
            $name = (string)($item['product_name'] ?? '');
            if (!empty($item['variant_name'])) {
                $name .= ' - ' . $item['variant_name'];
            }
            $quantity = (int)$item['quantity'];
            $unitPrice = (float)$item['unit_price'];
            $html .= '<tr>'
                . '<td style="border:1px solid #e5e7eb;padding:9px">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="border:1px solid #e5e7eb;padding:9px;text-align:center">' . $quantity . '</td>'
                . '<td style="border:1px solid #e5e7eb;padding:9px;text-align:right">' . $this->formatMoney($unitPrice) . '</td>'
                . '<td style="border:1px solid #e5e7eb;padding:9px;text-align:right">' . $this->formatMoney($unitPrice * $quantity) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table>'
            . '<p style="margin-top:18px"><a href="' . htmlspecialchars($orderUrl, ENT_QUOTES, 'UTF-8') . '" '
            . 'style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px">'
            . 'Xem đơn hàng</a></p>'
            . '<p style="color:#6b7280;font-size:13px">Email này được gửi tự động từ hệ thống TechMart.</p>'
            . '</div>';

        return $html;
    }

    private function buildText(array $order, array $items, string $intro): string
    {
        $discount   = (float)($order['discount_amount'] ?? 0);
        $finalTotal = (float)$order['total_amount'];

        $lines = ['TechMart - Đơn hàng #' . (int)$order['id'], $intro];
        if ($discount > 0) {
            $lines[] = 'Tạm tính: ' . $this->formatMoney($finalTotal + $discount);
            $lines[] = 'Giảm giá (Voucher): -' . $this->formatMoney($discount);
            $lines[] = 'Tiết kiệm được: ' . $this->formatMoney($discount);
        }
        $lines[] = 'Tổng thanh toán: ' . $this->formatMoney($finalTotal);
        $lines[] = 'Thanh toán: ' . (self::PAYMENT_LABELS[$order['payment_method'] ?? 'cod'] ?? ($order['payment_method'] ?? 'cod'));

        if (!empty($order['payment_reference_code'])) {
            $lines[] = 'Mã thanh toán: ' . $order['payment_reference_code'];
            $lines[] = 'Thông tin nhận tiền: ' . $this->paymentReceiver($order);
        }

        $lines[] = 'Sản phẩm:';
        foreach ($items as $item) {
            $name = (string)($item['product_name'] ?? '');
            if (!empty($item['variant_name'])) {
                $name .= ' - ' . $item['variant_name'];
            }
            $lines[] = '- ' . $name . ' x ' . (int)$item['quantity'] . ': ' . $this->formatMoney((float)$item['unit_price'] * (int)$item['quantity']);
        }

        $lines[] = function_exists('url') ? \url('/my-orders/' . (int)$order['id']) : '';

        return implode("\n", $lines);
    }

    private function paymentReceiver(array $order): string
    {
        $cfg = App::$config['payment'] ?? [];
        if (($order['payment_method'] ?? '') === 'bank_transfer') {
            return trim(($cfg['bank_name'] ?? '') . ' - STK: ' . ($cfg['bank_account_no'] ?? '') . ' - Chủ TK: ' . ($cfg['bank_account_name'] ?? ''));
        }

        if (($order['payment_method'] ?? '') === 'e_wallet') {
            return trim(($cfg['wallet_name'] ?? '') . ' - Tài khoản ví: ' . ($cfg['wallet_account'] ?? ''));
        }

        return '';
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

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}
