<?php
/**
 * Smart pagination partial với ellipsis
 * Yêu cầu: $result['page'], $result['lastPage']
 * Tuỳ chọn: $pageUrlFn (callable int→string) — nếu không có, dùng $_GET + ?page=N
 *
 * @var array{page:int,lastPage:int,total:int} $result
 * @var (callable(int):string)|null $pageUrlFn
 */
if (($result['lastPage'] ?? 1) <= 1) {
    return;
}

$current  = (int)$result['page'];
$last     = (int)$result['lastPage'];
$prevPage = max(1, $current - 1);
$nextPage = min($last, $current + 1);

$_buildPageUrl = $pageUrlFn ?? static function (int $p): string {
    return '?' . http_build_query(array_merge($_GET, ['page' => $p]));
};

// Tạo dải trang với null = dấu '...'
$range    = [];
$prevSeen = null;
for ($i = 1; $i <= $last; $i++) {
    $near = $i <= 2 || $i >= $last - 1 || abs($i - $current) <= 2;
    if ($near) {
        if ($prevSeen !== null && $i - $prevSeen > 1) {
            $range[] = null;
        }
        $range[] = $i;
        $prevSeen = $i;
    }
}
?>
<nav class="mt-4" aria-label="Phân trang">
    <ul class="pagination justify-content-center flex-wrap">
        <li class="page-item <?= $current === 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($_buildPageUrl($prevPage)) ?>" aria-label="Trang trước">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php foreach ($range as $p): ?>
            <?php if ($p === null): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php else: ?>
                <li class="page-item <?= $p === $current ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($_buildPageUrl($p)) ?>"><?= $p ?></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>

        <li class="page-item <?= $current === $last ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($_buildPageUrl($nextPage)) ?>" aria-label="Trang sau">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
