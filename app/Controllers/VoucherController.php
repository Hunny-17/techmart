<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Voucher;

final class VoucherController extends Controller
{
    /** GET /voucher/validate?code=...&total=... */
    public function check(): void
    {
        $code  = strtoupper(trim($_GET['code'] ?? ''));
        $total = max(0.0, (float)($_GET['total'] ?? 0));

        if ($code === '') {
            $this->json(['valid' => false, 'error' => 'Vui lòng nhập mã giảm giá.', 'discount' => 0]);
            return;
        }

        $result = (new Voucher())->validate($code, $total);
        unset($result['voucher']); // không expose toàn bộ voucher ra client
        $this->json($result);
    }
}
