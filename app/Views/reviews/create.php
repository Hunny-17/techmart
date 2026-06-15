<?php /** @var array $product */ /** @var array $orders */ /** @var int $selectedOrderId */ ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Đánh giá sản phẩm</h2>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="<?= e($product['image_url'] ?: 'https://placehold.co/72') ?>"
                         width="72" height="72" style="object-fit:cover" alt="">
                    <div>
                        <div class="fw-semibold"><?= e($product['name']) ?></div>
                        <div class="text-danger"><?= format_vnd((float)$product['price']) ?></div>
                    </div>
                </div>

                <form action="<?= url('/products/' . $product['id'] . '/review') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="order_id" class="form-label">Đơn hàng</label>
                        <select name="order_id" id="order_id" class="form-select">
                            <?php foreach ($orders as $order): ?>
                                <option value="<?= e($order['id']) ?>" <?= (int)$selectedOrderId === (int)$order['id'] ? 'selected' : '' ?>>
                                    #<?= e($order['id']) ?> - <?= e(date('d/m/Y H:i', strtotime((string)$order['created_at']))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Số sao</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rating"
                                           id="rating_<?= $i ?>" value="<?= $i ?>"
                                           <?= (int)old('rating', '5') === $i ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rating_<?= $i ?>">
                                        <?= str_repeat('★', $i) ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="comment" class="form-label">Nhận xét</label>
                        <textarea name="comment" id="comment" rows="5" class="form-control"
                                  placeholder="Chia sẻ trải nghiệm của bạn..."><?= old('comment') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/products/' . $product['id']) ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                        <button class="btn btn-primary">
                            <i class="bi bi-star"></i> Gửi đánh giá
                        </button>
                    </div>
                </form>
                <?php clearFormState(); ?>
            </div>
        </div>
    </div>
</div>
