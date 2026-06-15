<?php
/** Hiển thị flash message và xóa */
$flash = \App\Core\Flash::pull();
?>
<?php foreach ($flash as $type => $msg): ?>
    <?php
        $alertClass = match ($type) {
            'success' => 'alert-success',
            'error'   => 'alert-danger',
            'warning' => 'alert-warning',
            default   => 'alert-info',
        };
    ?>
    <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
        <?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; ?>
