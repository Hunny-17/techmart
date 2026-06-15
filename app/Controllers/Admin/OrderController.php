<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderEmailLog;
use App\Models\Voucher;
use App\Services\AdminLogger;
use App\Services\InvoiceSigner;
use App\Services\OrderCustomerNotifier;
use App\Services\OrderStatusNotifier;

final class OrderController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
    private const PAYMENT_METHODS = ['cod', 'bank_transfer', 'e_wallet'];
    private const PAYMENT_STATUSES = ['unpaid', 'awaiting_review', 'paid', 'refunded'];

    public function index(): void
    {
        $status = $_GET['status'] ?? null;
        if ($status !== null && $status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $payment = $_GET['payment'] ?? null;
        if ($payment !== null && $payment !== '' && !in_array($payment, self::PAYMENT_METHODS, true)) {
            $payment = null;
        }

        $keyword = trim((string)($_GET['q'] ?? ''));

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $orderModel = new Order();
        $result = $orderModel->paginateWithCustomer(
            $status,
            $page,
            10,
            $keyword !== '' ? $keyword : null,
            $payment
        );

        $this->view('admin/orders/index', [
            'title' => 'Quản lý đơn hàng',
            'result' => $result,
            'filters' => [
                'q' => $keyword,
                'status' => $status ?? '',
                'payment' => $payment ?? '',
            ],
            'statusCounts' => $orderModel->statusCounts(),
            'statuses' => self::STATUSES,
            'paymentMethods' => self::PAYMENT_METHODS,
        ], 'admin');
    }

    public function show(int $id): void
    {
        $order = (new Order())->withCustomer($id);
        if ($order === null) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/admin/orders');
        }

        $items = (new OrderDetail())->byOrder($id);

        $this->view('admin/orders/show', [
            'title' => 'Chi tiết đơn hàng #' . $id,
            'order' => $order,
            'items' => $items,
            'statuses' => self::STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'emailLogs' => (new OrderEmailLog())->byOrder($id),
        ], 'admin');
    }

    public function invoice(int $id): void
    {
        $order = (new Order())->withCustomer($id);
        if ($order === null) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/admin/orders');
        }

        $items = (new OrderDetail())->byOrder($id);

        $signer = new InvoiceSigner(
            $_ENV['INVOICE_SIGNATURE_KEY'] ?? throw new \RuntimeException('INVOICE_SIGNATURE_KEY is not set in .env')
        );

        $this->view('admin/orders/invoice', [
            'title'     => 'Hóa đơn #' . $id,
            'order'     => $order,
            'items'     => $items,
            'signature' => $signer->sign($order, $items),
        ], null);
    }

    public function email(int $id, int $logId): void
    {
        $log = (new OrderEmailLog())->findForOrder($logId, $id);
        if ($log === null || empty($log['mail_file'])) {
            Flash::set('error', 'Không tìm thấy email của đơn hàng.');
            $this->redirect('/admin/orders/' . $id);
        }

        $root = dirname(__DIR__, 3);
        $mailRoot = realpath($root . '/storage/mail');
        $file = realpath($root . '/' . ltrim((string)$log['mail_file'], '/\\'));

        if ($mailRoot === false || $file === false || !str_starts_with(str_replace('\\', '/', $file), str_replace('\\', '/', $mailRoot) . '/')) {
            Flash::set('error', 'File email không hợp lệ.');
            $this->redirect('/admin/orders/' . $id);
        }

        header('Content-Type: text/html; charset=UTF-8');
        readfile($file);
    }

    public function export(): void
    {
        $status = $_GET['status'] ?? null;
        if ($status !== null && $status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $payment = $_GET['payment'] ?? null;
        if ($payment !== null && $payment !== '' && !in_array($payment, self::PAYMENT_METHODS, true)) {
            $payment = null;
        }

        $keyword = trim((string)($_GET['q'] ?? ''));
        $rows = (new Order())->exportWithCustomer(
            $status,
            $keyword !== '' ? $keyword : null,
            $payment
        );

        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        $paymentLabels = [
            'cod' => 'COD',
            'bank_transfer' => 'Chuyển khoản',
            'e_wallet' => 'Ví điện tử',
        ];
        $paymentStatusLabels = [
            'unpaid' => 'Chưa thanh toán',
            'awaiting_review' => 'Chờ đối soát',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
        ];

        $filename = 'orders-' . date('Ymd-His') . '.csv';
        $inline = ($_GET['inline'] ?? '') === '1';
        header('Content-Type: ' . ($inline ? 'text/plain' : 'text/csv') . '; charset=UTF-8');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }

        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Mã đơn',
            'Khách hàng',
            'Email',
            'SĐT',
            'Số dòng',
            'Tổng SL',
            'Tổng tiền',
            'Thanh toán',
            'Mã thanh toán',
            'Trạng thái thanh toán',
            'Trạng thái',
            'Ngày đặt',
            'Địa chỉ giao hàng',
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                '#' . $row['id'],
                $row['customer_name'],
                $row['customer_email'],
                $row['customer_phone'],
                (int)$row['item_count'],
                (int)$row['total_quantity'],
                (float)$row['total_amount'],
                $paymentLabels[$row['payment_method']] ?? $row['payment_method'],
                $row['payment_reference_code'] ?? '',
                $paymentStatusLabels[$row['payment_status'] ?? 'unpaid'] ?? ($row['payment_status'] ?? 'unpaid'),
                $statusLabels[$row['status']] ?? $row['status'],
                date('d/m/Y H:i', strtotime((string)$row['created_at'])),
                $row['shipping_address'],
            ]);
        }

        fclose($out);
    }

    public function exportPreview(): void
    {
        $status = $_GET['status'] ?? null;
        if ($status !== null && $status !== '' && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $payment = $_GET['payment'] ?? null;
        if ($payment !== null && $payment !== '' && !in_array($payment, self::PAYMENT_METHODS, true)) {
            $payment = null;
        }

        $keyword = trim((string)($_GET['q'] ?? ''));
        $rows = (new Order())->exportWithCustomer(
            $status,
            $keyword !== '' ? $keyword : null,
            $payment
        );

        $this->view('admin/orders/export_preview', [
            'title' => 'Xem bảng xuất đơn hàng',
            'rows' => $rows,
            'filters' => [
                'q' => $keyword,
                'status' => $status ?? '',
                'payment' => $payment ?? '',
            ],
        ], 'admin');
    }

    public function updateStatus(int $id): void
    {
        Csrf::verify();

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if ($order === null) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/admin/orders');
        }

        $data = $this->validate($_POST, [
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);

        if (!$this->canChangeStatus($order['status'], $data['status'])) {
            Flash::set('error', 'Trạng thái đơn hàng không hợp lệ.');
            $this->redirect('/admin/orders/' . $id);
        }

        if ($data['status'] === 'cancelled' && $order['status'] !== 'cancelled') {
            $this->cancelOrderAndRestoreStock($id, $orderModel);
            $this->notifyCustomerStatusChange($id, 'cancelled');
            Flash::set('success', 'Đã hủy đơn hàng và hoàn lại tồn kho.');
            (new AdminLogger())->log('change_status', 'order', $id, "Đổi trạng thái đơn #{$id}: {$order['status']} → cancelled");
            $this->redirect('/admin/orders/' . $id);
        }

        $orderModel->changeStatus($id, $data['status']);
        if ($order['status'] !== $data['status']) {
            $this->notifyCustomerStatusChange($id, $data['status']);
        }
        Flash::set('success', 'Cập nhật trạng thái đơn hàng thành công.');
        (new AdminLogger())->log('change_status', 'order', $id, "Đổi trạng thái đơn #{$id}: {$order['status']} → {$data['status']}");
        $this->redirect('/admin/orders/' . $id);
    }

    public function updatePaymentStatus(int $id): void
    {
        Csrf::verify();

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if ($order === null) {
            Flash::set('error', 'Không tìm thấy đơn hàng.');
            $this->redirect('/admin/orders');
        }

        $data = $this->validate($_POST, [
            'payment_status' => ['required', 'in:' . implode(',', self::PAYMENT_STATUSES)],
        ]);

        if ($order['status'] === 'cancelled' && $data['payment_status'] !== 'refunded') {
            Flash::set('error', 'Đơn đã hủy chỉ nên chuyển thanh toán sang hoàn tiền.');
            $this->redirect('/admin/orders/' . $id);
        }

        if (!$this->canChangePaymentStatus($order, $data['payment_status'])) {
            Flash::set('error', 'Trạng thái thanh toán không phù hợp với phương thức thanh toán hoặc trạng thái đơn hàng.');
            $this->redirect('/admin/orders/' . $id);
        }

        $orderModel->changePaymentStatus($id, $data['payment_status']);
        if (($order['payment_status'] ?? 'unpaid') !== 'paid' && $data['payment_status'] === 'paid') {
            $this->notifyCustomerPaymentPaid($id);
        }
        Flash::set('success', 'Cập nhật trạng thái thanh toán thành công.');
        (new AdminLogger())->log(
            'change_payment_status',
            'order',
            $id,
            "Đổi trạng thái thanh toán đơn #{$id}: " . ($order['payment_status'] ?? 'unpaid') . " → {$data['payment_status']}"
        );
        $this->redirect('/admin/orders/' . $id);
    }

    private function cancelOrderAndRestoreStock(int $id, Order $orderModel): void
    {
        $db = Database::pdo();
        try {
            $db->beginTransaction();

            $order = $orderModel->find($id);
            $items = (new OrderDetail())->byOrder($id);
            foreach ($items as $item) {
                if (!empty($item['variant_id'])) {
                    $stmt = $db->prepare("
                        UPDATE product_variants
                        SET stock_quantity = stock_quantity + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$item['quantity'], (int)$item['variant_id']]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE products
                        SET stock_quantity = stock_quantity + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$item['quantity'], (int)$item['product_id']]);
                }
            }

            if (!empty($order['voucher_id'])) {
                (new Voucher())->decrementUsed((int)$order['voucher_id']);
            }

            $orderModel->changeStatus($id, 'cancelled');
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $e;
        }
    }

    private function canChangeStatus(string $current, string $next): bool
    {
        if ($current === $next) {
            return true;
        }

        if ($next === 'cancelled' && $current !== 'delivered') {
            return true;
        }

        $flow = [
            'pending' => 'confirmed',
            'confirmed' => 'shipping',
            'shipping' => 'delivered',
        ];

        return ($flow[$current] ?? null) === $next;
    }

    private function canChangePaymentStatus(array $order, string $next): bool
    {
        $current = (string)($order['payment_status'] ?? 'unpaid');
        if ($current === $next) {
            return true;
        }

        if ($next === 'refunded') {
            return $current === 'paid' || (string)$order['status'] === 'cancelled';
        }

        if ((string)$order['payment_method'] === 'cod' && $next === 'paid') {
            return (string)$order['status'] === 'delivered';
        }

        if ((string)$order['status'] === 'cancelled') {
            return false;
        }

        return true;
    }

    private function notifyCustomerStatusChange(int $id, string $status): void
    {
        try {
            $order = (new Order())->withCustomer($id);
            if ($order === null) {
                return;
            }

            $result = (new OrderStatusNotifier())->notify($order, $status);
            $this->recordEmailLog($id, $result);
            if (!$result['sent']) {
                Flash::set('warning', 'Trạng thái đã cập nhật nhưng chưa tạo/gửi được email thông báo.');
            }
        } catch (\Throwable $e) {
            error_log('Order status email failed: ' . $e->getMessage());
            $this->recordEmailLog($id, [
                'sent' => false,
                'recipient' => '',
                'subject' => 'Order status notification',
                'status' => $status,
                'mail_file' => null,
                'error_message' => $e->getMessage(),
            ]);
            Flash::set('warning', 'Trạng thái đã cập nhật nhưng email thông báo bị lỗi.');
        }
    }

    private function notifyCustomerPaymentPaid(int $id): void
    {
        try {
            $order = (new Order())->withCustomer($id);
            if ($order === null) {
                return;
            }

            $items = (new OrderDetail())->byOrder($id);
            $result = (new OrderCustomerNotifier())->paymentPaid($order, $items);
            $this->recordEmailLog($id, $result);
            if (!$result['sent']) {
                Flash::set('warning', 'Thanh toán đã cập nhật nhưng email xác nhận thanh toán chưa gửi được.');
            }
        } catch (\Throwable $e) {
            error_log('Payment paid email failed: ' . $e->getMessage());
            $this->recordEmailLog($id, [
                'sent' => false,
                'recipient' => '',
                'subject' => 'Payment paid confirmation',
                'status' => 'payment_paid',
                'mail_file' => null,
                'error_message' => $e->getMessage(),
            ]);
            Flash::set('warning', 'Thanh toán đã cập nhật nhưng email xác nhận thanh toán bị lỗi.');
        }
    }

    private function recordEmailLog(int $id, array $result): void
    {
        (new OrderEmailLog())->create([
            'order_id' => $id,
            'recipient' => (string)($result['recipient'] ?? ''),
            'subject' => (string)($result['subject'] ?? ''),
            'status' => (string)($result['status'] ?? ''),
            'send_status' => !empty($result['sent']) ? 'sent' : 'failed',
            'mail_file' => $this->relativeMailPath($result['mail_file'] ?? null),
            'error_message' => $result['error_message'] ?? null,
        ]);
    }

    private function relativeMailPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $root = str_replace('\\', '/', dirname(__DIR__, 3));
        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, $root . '/')) {
            return substr($normalized, strlen($root) + 1);
        }

        return $normalized;
    }

}
