<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Models\Voucher;

final class VoucherController extends Controller
{
    public function index(): void
    {
        $page          = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keyword       = trim((string)($_GET['q'] ?? ''));
        $activeFilter  = $_GET['active'] ?? '';

        $this->view('admin/vouchers/index', [
            'title'   => 'Quản lý voucher',
            'result'  => (new Voucher())->paginateFiltered($page, 15, $keyword !== '' ? $keyword : null, $activeFilter !== '' ? $activeFilter : null),
            'filters' => ['q' => $keyword, 'active' => $activeFilter],
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin/vouchers/create', ['title' => 'Thêm voucher'], 'admin');
    }

    public function store(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'code'           => ['required', 'max:50'],
            'discount_type'  => ['required', 'in:percent,fixed'],
            'discount_value' => ['required'],
            'min_order'      => [],
            'max_uses'       => [],
            'expires_at'     => [],
        ]);

        $code = strtoupper(trim($data['code']));
        $discountValue = (float)($data['discount_value'] ?? 0);
        $minOrderRaw = trim((string)($data['min_order'] ?? ''));
        $maxUsesRaw = trim((string)($data['max_uses'] ?? ''));
        $minOrder = $minOrderRaw !== '' ? (float)$minOrderRaw : 0.0;
        $maxUses = $maxUsesRaw !== '' ? (int)$maxUsesRaw : null;

        $manualError = match (true) {
            (new Voucher())->codeExists($code)                            => 'Mã voucher "' . $code . '" đã tồn tại.',
            $discountValue <= 0                                           => 'Giá trị giảm phải lớn hơn 0.',
            $minOrderRaw !== '' && !is_numeric($minOrderRaw)             => 'Đơn tối thiểu phải là số.',
            $minOrder < 0                                                => 'Đơn tối thiểu không được âm.',
            $maxUsesRaw !== '' && (!ctype_digit($maxUsesRaw) || (int)$maxUsesRaw <= 0) => 'Số lượt dùng tối đa phải là số nguyên lớn hơn 0.',
            $data['discount_type'] === 'percent' && $discountValue > 100  => 'Phần trăm giảm không được vượt quá 100.',
            default                                                       => null,
        };
        if ($manualError !== null) {
            Flash::set('error', $manualError);
            Session::set('_old', $data);
            $this->redirect('/admin/vouchers/create');
        }

        $expiresAt = isset($data['expires_at']) && $data['expires_at'] !== '' ? $data['expires_at'] : null;

        (new Voucher())->create([
            'code'           => $code,
            'discount_type'  => $data['discount_type'],
            'discount_value' => $discountValue,
            'min_order'      => $minOrder,
            'max_uses'       => $maxUses,
            'expires_at'     => $expiresAt,
            'is_active'      => 1,
        ]);

        Flash::set('success', 'Đã tạo voucher ' . $code . '.');
        $this->redirect('/admin/vouchers');
    }

    public function edit(int $id): void
    {
        $voucher = (new Voucher())->find($id);
        if ($voucher === null) {
            Flash::set('error', 'Không tìm thấy voucher.');
            $this->redirect('/admin/vouchers');
        }

        $this->view('admin/vouchers/edit', [
            'title'   => 'Sửa voucher',
            'voucher' => $voucher,
        ], 'admin');
    }

    public function update(int $id): void
    {
        Csrf::verify();

        $voucher = (new Voucher())->find($id);
        if ($voucher === null) {
            Flash::set('error', 'Không tìm thấy voucher.');
            $this->redirect('/admin/vouchers');
        }

        $data = $this->validate($_POST, [
            'code'           => ['required', 'max:50'],
            'discount_type'  => ['required', 'in:percent,fixed'],
            'discount_value' => ['required'],
            'min_order'      => [],
            'max_uses'       => [],
            'expires_at'     => [],
            'is_active'      => [],
        ]);

        $code = strtoupper(trim($data['code']));
        $discountValue = (float)($data['discount_value'] ?? 0);
        $minOrderRaw = trim((string)($data['min_order'] ?? ''));
        $maxUsesRaw = trim((string)($data['max_uses'] ?? ''));
        $minOrder = $minOrderRaw !== '' ? (float)$minOrderRaw : 0.0;
        $maxUses = $maxUsesRaw !== '' ? (int)$maxUsesRaw : null;

        $manualError = match (true) {
            (new Voucher())->codeExists($code, $id)                       => 'Mã voucher "' . $code . '" đã tồn tại.',
            $discountValue <= 0                                           => 'Giá trị giảm phải lớn hơn 0.',
            $minOrderRaw !== '' && !is_numeric($minOrderRaw)             => 'Đơn tối thiểu phải là số.',
            $minOrder < 0                                                => 'Đơn tối thiểu không được âm.',
            $maxUsesRaw !== '' && (!ctype_digit($maxUsesRaw) || (int)$maxUsesRaw <= 0) => 'Số lượt dùng tối đa phải là số nguyên lớn hơn 0.',
            $data['discount_type'] === 'percent' && $discountValue > 100  => 'Phần trăm giảm không được vượt quá 100.',
            default                                                       => null,
        };
        if ($manualError !== null) {
            Flash::set('error', $manualError);
            Session::set('_old', $data);
            $this->redirect('/admin/vouchers/' . $id . '/edit');
        }

        $expiresAt = isset($data['expires_at']) && $data['expires_at'] !== '' ? $data['expires_at'] : null;

        (new Voucher())->update($id, [
            'code'           => $code,
            'discount_type'  => $data['discount_type'],
            'discount_value' => $discountValue,
            'min_order'      => $minOrder,
            'max_uses'       => $maxUses,
            'expires_at'     => $expiresAt,
            'is_active'      => isset($data['is_active']) ? 1 : 0,
        ]);

        Flash::set('success', 'Đã cập nhật voucher ' . $code . '.');
        $this->redirect('/admin/vouchers');
    }

    public function destroy(int $id): void
    {
        Csrf::verify();

        $voucher = new Voucher();
        $row = $voucher->find($id);
        if ($row === null) {
            Flash::set('error', 'Không tìm thấy voucher.');
            $this->redirect('/admin/vouchers');
        }

        if ($voucher->orderUsageCount($id) > 0 || (int)($row['used_count'] ?? 0) > 0) {
            $voucher->update($id, ['is_active' => 0]);
            Flash::set('warning', 'Voucher đã phát sinh đơn hàng nên không xóa cứng. Hệ thống đã tắt voucher để giữ lịch sử đơn hàng.');
            $this->redirect('/admin/vouchers');
        }

        $voucher->delete($id);
        Flash::set('success', 'Đã xóa voucher.');
        $this->redirect('/admin/vouchers');
    }

    public function toggleActive(int $id): void
    {
        Csrf::verify();

        $v = (new Voucher())->find($id);
        if ($v === null) {
            Flash::set('error', 'Không tìm thấy voucher.');
            $this->redirect('/admin/vouchers');
        }

        $newState = (bool)$v['is_active'] ? 0 : 1;
        (new Voucher())->update($id, ['is_active' => $newState]);

        $label = $newState ? 'bật' : 'tắt';
        Flash::set('success', 'Đã ' . $label . ' voucher ' . e($v['code']) . '.');
        $this->redirect('/admin/vouchers');
    }
}
