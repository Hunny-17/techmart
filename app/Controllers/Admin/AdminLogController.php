<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminLog;

final class AdminLogController extends Controller
{
    public function index(): void
    {
        $model = new AdminLog();

        $filters = [
            'user_id'     => trim((string)($_GET['user_id']     ?? '')),
            'action'      => trim((string)($_GET['action']      ?? '')),
            'entity_type' => trim((string)($_GET['entity_type'] ?? '')),
            'date_from'   => trim((string)($_GET['date_from']   ?? '')),
            'date_to'     => trim((string)($_GET['date_to']     ?? '')),
        ];

        // Chỉ truyền filter có giá trị vào query — bỏ key rỗng
        $activeFilters = array_filter($filters, static fn($v) => $v !== '');

        $page = max(1, (int)($_GET['page'] ?? 1));

        $this->view('admin/logs/index', [
            'title'       => 'Lịch sử thao tác admin',
            'result'      => $model->paginateWithUser($page, 20, $activeFilters),
            'filters'     => $filters,
            'admins'      => $model->distinctAdmins(),
            'actions'     => $model->distinctActions(),
            'entityTypes' => $model->distinctEntityTypes(),
        ], 'admin');
    }
}
