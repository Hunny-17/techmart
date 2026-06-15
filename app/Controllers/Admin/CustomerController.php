<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\AdminLogger;
use App\Services\EmailVerificationNotifier;

final class CustomerController extends Controller
{
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $this->view('admin/customers/index', [
            'title' => 'Quản lý khách hàng',
            'result' => (new Customer())->paginateCustomers(
                $page,
                10,
                $keyword !== '' ? $keyword : null,
                $status !== '' ? $status : null
            ),
            'filters' => [
                'q' => $keyword,
                'status' => $status,
            ],
        ], 'admin');
    }

    public function export(): void
    {
        $keyword = trim((string)($_GET['q'] ?? ''));
        $status  = trim((string)($_GET['status'] ?? ''));
        $rows    = (new Customer())->exportAll(
            $keyword !== '' ? $keyword : null,
            $status  !== '' ? $status  : null
        );

        $filename = 'customers-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        if ($out === false) return;

        fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel
        fputcsv($out, ['ID', 'Họ tên', 'Email', 'SĐT', 'Địa chỉ', 'Trạng thái', 'Xác thực email', 'Ngày đăng ký', 'Số đơn', 'Tổng chi tiêu']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['full_name'],
                $r['email'],
                $r['phone'] ?? '',
                $r['address'] ?? '',
                $r['status'] === 'active' ? 'Hoạt động' : 'Đã khóa',
                !empty($r['email_verified_at']) ? 'Đã xác thực' : 'Chưa xác thực',
                date('d/m/Y', strtotime((string)$r['created_at'])),
                (int)$r['order_count'],
                (float)$r['total_spent'],
            ]);
        }

        fclose($out);
    }

    public function show(int $id): void
    {
        $customer = (new Customer())->findCustomer($id);
        if ($customer === null) {
            Flash::set('error', 'Không tìm thấy khách hàng.');
            $this->redirect('/admin/customers');
        }

        $order  = new Order();
        $orders = $order->byUser($id);
        $stats  = [
            'order_count' => count($orders),
            'total_spent' => $order->totalSpentByUser($id),
            'total_savings' => $order->totalSavingsByUser($id),
        ];

        $this->view('admin/customers/show', [
            'title'    => 'Khách hàng: ' . $customer['full_name'],
            'customer' => $customer,
            'orders'   => $orders,
            'stats'    => $stats,
        ], 'admin');
    }

    public function lock(int $id): void
    {
        Csrf::verify();

        $customer = new Customer();
        $found    = $customer->findCustomer($id);
        if ($found === null) {
            Flash::set('error', 'Không tìm thấy khách hàng.');
            $this->redirect('/admin/customers');
        }

        $customer->lock($id);
        Flash::set('success', 'Đã khóa tài khoản khách hàng.');
        (new AdminLogger())->log('lock', 'customer', $id, "Khoá tài khoản khách hàng: {$found['email']}");
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        $this->redirect(str_contains($back, '/admin/customers/' . $id) ? '/admin/customers/' . $id : '/admin/customers');
    }

    public function unlock(int $id): void
    {
        Csrf::verify();

        $customer = new Customer();
        $found    = $customer->findCustomer($id);
        if ($found === null) {
            Flash::set('error', 'Không tìm thấy khách hàng.');
            $this->redirect('/admin/customers');
        }

        $customer->unlock($id);
        Flash::set('success', 'Đã mở khóa tài khoản khách hàng.');
        (new AdminLogger())->log('unlock', 'customer', $id, "Mở khoá tài khoản khách hàng: {$found['email']}");
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        $this->redirect(str_contains($back, '/admin/customers/' . $id) ? '/admin/customers/' . $id : '/admin/customers');
    }

    public function resendVerification(int $id): void
    {
        Csrf::verify();

        $customerModel = new Customer();
        $customer = $customerModel->findCustomer($id);
        if ($customer === null) {
            Flash::set('error', 'Không tìm thấy khách hàng.');
            $this->redirect('/admin/customers');
        }

        if (!empty($customer['email_verified_at'])) {
            Flash::set('info', 'Email khách hàng này đã được xác thực.');
            $this->redirect('/admin/customers');
        }

        if (($customer['status'] ?? '') === 'locked') {
            Flash::set('warning', 'Tài khoản đang bị khóa nên chưa gửi lại email xác thực.');
            $this->redirect('/admin/customers');
        }

        $userModel = new User();
        $retryAfter = $userModel->emailVerificationRetryAfter($customer);
        if ($retryAfter > 0) {
            Flash::set('warning', 'Vui lòng chờ ' . $retryAfter . ' giây trước khi gửi lại email xác thực.');
            $this->redirect('/admin/customers');
        }

        $token = $userModel->createEmailVerificationToken($id);
        $sent = (new EmailVerificationNotifier())->send($customer, $token);

        if ($sent) {
            Flash::set('success', 'Đã gửi lại email xác thực cho khách hàng.');
        } else {
            Flash::set('warning', 'Chưa gửi được email xác thực. Vui lòng kiểm tra cấu hình SMTP.');
        }
        (new AdminLogger())->log('resend_verification', 'customer', $id, "Gửi lại email xác thực cho: {$customer['email']}");
        $this->redirect('/admin/customers');
    }
}
