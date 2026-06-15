<?php /** @var array $items */ /** @var int $total */ ?>
<?php
$totalQty = array_sum(array_column($items, 'quantity'));
?>

<section class="page-hero cart-hero mb-4">
    <div>
        <span class="store-eyebrow">Giỏ hàng</span>
        <h1>Kiểm tra sản phẩm trước khi đặt hàng</h1>
        <p>Xem lại sản phẩm, mẫu đã chọn, số lượng và tồn kho hiện tại trước khi chuyển sang bước thanh toán.</p>
    </div>
    <div class="cart-hero-summary">
        <div>
            <strong><?= number_format($totalQty) ?></strong>
            <span>sản phẩm</span>
        </div>
        <div>
            <strong><?= format_vnd($total) ?></strong>
            <span>tạm tính</span>
        </div>
    </div>
</section>

<?php if (empty($items)): ?>
    <div class="page-empty-state cart-empty-state">
        <i class="bi bi-cart-x"></i>
        <h2>Giỏ hàng đang trống</h2>
        <p>Thêm laptop, điện thoại hoặc phụ kiện bạn thích vào giỏ để bắt đầu đặt hàng.</p>
        <a href="<?= url('/products') ?>" class="btn btn-primary">
            Xem sản phẩm <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
<?php else: ?>
    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <section class="panel-card cart-items-card">
                <div class="panel-header cart-section-header">
                    <div>
                        <h2>Sản phẩm trong giỏ</h2>
                        <p><?= number_format(count($items)) ?> dòng sản phẩm, <?= number_format($totalQty) ?> sản phẩm.</p>
                    </div>
                    <a href="<?= url('/products') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Mua thêm
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0 align-middle cart-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-center" style="width:190px">Số lượng</th>
                                <th class="text-end">Thành tiền</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <?php $isLowStock = (int)$it['stock_quantity'] <= 5; ?>
                                <tr>
                                    <td>
                                        <div class="cart-line-item">
                                            <img src="<?= e($it['image_url'] ?: 'https://placehold.co/60') ?>"
                                                 class="line-item-image" alt="<?= e($it['name']) ?>">
                                            <div class="min-w-0">
                                                <a href="<?= url('/products/' . $it['id']) ?>"
                                                   class="cart-item-name">
                                                    <?= e($it['name']) ?>
                                                </a>
                                                <?php if (!empty($it['variant_name'])): ?>
                                                    <div class="small text-muted">Mẫu: <?= e($it['variant_name']) ?></div>
                                                <?php endif; ?>
                                                <div class="small <?= $isLowStock ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                                    Còn <?= number_format((int)$it['stock_quantity']) ?> sản phẩm
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= format_vnd($it['price']) ?></td>
                                    <td>
                                        <form action="<?= url('/cart/update') ?>" method="post"
                                              class="cart-qty-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="cart_key" value="<?= e($it['cart_key']) ?>">
                                            <input type="number" name="quantity" value="<?= e($it['quantity']) ?>"
                                                   min="0" max="<?= e($it['stock_quantity']) ?>"
                                                   class="form-control form-control-sm cart-qty-input"
                                                   aria-label="Số lượng <?= e($it['name']) ?>">
                                            <button class="btn btn-sm btn-outline-primary" title="Cập nhật">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold text-danger"><?= format_vnd($it['subtotal']) ?></td>
                                    <td>
                                        <form action="<?= url('/cart/remove') ?>" method="post"
                                              data-confirm="Xóa sản phẩm này khỏi giỏ?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="cart_key" value="<?= e($it['cart_key']) ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <aside class="panel-card cart-summary-card">
                <div class="panel-header cart-section-header cart-section-header--compact">
                    <div>
                        <h2>Tóm tắt đơn hàng</h2>
                        <p>Voucher sẽ áp dụng ở bước thanh toán.</p>
                    </div>
                </div>
                <div class="cart-summary-row text-muted small">
                    <span>Số lượng sản phẩm</span>
                    <span><?= number_format($totalQty) ?></span>
                </div>
                <div class="cart-summary-row">
                    <span class="text-muted">Tạm tính</span>
                    <strong><?= format_vnd($total) ?></strong>
                </div>
                <div class="cart-summary-row text-muted small">
                    <span>Phí giao hàng</span>
                    <span class="text-success fw-semibold">Miễn phí</span>
                </div>
                <hr>
                <div class="cart-summary-total">
                    <span>Tổng cộng</span>
                    <strong><?= format_vnd($total) ?></strong>
                </div>
                <a href="<?= url('/checkout') ?>" class="btn btn-success btn-lg w-100">
                    Tiến hành đặt hàng <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <div class="cart-summary-note">
                    <i class="bi bi-shield-check me-1"></i>
                    Tồn kho sẽ được kiểm tra lại khi đặt hàng.
                </div>
            </aside>
        </div>
    </div>
<?php endif; ?>
