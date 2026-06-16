<?php
/**
 * @var array  $items
 * @var int    $total
 * @var array  $user
 * @var string $paymentReferenceCode
 * @var array  $availableVouchers
 */
$paymentOld = old('payment_method', 'cod');
$paymentReferenceCode = $paymentReferenceCode ?? '';
$availableVouchers = $availableVouchers ?? [];
$paymentConfig = \App\Core\App::$config['payment'] ?? [];
$paymentAmount = (int)round((float)$total);
$bankQrUrl = '';
if (!empty($paymentConfig['bank_id']) && !empty($paymentConfig['bank_account_no'])) {
    $bankQrUrl = sprintf(
        'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
        rawurlencode((string)$paymentConfig['bank_id']),
        rawurlencode((string)$paymentConfig['bank_account_no']),
        $paymentAmount,
        rawurlencode((string)$paymentReferenceCode),
        rawurlencode((string)$paymentConfig['bank_account_name'])
    );
}
$paymentOptions = [
    'cod' => [
        'title' => 'Xác nhận đặt hàng COD',
    ],
    'bank_transfer' => [
        'title' => 'Thông tin chuyển khoản',
        'instruction' => 'Vui lòng chuyển khoản đúng số tiền và ghi mã thanh toán trong nội dung chuyển khoản.',
        'receiver' => trim(($paymentConfig['bank_name'] ?? '') . ' · STK: ' . ($paymentConfig['bank_account_no'] ?? '') . ' · Chủ TK: ' . ($paymentConfig['bank_account_name'] ?? '')),
        'qr_url' => $bankQrUrl,
    ],
    'e_wallet' => [
        'title' => 'Thông tin thanh toán ví điện tử',
        'instruction' => 'Vui lòng dùng mã thanh toán để chuyển qua ví điện tử và giúp shop đối chiếu nhanh hơn.',
        'receiver' => trim(($paymentConfig['wallet_name'] ?? '') . ' · Tài khoản ví: ' . ($paymentConfig['wallet_account'] ?? '')),
        'qr_url' => (string)($paymentConfig['wallet_qr_url'] ?? ''),
    ],
];
$paymentLabels = [
    'cod' => 'Thanh toán khi nhận hàng',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'e_wallet' => 'Ví điện tử',
];
?>

<section class="page-hero checkout-hero mb-4">
    <div>
        <span class="store-eyebrow">Đặt hàng</span>
        <h1>Xác nhận đơn hàng</h1>
        <p>Kiểm tra thông tin giao hàng và chọn phương thức thanh toán phù hợp.</p>
    </div>
    <div class="checkout-hero-actions">
        <a href="<?= url('/cart') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại giỏ hàng
        </a>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel-card">
            <div class="panel-header">
                <div>
                    <h2>Thông tin giao hàng</h2>
                    <p>Điền địa chỉ, số điện thoại và chọn phương thức thanh toán.</p>
                </div>
                <i class="bi bi-truck"></i>
            </div>
            <div class="p-4">
                <form id="checkout-form" action="<?= url('/checkout') ?>" method="post"
                      data-payment-reference="<?= e($paymentReferenceCode) ?>"
                      data-order-total="<?= e(format_vnd($total)) ?>"
                      data-order-total-raw="<?= e((int)round((float)$total)) ?>"
                      data-current-total-raw="<?= e((int)round((float)$total)) ?>"
                      data-bank-id="<?= e((string)($paymentConfig['bank_id'] ?? '')) ?>"
                      data-bank-account-no="<?= e((string)($paymentConfig['bank_account_no'] ?? '')) ?>"
                      data-bank-account-name="<?= e((string)($paymentConfig['bank_account_name'] ?? '')) ?>"
                      data-payment-options="<?= e(json_encode($paymentOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Địa chỉ giao hàng</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4"
                                  class="form-control" required><?= old('shipping_address', $user['address'] ?? '') ?></textarea>
                        <?php if (errors('shipping_address')): ?>
                            <div class="text-danger small mt-1"><?= e(errors('shipping_address')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input id="phone" name="phone" class="form-control"
                               value="<?= old('phone', $user['phone'] ?? '') ?>" required>
                        <?php if (errors('phone')): ?>
                            <div class="text-danger small mt-1"><?= e(errors('phone')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Phương thức thanh toán</label>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="checkout-payment-option d-flex gap-3 p-3 h-100" for="payment_cod">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method"
                                           id="payment_cod" value="cod" <?= $paymentOld === 'cod' ? 'checked' : '' ?>>
                                    <span>
                                        <span class="d-block fw-semibold"><i class="bi bi-cash-coin me-1"></i> COD</span>
                                        <span class="small text-muted">Thanh toán trực tiếp cho shipper khi nhận hàng.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-payment-option d-flex gap-3 p-3 h-100" for="payment_bank">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method"
                                           id="payment_bank" value="bank_transfer" <?= $paymentOld === 'bank_transfer' ? 'checked' : '' ?>>
                                    <span>
                                        <span class="d-block fw-semibold"><i class="bi bi-bank me-1"></i> Chuyển khoản</span>
                                        <span class="small text-muted">Nhận mã thanh toán và chuyển khoản theo nội dung đó.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="checkout-payment-option d-flex gap-3 p-3 h-100" for="payment_wallet">
                                    <input class="form-check-input mt-1" type="radio" name="payment_method"
                                           id="payment_wallet" value="e_wallet" <?= $paymentOld === 'e_wallet' ? 'checked' : '' ?>>
                                    <span>
                                        <span class="d-block fw-semibold"><i class="bi bi-wallet2 me-1"></i> Ví điện tử</span>
                                        <span class="small text-muted">Dùng mã thanh toán để đối chiếu khi chuyển qua ví.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <?php if (errors('payment_method')): ?>
                            <div class="text-danger small mt-1"><?= e(errors('payment_method')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div id="checkoutPaymentGuide" class="checkout-payment-guide d-none mb-3">
                        <div class="checkout-payment-guide-copy">
                            <div class="small text-muted">Quét mã để thanh toán</div>
                            <h3 id="checkoutPaymentGuideTitle">Thông tin thanh toán</h3>
                            <p id="checkoutPaymentGuideInstruction"></p>
                            <div class="checkout-payment-guide-details">
                                <div>
                                    <span>Mã thanh toán</span>
                                    <strong data-payment-code></strong>
                                </div>
                                <div>
                                    <span>Số tiền</span>
                                    <strong class="text-danger" data-payment-total></strong>
                                </div>
                            </div>
                            <div class="small text-muted mt-2" id="checkoutPaymentGuideReceiver"></div>
                            <button class="btn btn-sm btn-outline-primary mt-3 copy-payment-code" type="button">
                                <i class="bi bi-clipboard me-1"></i> Copy mã
                            </button>
                        </div>
                        <div class="checkout-payment-guide-qr" id="checkoutPaymentGuideQrWrap">
                            <img id="checkoutPaymentGuideQrImage" src="" alt="QR thanh toán">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mã giảm giá</label>
                        <div class="input-group">
                            <input type="text" id="voucher-input" class="form-control text-uppercase"
                                   placeholder="Nhập mã giảm giá..."
                                   autocomplete="off" maxlength="50">
                            <button type="button" id="voucher-apply-btn" class="btn btn-outline-secondary">
                                Áp dụng
                            </button>
                        </div>
                        <div id="voucher-feedback" class="mt-1 small"></div>
                        <?php if (!empty($availableVouchers)): ?>
                            <div class="available-voucher-list mt-3">
                                <div class="available-voucher-heading">
                                    <i class="bi bi-ticket-perforated"></i>
                                    <span>Mã có thể dùng</span>
                                </div>
                                <div class="available-voucher-grid">
                                    <?php foreach ($availableVouchers as $voucher): ?>
                                        <?php
                                            $discountLabel = $voucher['discount_type'] === 'percent'
                                                ? 'Giảm ' . rtrim(rtrim(number_format((float)$voucher['discount_value'], 2, ',', '.'), '0'), ',') . '%'
                                                : 'Giảm ' . format_vnd((float)$voucher['discount_value']);
                                            $minOrder = (float)($voucher['min_order'] ?? 0);
                                        ?>
                                        <button type="button" class="available-voucher-chip" data-voucher-code="<?= e($voucher['code']) ?>">
                                            <strong><?= e($voucher['code']) ?></strong>
                                            <span><?= e($discountLabel) ?></span>
                                            <?php if ($minOrder > 0): ?>
                                                <small>Đơn từ <?= format_vnd($minOrder) ?></small>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="voucher_code" id="voucher-code-hidden">
                    </div>

                    <div class="mb-4">
                        <label for="note" class="form-label">Ghi chú</label>
                        <textarea id="note" name="note" rows="3" class="form-control"
                                  placeholder="Ví dụ: giao giờ hành chính, gọi trước khi giao..."><?= old('note') ?></textarea>
                    </div>

                    <div class="checkout-form-actions">
                        <a href="<?= url('/cart') ?>" class="btn btn-outline-secondary">Hủy</a>
                        <button class="btn btn-success btn-lg" type="submit">
                            Tiếp tục thanh toán <i class="bi bi-arrow-right-circle ms-1"></i>
                        </button>
                    </div>
                </form>
                <?php clearFormState(); ?>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel-card">
            <div class="panel-header">
                <div>
                    <h2>Tóm tắt đơn hàng</h2>
                    <p><?= number_format(count($items)) ?> sản phẩm</p>
                </div>
                <i class="bi bi-bag-check"></i>
            </div>
            <table class="table mb-0 align-middle checkout-summary-table">
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e($item['image_url'] ?: 'https://placehold.co/52') ?>"
                                         class="line-item-image line-item-image-sm" alt="<?= e($item['name']) ?>">
                                    <div>
                                        <div class="fw-semibold"><?= e($item['name']) ?></div>
                                        <?php if (!empty($item['variant_name'])): ?>
                                            <div class="small text-muted">Mẫu: <?= e($item['variant_name']) ?></div>
                                        <?php endif; ?>
                                        <div class="small text-muted">
                                            <?= e($item['quantity']) ?> x <?= format_vnd($item['price']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-semibold"><?= format_vnd($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr id="discount-row" style="display:none">
                        <td class="text-success"><i class="bi bi-ticket-perforated me-1"></i> Giảm giá</td>
                        <td class="text-end text-success fw-semibold" id="discount-amount-cell">-0đ</td>
                    </tr>
                    <tr>
                        <th>Tổng cộng</th>
                        <th class="text-end text-danger fs-5" id="checkout-total-cell"><?= format_vnd($total) ?></th>
                    </tr>
                </tfoot>
            </table>
        </section>

        <div class="checkout-note mt-3">
            <i class="bi bi-shield-check me-1"></i>
            Với chuyển khoản hoặc ví điện tử, đơn sẽ ở trạng thái chờ xử lý cho tới khi shop xác nhận thanh toán.
        </div>
    </div>
</div>

<div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalTitle">Xác nhận đặt hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="paymentCodPanel">
                    <p class="mb-2">Bạn sẽ thanh toán khi nhận hàng.</p>
                    <div class="alert alert-light border mb-0">
                        Tổng tiền cần thanh toán: <strong class="text-danger" data-payment-total></strong>
                    </div>
                </div>

                <div id="paymentCodePanel" class="d-none">
                    <p class="mb-3" id="paymentInstruction"></p>
                    <div class="text-center mb-3" id="paymentQrWrap">
                        <img id="paymentQrImage" class="img-fluid border rounded bg-white p-2" style="max-width: 220px" src="" alt="QR thanh toán">
                    </div>
                    <div class="border rounded p-3 bg-light">
                        <div class="small text-muted">Mã thanh toán</div>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <code class="fs-5 fw-bold" data-payment-code></code>
                            <button class="btn btn-sm btn-outline-primary copy-payment-code" type="button">
                                <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                        </div>
                        <hr>
                        <div class="small text-muted">Số tiền</div>
                        <div class="fw-semibold text-danger" data-payment-total></div>
                        <div class="small text-muted mt-2" id="paymentReceiver"></div>
                    </div>
                    <div class="small text-muted mt-3">
                        Sau khi đặt hàng, mã này cũng sẽ hiển thị trong chi tiết đơn để bạn đối chiếu.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Quay lại</button>
                <button type="button" class="btn btn-success" id="confirmCheckoutSubmit">
                    Xác nhận đặt hàng <i class="bi bi-check2-circle ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= csp_nonce() ?>">
var checkoutForm = document.getElementById('checkout-form');
var paymentModalElement = document.getElementById('paymentConfirmModal');
var paymentModal = window.bootstrap?.Modal ? new bootstrap.Modal(paymentModalElement) : null;
var codPanel = document.getElementById('paymentCodPanel');
var codePanel = document.getElementById('paymentCodePanel');
var modalTitle = document.getElementById('paymentModalTitle');
var instruction = document.getElementById('paymentInstruction');
var receiver = document.getElementById('paymentReceiver');
var qrWrap = document.getElementById('paymentQrWrap');
var qrImage = document.getElementById('paymentQrImage');
var confirmButton = document.getElementById('confirmCheckoutSubmit');
var guide = document.getElementById('checkoutPaymentGuide');
var guideTitle = document.getElementById('checkoutPaymentGuideTitle');
var guideInstruction = document.getElementById('checkoutPaymentGuideInstruction');
var guideReceiver = document.getElementById('checkoutPaymentGuideReceiver');
var guideQrWrap = document.getElementById('checkoutPaymentGuideQrWrap');
var guideQrImage = document.getElementById('checkoutPaymentGuideQrImage');
var copyButton = { addEventListener: () => {} };
let allowSubmit = false;
var paymentContentFromConfig = JSON.parse(checkoutForm.dataset.paymentOptions || '{}');

var paymentContent = {
    cod: {
        title: 'Xác nhận đặt hàng COD',
    },
    bank_transfer: {
        title: 'Thông tin chuyển khoản',
        instruction: 'Vui lòng chuyển khoản đúng số tiền và ghi mã thanh toán trong nội dung chuyển khoản.',
        receiver: 'Người nhận: MB Bank · STK: 100612200517 · Chủ TK: TRAN QUOC HUY',
    },
    e_wallet: {
        title: 'Thông tin thanh toán ví điện tử',
        instruction: 'Vui lòng dùng mã thanh toán để chuyển qua ví điện tử và giúp shop đối chiếu nhanh hơn.',
        receiver: 'Ví nhận: TechMart Pay · Mã ví: TECHMARTPAY',
    },
};

function buildBankQrUrl() {
    const bankId = checkoutForm.dataset.bankId || '';
    const accountNo = checkoutForm.dataset.bankAccountNo || '';
    const accountName = checkoutForm.dataset.bankAccountName || '';
    const amount = Number(String(checkoutForm.dataset.orderTotal || '').replace(/[^\d]/g, '') || checkoutForm.dataset.orderTotalRaw || 0);
    const code = checkoutForm.dataset.paymentReference || '';

    if (!bankId || !accountNo || amount <= 0) {
        return '';
    }

    const path = encodeURIComponent(bankId) + '-' + encodeURIComponent(accountNo) + '-compact2.png';
    const params = new URLSearchParams({
        amount: String(Math.round(amount)),
        addInfo: code,
        accountName,
    });

    return 'https://img.vietqr.io/image/' + path + '?' + params.toString();
}

function selectedPaymentMethod() {
    return checkoutForm.querySelector('input[name="payment_method"]:checked')?.value || 'cod';
}

function paymentContentFor(method) {
    const defaults = paymentContent || {};
    const configured = paymentContentFromConfig || {};
    const content = { ...(defaults[method] || defaults.cod || {}), ...(configured[method] || {}) };
    if (method === 'bank_transfer') {
        content.qr_url = buildBankQrUrl() || content.qr_url || '';
    }

    if (method === 'e_wallet' && !content.qr_url) {
        const bankContent = { ...(defaults.bank_transfer || {}), ...(configured.bank_transfer || {}) };
        content.qr_url = buildBankQrUrl() || '';
        if (content.qr_url && bankContent.receiver) {
            content.receiver = bankContent.receiver;
            content.instruction = 'Ví điện tử chưa cấu hình QR riêng. Vui lòng quét QR chuyển khoản và ghi đúng mã thanh toán để shop đối chiếu.';
        }
    }

    return content;
}

function setPaymentBlocks(method, content) {
    if (!codPanel || !codePanel || !guide) {
        return;
    }

    const total = checkoutForm.dataset.orderTotal;
    const code = checkoutForm.dataset.paymentReference;

    document.querySelectorAll('[data-payment-total]').forEach(el => {
        el.textContent = total;
    });
    document.querySelectorAll('[data-payment-code]').forEach(el => {
        el.textContent = code;
    });

    if (method === 'cod') {
        guide.classList.add('d-none');
        codPanel.classList.remove('d-none');
        codePanel.classList.add('d-none');
        return;
    }

    codPanel.classList.add('d-none');
    codePanel.classList.remove('d-none');
    instruction.textContent = content.instruction || '';
    receiver.textContent = content.receiver || '';

    guide.classList.remove('d-none');
    guideTitle.textContent = content.title || 'Thông tin thanh toán';
    guideInstruction.textContent = content.instruction || '';
    guideReceiver.textContent = content.receiver || '';

    if (content.qr_url) {
        qrImage.src = content.qr_url;
        guideQrImage.src = content.qr_url;
        qrWrap.classList.remove('d-none');
        guideQrWrap.classList.remove('d-none');
    } else {
        qrImage.removeAttribute('src');
        guideQrImage.removeAttribute('src');
        qrWrap.classList.add('d-none');
        guideQrWrap.classList.add('d-none');
    }
}

function refreshPaymentGuide() {
    const method = selectedPaymentMethod();
    setPaymentBlocks(method, paymentContentFor(method));
}

document.querySelectorAll('input[name="payment_method"]').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.checkout-payment-option').forEach(option => option.classList.remove('is-selected'));
        input.closest('.checkout-payment-option')?.classList.add('is-selected');
        refreshPaymentGuide();
    });
    if (input.checked) {
        input.closest('.checkout-payment-option')?.classList.add('is-selected');
    }
});

refreshPaymentGuide();

checkoutForm.addEventListener('submit', event => {
    if (allowSubmit) {
        return;
    }

    event.preventDefault();
    if (!checkoutForm.reportValidity()) {
        return;
    }

    const method = selectedPaymentMethod();
    const content = paymentContentFor(method);

    modalTitle.textContent = content.title;
    setPaymentBlocks(method, content);

    if (paymentModal) {
        paymentModal.show();
        return;
    }

    if (window.confirm('Xác nhận đặt hàng?')) {
        allowSubmit = true;
        checkoutForm.submit();
    }
});

confirmButton.addEventListener('click', () => {
    allowSubmit = true;
    checkoutForm.submit();
});

copyButton.addEventListener('click', async () => {
    const code = checkoutForm.dataset.paymentReference;
    try {
        await navigator.clipboard.writeText(code);
        copyButton.innerHTML = '<i class="bi bi-check2 me-1"></i> Đã copy';
        setTimeout(() => {
            copyButton.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy';
        }, 1500);
    } catch (error) {
        window.prompt('Copy mã thanh toán:', code);
    }
});

document.addEventListener('click', async event => {
    const button = event.target.closest('.copy-payment-code');
    if (!button) {
        return;
    }

    const code = checkoutForm.dataset.paymentReference;
    try {
        await navigator.clipboard.writeText(code);
        button.innerHTML = '<i class="bi bi-check2 me-1"></i> Đã copy';
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy';
        }, 1500);
    } catch (error) {
        window.prompt('Copy mã thanh toán:', code);
    }
});

// Voucher AJAX
const voucherInput     = document.getElementById('voucher-input');
const voucherApplyBtn  = document.getElementById('voucher-apply-btn');
const voucherFeedback  = document.getElementById('voucher-feedback');
const voucherHidden    = document.getElementById('voucher-code-hidden');
const discountRow      = document.getElementById('discount-row');
const discountCell     = document.getElementById('discount-amount-cell');
const totalCell        = document.getElementById('checkout-total-cell');
const voucherChips     = document.querySelectorAll('[data-voucher-code]');

function setActiveVoucherChip(code) {
    voucherChips.forEach(chip => {
        chip.classList.toggle('is-active', chip.dataset.voucherCode === code);
    });
}

voucherChips.forEach(chip => {
    chip.addEventListener('click', () => {
        const code = chip.dataset.voucherCode || '';
        voucherInput.value = code;
        setActiveVoucherChip(code);
        voucherApplyBtn.click();
    });
});

new MutationObserver(refreshPaymentGuide).observe(totalCell, {
    childList: true,
    characterData: true,
    subtree: true,
});

function applyVoucherState(discountFmt, newTotalFmt, newTotalRaw) {
    discountRow.style.display = '';
    discountCell.textContent  = '-' + discountFmt;
    totalCell.textContent     = newTotalFmt;
    checkoutForm.dataset.orderTotal = newTotalFmt; // dùng cho modal confirm
    // orderTotalRaw KHÔNG cập nhật — luôn giữ subtotal gốc để validate lại nếu user đổi mã
}

function resetVoucherState() {
    discountRow.style.display = 'none';
    const originalTotal = checkoutForm.dataset.orderTotalRaw;
    const originalFmt   = new Intl.NumberFormat('vi-VN').format(Number(originalTotal || 0)) + 'đ';
    totalCell.textContent = originalFmt;
    checkoutForm.dataset.orderTotal = originalFmt;
    voucherHidden.value = '';
}

voucherApplyBtn.addEventListener('click', async () => {
    const code  = voucherInput.value.trim().toUpperCase();
    const total = Number(checkoutForm.dataset.orderTotalRaw || 0);
    const base  = window.APP_URL || ''; // đọc lazily — APP_URL đã được set khi handler chạy

    if (!code) {
        voucherFeedback.innerHTML = '<span class="text-danger">Vui lòng nhập mã giảm giá.</span>';
        resetVoucherState();
        return;
    }

    voucherApplyBtn.disabled = true;
    voucherFeedback.innerHTML = '<span class="text-muted">Đang kiểm tra...</span>';

    try {
        const res = await fetch(base + '/voucher/validate?code=' + encodeURIComponent(code) + '&total=' + total, {
            credentials: 'same-origin',
        });
        const data = await res.json();

        if (!data.valid) {
            voucherFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>' + escHtml(data.error) + '</span>';
            setActiveVoucherChip('');
            resetVoucherState();
        } else {
            const discount    = Number(data.discount || 0);
            const newTotal    = Math.max(0, total - discount);
            const discountFmt = new Intl.NumberFormat('vi-VN').format(discount) + 'đ';
            const newTotalFmt = new Intl.NumberFormat('vi-VN').format(newTotal) + 'đ';

            voucherFeedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Áp dụng thành công! Giảm ' + discountFmt + '.</span>';
            voucherHidden.value = code;
            setActiveVoucherChip(code);
            applyVoucherState(discountFmt, newTotalFmt);
        }
    } catch (err) {
        voucherFeedback.innerHTML = '<span class="text-danger">Không thể kết nối. Vui lòng thử lại.</span>';
    } finally {
        voucherApplyBtn.disabled = false;
    }
});

voucherInput.addEventListener('input', () => {
    if (voucherHidden.value !== '') {
        resetVoucherState();
        setActiveVoucherChip('');
        voucherFeedback.innerHTML = '';
    }
});

window.__checkoutPaymentReady = true;
</script>
