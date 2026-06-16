// TechMart frontend JS - vanilla, không phụ thuộc framework

document.addEventListener('DOMContentLoaded', () => {
    refreshCartBadge();
    refreshWishlistBadge();
    initConfirmForms();
    initWishlistToggles();
    initSearchAutocomplete();
    initBackToTop();
    initFlashAutoDismiss();
    initCopyButtons();
    initCompare();
    initHomeHeroCarousel();
});


function initHomeHeroCarousel() {
    const hero = document.querySelector('[data-home-hero]');
    if (!hero) return;

    const slides = Array.from(hero.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
    if (slides.length <= 1) return;

    let current = Math.max(0, slides.findIndex(slide => slide.classList.contains('active')));
    let timer = null;

    const show = index => {
        current = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
    };

    const start = () => {
        stop();
        timer = window.setInterval(() => show(current + 1), 4500);
    };

    const stop = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            show(Number(dot.dataset.heroDot || 0));
            start();
        });
    });

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    show(current);
    start();
}
function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
}

/**
 * Fetch số item trong giỏ và cập nhật badge ở header
 */
async function refreshCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    try {
        const base = window.APP_URL || '';
        const res = await fetch(base + '/cart/count', { credentials: 'same-origin' });
        if (!res.ok) return;
        const { count } = await res.json();
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    } catch (e) {
        // silent fail
    }
}

async function refreshWishlistBadge() {
    const badge = document.getElementById('wishlist-badge');
    if (!badge) return;
    try {
        const base = window.APP_URL || '';
        const res = await fetch(base + '/wishlist/count', { credentials: 'same-origin' });
        if (!res.ok) return;
        const { count } = await res.json();
        updateWishlistBadge(count);
    } catch (e) {
        // silent fail
    }
}

function updateWishlistBadge(count) {
    const badge = document.getElementById('wishlist-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

function initWishlistToggles() {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            e.stopPropagation();

            const productId = btn.dataset.productId;
            const base = window.APP_URL || '';

            try {
                const body = new URLSearchParams({
                    product_id: productId,
                    _token: window.CSRF_TOKEN || '',
                });
                const res = await fetch(base + '/wishlist/toggle', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                });

                if (res.status === 401) {
                    window.location.href = base + '/login';
                    return;
                }
                if (!res.ok) return;

                const { wishlisted, count } = await res.json();
                const icon = btn.querySelector('i');
                const label = btn.querySelector('.wishlist-label');

                if (icon) {
                    icon.className = wishlisted ? 'bi bi-heart-fill' : 'bi bi-heart';
                }
                if (label) {
                    label.textContent = wishlisted ? 'Đã yêu thích' : 'Yêu thích';
                }
                btn.classList.toggle('active', wishlisted);
                btn.title = wishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích';
                btn.dispatchEvent(new CustomEvent('wishlist:toggled', { bubbles: true, detail: { wishlisted } }));
                updateWishlistBadge(count);
            } catch (err) {
                // silent fail
            }
        });
    });
}

function initSearchAutocomplete() {
    const input = document.getElementById('nav-search-input');
    const dropdown = document.getElementById('search-dropdown');
    if (!input || !dropdown) return;

    const base = window.APP_URL || '';
    let timer;

    const hide = () => {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (q.length < 2) { hide(); return; }

        timer = setTimeout(async () => {
            try {
                const res = await fetch(base + '/products/suggest?q=' + encodeURIComponent(q), {
                    credentials: 'same-origin',
                });
                if (!res.ok) { hide(); return; }
                const items = await res.json();
                if (!items.length) { hide(); return; }

                dropdown.innerHTML = items.map(p => `
                    <a href="${base}/products/${p.id}" class="search-autocomplete-item">
                        <img src="${escHtml(p.image_url || 'https://placehold.co/40?text=...')}" alt="">
                        <div>
                            <div class="search-autocomplete-item-name">${escHtml(p.name)}</div>
                            <div class="search-autocomplete-item-price">${fmtVnd(p.price)}</div>
                        </div>
                    </a>
                `).join('');
                dropdown.style.display = 'block';
            } catch (err) {
                hide();
            }
        }, 300);
    });

    input.addEventListener('blur', () => setTimeout(hide, 150));
    input.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) hide();
    });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function fmtVnd(amount) {
    return new Intl.NumberFormat('vi-VN').format(Number(amount || 0)) + 'đ';
}

function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => { btn.innerHTML = original; }, 1500);
            } catch {
                window.prompt('Copy:', text);
            }
        });
    });
}

function initFlashAutoDismiss() {
    document.querySelectorAll('.alert.alert-dismissible').forEach(alertEl => {
        setTimeout(() => {
            const instance = bootstrap?.Alert?.getOrCreateInstance?.(alertEl);
            if (instance) {
                instance.close();
            } else {
                alertEl.classList.remove('show');
            }
        }, 4500);
    });
}

function initBackToTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }, { passive: true });

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// Expose để các trang khác gọi sau khi add/remove
window.refreshCartBadge = refreshCartBadge;

// ── Product comparison ──────────────────────────────────────────────────────

const COMPARE_KEY = 'tm_compare';
const COMPARE_MAX = 3;

function getCompareList() {
    try { return JSON.parse(localStorage.getItem(COMPARE_KEY) || '[]'); }
    catch { return []; }
}

function saveCompareList(list) {
    localStorage.setItem(COMPARE_KEY, JSON.stringify(list));
}

function toggleCompare(id, name, img) {
    const list = getCompareList();
    const idx  = list.findIndex(p => p.id === id);
    if (idx >= 0) {
        list.splice(idx, 1);
    } else {
        if (list.length >= COMPARE_MAX) {
            alert('Chỉ có thể so sánh tối đa ' + COMPARE_MAX + ' sản phẩm.');
            return;
        }
        list.push({ id, name, img });
    }
    saveCompareList(list);
    renderCompareBar();
    syncCompareButtons();
}

function renderCompareBar() {
    const bar = document.getElementById('compare-bar');
    if (!bar) return;
    const list  = getCompareList();
    const slots = bar.querySelectorAll('.compare-slot');

    slots.forEach((slot, i) => {
        if (list[i]) {
            const imgEl = list[i].img
                ? `<img src="${escHtml(list[i].img)}" alt="" class="compare-slot-thumb">`
                : `<span class="compare-slot-no-img"><i class="bi bi-image"></i></span>`;
            slot.innerHTML = `${imgEl}<span class="compare-slot-name">${escHtml(list[i].name)}</span>
                <button class="compare-slot-remove" data-idx="${i}" title="Xóa">&times;</button>`;
            slot.classList.add('filled');
        } else {
            slot.innerHTML = '<span class="compare-slot-empty">+ Thêm sản phẩm</span>';
            slot.classList.remove('filled');
        }
    });

    bar.querySelectorAll('.compare-slot-remove').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const updated = getCompareList();
            updated.splice(parseInt(btn.dataset.idx), 1);
            saveCompareList(updated);
            renderCompareBar();
            syncCompareButtons();
        });
    });

    const goBtn = bar.querySelector('.compare-bar-go');
    if (goBtn) {
        goBtn.href = (window.APP_URL || '') + '/compare?ids=' + list.map(p => p.id).join(',');
    }

    bar.classList.toggle('visible', list.length > 0);
}

function syncCompareButtons() {
    const ids = getCompareList().map(p => p.id);
    document.querySelectorAll('[data-compare-id]').forEach(btn => {
        const active = ids.includes(btn.dataset.compareId);
        btn.classList.toggle('compare-active', active);
        const label = btn.querySelector('.compare-label');
        if (label) label.textContent = active ? 'Đang so sánh' : 'So sánh';
        btn.title = active ? 'Xóa khỏi so sánh' : 'So sánh';
    });
}

function initCompare() {
    renderCompareBar();
    syncCompareButtons();

    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-compare-id]');
        if (btn) {
            toggleCompare(btn.dataset.compareId, btn.dataset.compareName, btn.dataset.compareImg || '');
            return;
        }
        const clearBtn = e.target.closest('.compare-bar-clear');
        if (clearBtn) {
            saveCompareList([]);
            renderCompareBar();
            syncCompareButtons();
        }
        // Remove button on compare page
        const removeBtn = e.target.closest('.compare-remove-btn');
        if (removeBtn) {
            const id = removeBtn.dataset.compareId;
            const list = getCompareList().filter(p => p.id !== id);
            saveCompareList(list);
            renderCompareBar();
            syncCompareButtons();
            // Reload compare page with updated ids
            const ids = list.map(p => p.id).join(',');
            window.location.href = (window.APP_URL || '') + '/compare' + (ids ? '?ids=' + ids : '');
        }
    });
}
