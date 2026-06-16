<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Employee;
use App\Services\AdminLogger;

final class EmployeeController extends Controller
{
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $this->view('admin/employees/index', [
            'title' => 'Quản lý nhân viên',
            'result' => (new Employee())->paginateStaff(
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

    public function create(): void
    {
        $this->view('admin/employees/create', [
            'title' => 'Thêm nhân viên',
        ], 'admin');
    }

    public function store(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'full_name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['max:20'],
            'password' => ['required', 'min:6', 'max:100'],
        ]);

        $employee = new Employee();
        if ($employee->emailExists($data['email'])) {
            Flash::set('error', 'Email nay da duoc su dung.');
            $this->redirect('/admin/employees/create');
        }

        $employee->createStaff($data);

        Flash::set('success', 'Thêm nhân viên thành công.');
        (new AdminLogger())->log('create', 'employee', null, "Thêm nhân viên mới: {$data['full_name']} ({$data['email']})");
        $this->redirect('/admin/employees');
    }

    public function destroy(int $id): void
    {
        Csrf::verify();

        $employee = new Employee();
        $found    = $employee->findStaff($id);
        if ($found === null) {
            Flash::set('error', 'Không tìm thấy nhân viên.');
            $this->redirect('/admin/employees');
        }

        $deleted = $employee->deleteStaff($id);
        if ($deleted) {
            Flash::set('success', 'Đã xóa nhân viên chưa phát sinh dữ liệu liên quan.');
            (new AdminLogger())->log('delete', 'employee', $id, "Xoá nhân viên: {$found['full_name']} ({$found['email']})");
            $this->redirect('/admin/employees');
        }

        $employee->lockStaff($id);
        Flash::set('warning', 'Không xóa cứng nhân viên này. Tài khoản đã được khóa để giữ an toàn dữ liệu.');
        (new AdminLogger())->log('delete', 'employee', $id, "Khoá nhân viên do có dữ liệu liên quan: {$found['full_name']} ({$found['email']})");
        $this->redirect('/admin/employees');
    }
    public function lock(int $id): void
    {
        Csrf::verify();

        $employee = new Employee();
        $found    = $employee->findStaff($id);
        if ($found === null) {
            Flash::set('error', 'Không tìm thấy nhân viên.');
            $this->redirect('/admin/employees');
        }

        $employee->lockStaff($id);
        Flash::set('success', 'Đã khóa tài khoản nhân viên.');
        (new AdminLogger())->log('lock', 'employee', $id, "Khoá tài khoản nhân viên: {$found['full_name']}");
        $this->redirect('/admin/employees');
    }

    public function unlock(int $id): void
    {
        Csrf::verify();

        $employee = new Employee();
        $found    = $employee->findStaff($id);
        if ($found === null) {
            Flash::set('error', 'Không tìm thấy nhân viên.');
            $this->redirect('/admin/employees');
        }

        $employee->unlockStaff($id);
        Flash::set('success', 'Đã mở khóa tài khoản nhân viên.');
        (new AdminLogger())->log('unlock', 'employee', $id, "Mở khoá tài khoản nhân viên: {$found['full_name']}");
        $this->redirect('/admin/employees');
    }
}
