import './bootstrap';
import './chat-widget';
import './admin-chat';
import QRCode from 'qrcode';

const demoQrCanvas = document.querySelector('[data-demo-qr]');
if (demoQrCanvas) {
    const status = document.querySelector('[data-demo-qr-status]');
    QRCode.toCanvas(demoQrCanvas, demoQrCanvas.dataset.qrPayload, {
        width: 320,
        margin: 2,
        errorCorrectionLevel: 'M',
        color: { dark: '#17211b', light: '#ffffff' },
    }).then(() => {
        if (status) status.textContent = 'QR demo đã sẵn sàng để quét.';
    }).catch(() => {
        if (status) status.textContent = 'Không thể tạo QR. Hãy tải lại trang hoặc dùng nút giả lập bên dưới.';
    });
}

const menuButton = document.querySelector('[data-menu-button]');
const mobilePanel = document.querySelector('[data-mobile-panel]');
menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!open));
    mobilePanel.hidden = open;
    mobilePanel.toggleAttribute('data-open', !open);
});

const adminSidebar = document.getElementById('admin-sidebar');
const adminSidebarTrigger = document.querySelector('[data-admin-sidebar-open]');
const adminSidebarCloseButtons = document.querySelectorAll('[data-admin-sidebar-close]');
const adminSidebarMedia = window.matchMedia('(max-width: 1100px)');

const setAdminSidebarOpen = (open, { restoreFocus = true } = {}) => {
    if (!adminSidebar || !adminSidebarTrigger || !adminSidebarMedia.matches) return;
    document.body.classList.toggle('admin-sidebar-open', open);
    adminSidebarTrigger.setAttribute('aria-expanded', String(open));
    if (open) {
        requestAnimationFrame(() => adminSidebar.querySelector('.admin-sidebar-close')?.focus());
    } else if (restoreFocus) {
        adminSidebarTrigger.focus();
    }
};

adminSidebarTrigger?.addEventListener('click', () => setAdminSidebarOpen(true, { restoreFocus: false }));
adminSidebarCloseButtons.forEach((button) => button.addEventListener('click', () => setAdminSidebarOpen(false)));
adminSidebar?.querySelectorAll('nav a').forEach((link) => link.addEventListener('click', () => {
    if (adminSidebarMedia.matches) setAdminSidebarOpen(false, { restoreFocus: false });
}));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('admin-sidebar-open')) {
        setAdminSidebarOpen(false);
    }
});
adminSidebarMedia.addEventListener?.('change', (event) => {
    if (event.matches) return;
    document.body.classList.remove('admin-sidebar-open');
    adminSidebarTrigger?.setAttribute('aria-expanded', 'false');
});

const searchToggle = document.querySelector('[data-search-toggle]');
const searchPanel = document.querySelector('[data-search-panel]');
const searchInput = document.querySelector('[data-search-input]');
const searchClose = document.querySelector('[data-search-close]');
let searchCloseTimer;

const isSearchOpen = () => searchToggle?.getAttribute('aria-expanded') === 'true';

const openHeaderSearch = () => {
    if (!searchToggle || !searchPanel) return;
    window.clearTimeout(searchCloseTimer);
    searchPanel.hidden = false;
    searchToggle.setAttribute('aria-expanded', 'true');
    searchToggle.classList.add('is-active');
    if (mobilePanel && menuButton) {
        mobilePanel.hidden = true;
        mobilePanel.removeAttribute('data-open');
        menuButton.setAttribute('aria-expanded', 'false');
    }
    requestAnimationFrame(() => {
        searchPanel.classList.add('is-open');
        searchInput?.focus();
    });
};

const closeHeaderSearch = ({ restoreFocus = true } = {}) => {
    if (!searchToggle || !searchPanel || !isSearchOpen()) return;
    searchPanel.classList.remove('is-open');
    searchToggle.classList.remove('is-active');
    searchToggle.setAttribute('aria-expanded', 'false');
    searchCloseTimer = window.setTimeout(() => { searchPanel.hidden = true; }, 180);
    if (restoreFocus) searchToggle.focus();
};

searchToggle?.addEventListener('click', (event) => {
    event.preventDefault();
    if (isSearchOpen()) closeHeaderSearch();
    else openHeaderSearch();
});
searchClose?.addEventListener('click', () => closeHeaderSearch());
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isSearchOpen()) closeHeaderSearch();
});
document.addEventListener('pointerdown', (event) => {
    if (!isSearchOpen() || searchPanel?.contains(event.target) || searchToggle?.contains(event.target)) return;
    closeHeaderSearch({ restoreFocus: false });
});

const toast = document.querySelector('[data-toast]');
if (toast) {
    const closeToast = () => toast.remove();
    toast.querySelector('button')?.addEventListener('click', closeToast);
    window.setTimeout(closeToast, 5000);
}

const revealItems = document.querySelectorAll('[data-reveal]');
if (revealItems.length) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    } else {
        document.documentElement.classList.add('reveal-ready');
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px' });
        revealItems.forEach((item) => revealObserver.observe(item));
    }
}

document.querySelectorAll('.category-mosaic, .product-grid').forEach((group) => {
    group.querySelectorAll(':scope > [data-reveal]').forEach((item, index) => {
        item.style.setProperty('--reveal-delay', `${(index % 2) * 80}ms`);
    });
});

const header = document.querySelector('.site-header');
const headerSentinel = document.querySelector('[data-header-sentinel]');
if (header && headerSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => {
        header.classList.toggle('is-compact', !entry.isIntersecting);
    }).observe(headerSentinel);
}

const cartLink = document.querySelector('[data-cart-link]');
const cartCount = document.querySelector('[data-cart-count]');
const cartStatus = document.querySelector('[data-cart-status]');
const cartToast = document.querySelector('[data-cart-toast]');
let cartToastTimer;

const showCartToast = (message, isError = false) => {
    if (!cartToast) return;
    window.clearTimeout(cartToastTimer);
    cartToast.querySelector('span').textContent = message;
    cartToast.classList.toggle('is-error', isError);
    cartToast.hidden = false;
    requestAnimationFrame(() => cartToast.classList.add('is-visible'));
    cartToastTimer = window.setTimeout(() => {
        cartToast.classList.remove('is-visible');
        window.setTimeout(() => { cartToast.hidden = true; }, 180);
    }, 3600);
};
cartToast?.querySelector('button')?.addEventListener('click', () => {
    window.clearTimeout(cartToastTimer);
    cartToast.classList.remove('is-visible');
    cartToast.hidden = true;
});

const flyProductToCart = (form) => {
    if (!cartLink || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return Promise.resolve();
    const source = form.closest('.product-card')?.querySelector('.product-image img')
        || form.closest('[data-product-options]')?.querySelector('.detail-image img');
    if (!source) return Promise.resolve();

    const sourceRect = source.getBoundingClientRect();
    const targetRect = cartLink.getBoundingClientRect();
    const flyer = source.cloneNode();
    Object.assign(flyer.style, {
        position: 'fixed', zIndex: '120', pointerEvents: 'none', objectFit: 'cover',
        left: `${sourceRect.left}px`, top: `${sourceRect.top}px`, width: `${sourceRect.width}px`,
        height: `${sourceRect.height}px`, borderRadius: '18px', transformOrigin: 'center',
    });
    flyer.setAttribute('aria-hidden', 'true');
    flyer.className = 'cart-flyer';
    document.body.appendChild(flyer);

    const deltaX = targetRect.left + targetRect.width / 2 - (sourceRect.left + sourceRect.width / 2);
    const deltaY = targetRect.top + targetRect.height / 2 - (sourceRect.top + sourceRect.height / 2);
    const animation = flyer.animate([
        { transform: 'translate3d(0, 0, 0) scale(1)', opacity: 0.95 },
        { transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(.12)`, opacity: 0.2 },
    ], { duration: 680, easing: 'cubic-bezier(.22,.8,.24,1)', fill: 'forwards' });
    return animation.finished.catch(() => {}).finally(() => flyer.remove());
};

const updateCartBadge = (count, message = '') => {
    if (!cartCount || !cartLink) return;
    cartCount.textContent = String(count);
    cartCount.hidden = count < 1;
    cartLink.setAttribute('aria-label', `Giỏ hàng, ${count} sản phẩm`);
    cartLink.classList.remove('is-receiving');
    cartCount.classList.remove('is-updating');
    void cartCount.offsetWidth;
    cartLink.classList.add('is-receiving');
    cartCount.classList.add('is-updating');
    window.setTimeout(() => {
        cartLink.classList.remove('is-receiving');
        cartCount.classList.remove('is-updating');
    }, 620);
    if (cartStatus) cartStatus.textContent = `${message} Giỏ hàng hiện có ${count} sản phẩm.`.trim();
};

document.querySelectorAll('[data-cart-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        const button = form.querySelector('[data-cart-button]');
        if (!button || button.disabled || button.getAttribute('aria-busy') === 'true') return;
        event.preventDefault();
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = '<span>Đang thêm…</span>';

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Không thể thêm sản phẩm vào giỏ.');

            button.classList.add('is-success');
            button.innerHTML = '<span>Đã thêm vào giỏ</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>';
            showCartToast(data.message);
            await flyProductToCart(form);
            updateCartBadge(Number(data.cart_count || 0), data.message);
        } catch (error) {
            showCartToast(error.message || 'Không thể thêm sản phẩm vào giỏ.', true);
        } finally {
            window.setTimeout(() => {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.classList.remove('is-success');
                button.innerHTML = originalContent;
            }, 900);
        }
    });
});

const formatCartMoney = (value) => `${Number(value).toLocaleString('vi-VN')}đ`;
const pulseCartValue = (element) => {
    if (!element) return;
    element.classList.remove('is-price-updated');
    void element.offsetWidth;
    element.classList.add('is-price-updated');
    window.setTimeout(() => element.classList.remove('is-price-updated'), 420);
};
const applyCartQuantityResponse = (form, data) => {
    const item = form.closest('[data-cart-item]');
    const lineTotal = item?.querySelector('[data-cart-line-total]');
    if (lineTotal) {
        lineTotal.textContent = formatCartMoney(data.line_total);
        pulseCartValue(lineTotal);
    }
    document.querySelectorAll('[data-cart-subtotal]').forEach((element) => {
        element.textContent = formatCartMoney(data.subtotal);
        pulseCartValue(element);
    });
    document.querySelectorAll('[data-cart-hero-count], [data-cart-items-count]').forEach((element) => {
        element.textContent = String(data.cart_count);
    });
    const progress = document.querySelector('[data-shipping-progress]');
    progress?.style.setProperty('--shipping-progress', `${data.free_shipping_progress}%`);
    const shippingMessage = document.querySelector('[data-shipping-message]');
    if (shippingMessage) {
        shippingMessage.innerHTML = Number(data.free_shipping_remaining) > 0
            ? `Mua thêm <strong>${formatCartMoney(data.free_shipping_remaining)}</strong> để được miễn phí giao hàng.`
            : '<strong>Bạn đã được miễn phí giao hàng.</strong>';
    }
    updateCartBadge(Number(data.cart_count || 0), data.message);
};

document.querySelectorAll('[data-cart-quantity-form]').forEach((form) => {
    const input = form.querySelector('input[name="quantity"]');
    const minus = form.querySelector('[data-cart-qty-minus]');
    const plus = form.querySelector('[data-cart-qty-plus]');
    let committedValue = Number(input.value);
    let requestController;
    let saveTimer;
    let requestVersion = 0;
    const clamp = (value) => Math.min(Number(input.max || 20), Math.max(Number(input.min || 1), value));
    const syncButtonStates = () => {
        const value = Number(input.value);
        minus.disabled = value <= Number(input.min || 1);
        plus.disabled = value >= Number(input.max || 20);
    };
    const save = async () => {
        const version = ++requestVersion;
        requestController = new AbortController();
        const currentController = requestController;
        form.classList.add('is-updating');
        form.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form), signal: currentController.signal,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Không thể cập nhật số lượng. Vui lòng thử lại.');
            if (version !== requestVersion) return;
            input.value = String(data.quantity);
            committedValue = Number(data.quantity);
            syncButtonStates();
            applyCartQuantityResponse(form, data);
        } catch (error) {
            if (error.name === 'AbortError') return;
            input.value = String(committedValue);
            syncButtonStates();
            showCartToast(error.message || 'Không thể cập nhật số lượng. Vui lòng thử lại.', true);
        } finally {
            if (currentController === requestController) {
                form.classList.remove('is-updating');
                form.removeAttribute('aria-busy');
            }
        }
    };
    const queueSave = (delay = 90) => {
        window.clearTimeout(saveTimer);
        requestController?.abort();
        saveTimer = window.setTimeout(save, delay);
    };
    const changeBy = (amount) => {
        const nextValue = clamp(Number(input.value) + amount);
        if (nextValue === Number(input.value)) return;
        input.value = String(nextValue);
        syncButtonStates();
        queueSave();
    };

    minus?.addEventListener('click', () => changeBy(-1));
    plus?.addEventListener('click', () => changeBy(1));
    input?.addEventListener('change', () => {
        input.value = String(clamp(Number(input.value) || 1));
        syncButtonStates();
        queueSave(120);
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        queueSave(0);
    });
    syncButtonStates();
});

const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
const autoplayVideos = document.querySelectorAll('[data-autoplay-video]');
const syncVideoMotion = () => autoplayVideos.forEach((video) => {
    if (motionPreference.matches) {
        video.pause();
        video.removeAttribute('autoplay');
        video.closest('.home-video')?.classList.add('is-paused');
        return;
    }
    video.closest('.home-video')?.classList.remove('is-paused');
    video.setAttribute('autoplay', '');
    video.play().catch(() => {});
});
syncVideoMotion();
motionPreference.addEventListener?.('change', syncVideoMotion);

document.addEventListener('visibilitychange', () => {
    autoplayVideos.forEach((video) => {
        if (document.hidden) video.pause();
        else if (!motionPreference.matches) video.play().catch(() => {});
    });
});

document.querySelectorAll('[data-submit-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-submit-button]');
        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = 'Đang tạo đơn…';
        }
    });
});

const shippingForm = document.querySelector('[data-shipping-form]');
if (shippingForm) {
    const province = shippingForm.querySelector('[data-province]');
    const district = shippingForm.querySelector('[data-district]');
    const ward = shippingForm.querySelector('[data-ward]');
    const districtId = shippingForm.querySelector('[data-district-id]');
    const wardCode = shippingForm.querySelector('[data-ward-code]');
    const quoteBox = shippingForm.querySelector('[data-shipping-quote]');
    const feeOutput = shippingForm.querySelector('[data-shipping-fee]');
    const totalOutput = shippingForm.querySelector('[data-order-total]');
    const couponInput = shippingForm.querySelector('[data-coupon-code]');
    const couponStatus = shippingForm.querySelector('[data-coupon-status]');
    const discountRow = shippingForm.querySelector('[data-discount-row]');
    const discountOutput = shippingForm.querySelector('[data-discount-amount]');
    const couponRemove = shippingForm.querySelector('[data-coupon-remove]');
    const couponDialog = shippingForm.querySelector('[data-coupon-dialog]');
    const couponTrigger = shippingForm.querySelector('[data-coupon-open]');
    const couponOpeners = shippingForm.querySelectorAll('[data-coupon-open], [data-coupon-change]');
    const couponClose = shippingForm.querySelector('[data-coupon-close]');
    const couponConfirm = shippingForm.querySelector('[data-coupon-confirm]');
    const couponApplied = shippingForm.querySelector('[data-coupon-applied]');
    const couponAppliedCode = shippingForm.querySelector('[data-coupon-applied-code]');
    const couponAppliedDiscount = shippingForm.querySelector('[data-coupon-applied-discount]');
    const couponTriggerCopy = shippingForm.querySelector('[data-coupon-trigger-copy]');
    const couponTriggerAction = shippingForm.querySelector('[data-coupon-trigger-action]');
    const pendingLabel = shippingForm.querySelector('[data-coupon-pending-label]');
    const pendingDiscountOutput = shippingForm.querySelector('[data-coupon-pending-discount]');
    const manualCoupon = shippingForm.querySelector('[data-manual-coupon]');
    const manualCouponCheck = shippingForm.querySelector('[data-manual-coupon-check]');
    const manualCouponStatus = shippingForm.querySelector('[data-manual-coupon-status]');
    const subtotal = Number(shippingForm.dataset.subtotal || 0);
    let selectedDiscount = 0;
    let currentShippingFee = null;
    let pendingCouponCode = '';
    let pendingCouponDiscount = 0;
    let couponDialogOpener = null;
    const couponStorageKey = 'pawora_checkout_coupon';
    const money = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
    const normalizeLocation = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/^(thanh pho|tinh|quan|huyen|thi xa|thi tran)\s+/, '')
        .replace(/[^a-z0-9]/g, '');

    const setQuoteEmpty = () => {
        quoteBox.textContent = 'Chọn đủ tỉnh/thành phố, quận/huyện và phường/xã để xem phí giao hàng.';
        feeOutput.textContent = 'Chưa tính';
        currentShippingFee = null;
        totalOutput.textContent = money.format(subtotal - selectedDiscount);
    };

    const loadDistricts = async () => {
        const selected = district.dataset.selected || '';
        district.disabled = true;
        district.innerHTML = `<option value="">${province.value ? 'Đang tải quận/huyện…' : 'Chọn tỉnh/thành phố trước'}</option>`;
        districtId.value = '';
        wardCode.value = '';
        ward.disabled = true;
        ward.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
        setQuoteEmpty();

        if (!province.value) return;

        try {
            const url = new URL(shippingForm.dataset.districtsUrl, window.location.origin);
            url.searchParams.set('province', province.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('districts_failed');
            const data = await response.json();
            district.innerHTML = '<option value="">Chọn quận/huyện</option>';
            data.districts.forEach((item) => {
                const option = new Option(item.name, item.name);
                option.dataset.id = item.id;
                option.selected = selected !== '' && normalizeLocation(item.name) === normalizeLocation(selected);
                district.add(option);
            });
            district.disabled = data.districts.length === 0;
            if (!data.districts.length) district.innerHTML = '<option value="">GHN chưa trả danh sách quận/huyện</option>';
            districtId.value = district.selectedOptions[0]?.dataset.id || '';
            district.dataset.selected = '';
            if (districtId.value) await loadWards();
        } catch {
            district.innerHTML = '<option value="">Không tải được quận/huyện</option>';
        }
    };

    const loadWards = async () => {
        const selected = ward.dataset.selected || '';
        ward.disabled = true;
        ward.innerHTML = `<option value="">${districtId.value ? 'Đang tải phường/xã…' : 'Chọn quận/huyện trước'}</option>`;
        wardCode.value = '';
        setQuoteEmpty();
        if (!districtId.value) return;
        try {
            const url = new URL(shippingForm.dataset.wardsUrl, window.location.origin);
            url.searchParams.set('district_id', districtId.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('wards_failed');
            const data = await response.json();
            ward.innerHTML = '<option value="">Chọn phường/xã/đặc khu</option>';
            data.wards.forEach((item) => {
                const option = new Option(item.name, item.name, false, selected !== '' && normalizeLocation(item.name) === normalizeLocation(selected));
                option.dataset.code = item.code;
                ward.add(option);
            });
            ward.disabled = data.wards.length === 0;
            wardCode.value = ward.selectedOptions[0]?.dataset.code || '';
            if (!wardCode.value && data.wards.length === 1) {
                ward.selectedIndex = 1;
                wardCode.value = ward.selectedOptions[0]?.dataset.code || '';
            }
            ward.dataset.selected = '';
            loadShippingQuote();
        } catch {
            ward.innerHTML = '<option value="">Không tải được danh sách. Vui lòng thử lại</option>';
        }
    };

    const loadShippingQuote = async () => {
        if (!province.value || !districtId.value || !wardCode.value) {
            setQuoteEmpty();
            return;
        }
        quoteBox.setAttribute('aria-busy', 'true');
        quoteBox.textContent = 'Đang tính phí giao hàng…';
        try {
            const url = new URL(shippingForm.dataset.quoteUrl, window.location.origin);
            url.searchParams.set('province', province.value);
            url.searchParams.set('district_id', districtId.value);
            url.searchParams.set('ward_code', wardCode.value);
            if (couponInput?.value) url.searchParams.set('coupon_code', couponInput.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('quote_failed');
            const quote = await response.json();
            currentShippingFee = Number(quote.fee);
            selectedDiscount = Number(quote.discount || selectedDiscount);
            feeOutput.textContent = quote.free_shipping ? 'Miễn phí' : money.format(quote.fee);
            totalOutput.textContent = money.format(Number(quote.total));
            quoteBox.innerHTML = `<strong>${quote.free_shipping ? 'Miễn phí giao hàng' : `Phí giao hàng ${money.format(quote.fee)}`}</strong><span>Dự kiến nhận trong ${quote.estimated_days}.</span>`;
        } catch {
            quoteBox.textContent = 'Chưa tính được phí giao hàng. Vui lòng chọn lại tỉnh/thành phố.';
            feeOutput.textContent = 'Chưa tính';
        } finally {
            quoteBox.removeAttribute('aria-busy');
        }
    };
    province.addEventListener('change', () => {
        district.dataset.selected = '';
        ward.dataset.selected = '';
        loadDistricts();
    });
    district.addEventListener('change', () => {
        districtId.value = district.selectedOptions[0]?.dataset.id || '';
        loadWards();
    });
    ward.addEventListener('change', () => {
        wardCode.value = ward.selectedOptions[0]?.dataset.code || '';
        loadShippingQuote();
    });

    const selectPendingCoupon = (code = '', discount = 0) => {
        pendingCouponCode = String(code || '').trim().toUpperCase();
        pendingCouponDiscount = Number(discount || 0);
        pendingLabel.textContent = pendingCouponCode ? `Đã chọn ${pendingCouponCode}` : 'Chưa chọn mã';
        pendingDiscountOutput.textContent = pendingCouponCode
            ? `Dự kiến giảm ${money.format(pendingCouponDiscount)}`
            : 'Chọn một ưu đãi để tiếp tục';
        couponConfirm.disabled = !pendingCouponCode;
        shippingForm.querySelectorAll('[data-voucher]').forEach((voucher) => {
            const selected = voucher.dataset.voucher === pendingCouponCode;
            voucher.classList.toggle('is-selected', selected);
            voucher.setAttribute('aria-pressed', String(selected));
        });
    };

    const renderCoupon = (code = '', discount = 0) => {
        const normalizedCode = String(code || '').trim().toUpperCase();
        selectedDiscount = Number(discount || 0);
        couponInput.value = normalizedCode;
        if (normalizedCode) sessionStorage.setItem(couponStorageKey, normalizedCode);
        else sessionStorage.removeItem(couponStorageKey);
        discountRow.hidden = !normalizedCode || selectedDiscount <= 0;
        discountOutput.textContent = `-${money.format(selectedDiscount)}`;
        couponTrigger.hidden = Boolean(normalizedCode);
        couponApplied.hidden = !normalizedCode;
        couponAppliedCode.textContent = normalizedCode;
        couponAppliedDiscount.textContent = normalizedCode ? `Giảm ${money.format(selectedDiscount)}` : '';
        couponTriggerCopy.textContent = normalizedCode ? `Đang dùng ${normalizedCode}` : 'Chọn hoặc nhập mã';
        couponTriggerAction.textContent = normalizedCode ? 'Đổi mã' : 'Chọn mã';
        selectPendingCoupon(normalizedCode, selectedDiscount);
        totalOutput.textContent = money.format(subtotal - selectedDiscount + (currentShippingFee || 0));
    };

    const previewCoupon = async (code) => {
        const normalizedCode = String(code || '').trim().toUpperCase();
        if (!normalizedCode) throw new Error('Vui lòng nhập hoặc chọn một mã giảm giá.');
        const url = new URL(shippingForm.dataset.couponPreviewUrl, window.location.origin);
        url.searchParams.set('coupon_code', normalizedCode);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Mã giảm giá không hợp lệ hoặc chưa đủ điều kiện.');
        return data;
    };

    const openCouponDialog = (opener) => {
        if (!couponDialog?.showModal) return;
        couponDialogOpener = opener;
        selectPendingCoupon(couponInput.value, selectedDiscount);
        manualCoupon.value = '';
        manualCouponStatus.textContent = 'Mã sẽ được kiểm tra trực tiếp với hệ thống.';
        manualCouponStatus.classList.remove('is-error', 'is-success');
        couponDialog.showModal();
        document.body.classList.add('coupon-dialog-open');
        requestAnimationFrame(() => {
            const selectedVoucher = couponDialog.querySelector('[data-voucher].is-selected');
            (selectedVoucher || manualCoupon || couponClose)?.focus();
        });
    };

    const closeCouponDialog = () => {
        if (couponDialog?.open) couponDialog.close();
    };

    const setCouponBusy = (busy) => {
        couponConfirm.disabled = busy || !pendingCouponCode;
        couponConfirm.setAttribute('aria-busy', String(busy));
        couponConfirm.textContent = busy ? 'Đang áp dụng...' : 'Áp dụng';
    };

    couponOpeners.forEach((opener) => opener.addEventListener('click', () => openCouponDialog(opener)));
    couponClose?.addEventListener('click', closeCouponDialog);
    couponDialog?.addEventListener('click', (event) => {
        if (event.target === couponDialog) closeCouponDialog();
    });
    couponDialog?.addEventListener('close', () => {
        document.body.classList.remove('coupon-dialog-open');
        couponDialogOpener?.focus();
        couponDialogOpener = null;
    });

    shippingForm.querySelectorAll('[data-voucher]').forEach((voucher) => {
        voucher.addEventListener('click', () => {
            if (voucher.disabled || voucher.dataset.eligible !== 'true') return;
            selectPendingCoupon(voucher.dataset.voucher, voucher.dataset.discount);
            manualCoupon.value = '';
            manualCouponStatus.textContent = `Đã chọn ${voucher.dataset.voucher}. Nhấn Áp dụng để xác nhận.`;
            manualCouponStatus.classList.remove('is-error');
            manualCouponStatus.classList.add('is-success');
        });
    });

    const checkManualCoupon = async () => {
        const code = manualCoupon.value.trim().toUpperCase();
        manualCoupon.value = code;
        manualCouponStatus.textContent = 'Đang kiểm tra mã giảm giá...';
        manualCouponStatus.classList.remove('is-error', 'is-success');
        manualCouponStatus.setAttribute('aria-busy', 'true');
        manualCouponCheck.disabled = true;
        try {
            const data = await previewCoupon(code);
            selectPendingCoupon(data.code, data.discount);
            manualCouponStatus.textContent = `${data.code} hợp lệ. Dự kiến giảm ${money.format(data.discount)}.`;
            manualCouponStatus.classList.add('is-success');
        } catch (error) {
            selectPendingCoupon('', 0);
            manualCouponStatus.textContent = error.message || 'Không thể kiểm tra mã giảm giá. Vui lòng thử lại.';
            manualCouponStatus.classList.add('is-error');
        } finally {
            manualCouponStatus.removeAttribute('aria-busy');
            manualCouponCheck.disabled = false;
        }
    };

    manualCoupon?.addEventListener('input', () => {
        manualCoupon.value = manualCoupon.value.toUpperCase();
        manualCouponStatus.textContent = 'Nhấn Kiểm tra để xác nhận điều kiện của mã.';
        manualCouponStatus.classList.remove('is-error', 'is-success');
    });
    manualCouponCheck?.addEventListener('click', checkManualCoupon);
    manualCoupon?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        checkManualCoupon();
    });

    couponConfirm?.addEventListener('click', async () => {
        if (!pendingCouponCode) return;
        setCouponBusy(true);
        manualCouponStatus.textContent = 'Đang xác nhận ưu đãi với hệ thống...';
        manualCouponStatus.classList.remove('is-error', 'is-success');
        try {
            const data = await previewCoupon(pendingCouponCode);
            renderCoupon(data.code, data.discount);
            couponStatus.textContent = `Đã áp dụng ${data.code}. Bạn được giảm ${money.format(data.discount)}.`;
            if (province.value && districtId.value && wardCode.value) await loadShippingQuote();
            closeCouponDialog();
        } catch (error) {
            manualCouponStatus.textContent = error.message || 'Không thể áp dụng mã giảm giá. Vui lòng thử lại.';
            manualCouponStatus.classList.add('is-error');
        } finally {
            setCouponBusy(false);
        }
    });

    couponRemove?.addEventListener('click', async () => {
        renderCoupon('', 0);
        couponStatus.textContent = 'Đã bỏ mã giảm giá.';
        if (province.value && districtId.value && wardCode.value) await loadShippingQuote();
    });

    const restorableCoupon = couponInput?.value || sessionStorage.getItem(couponStorageKey) || '';
    if (restorableCoupon) {
        previewCoupon(restorableCoupon)
            .then((data) => {
                renderCoupon(data.code, data.discount);
                couponStatus.textContent = `Đã khôi phục mã ${data.code}.`;
            })
            .catch(() => {
                renderCoupon('', 0);
                couponStatus.textContent = 'Mã giảm giá trước đó không còn hợp lệ.';
            });
    }

    shippingForm.querySelectorAll('[data-saved-address]').forEach((choice) => {
        choice.addEventListener('change', () => {
            if (!choice.checked) return;
            shippingForm.querySelector('[name="customer_name"]').value = choice.dataset.receiverName || '';
            shippingForm.querySelector('[name="phone"]').value = choice.dataset.phone || '';
            shippingForm.querySelector('[name="address_line"]').value = choice.dataset.addressLineValue || '';
            province.value = choice.dataset.provinceValue || '';
            district.dataset.selected = choice.dataset.districtValue || '';
            ward.dataset.selected = choice.dataset.wardValue || '';
            loadDistricts();
        });
    });

    shippingForm.querySelector('[data-new-address]')?.addEventListener('change', (event) => {
        if (!event.target.checked) return;
        shippingForm.querySelector('[name="address_line"]').value = '';
        province.value = '';
        district.dataset.selected = '';
        ward.dataset.selected = '';
        loadDistricts();
        province.focus();
    });

    if (province.value) loadDistricts();
}

document.querySelectorAll('[data-address-directory]').forEach((form) => {
    const province = form.querySelector('[data-address-province]');
    const district = form.querySelector('[data-address-district]');
    const ward = form.querySelector('[data-address-ward]');
    const districtId = form.querySelector('[data-address-district-id]');
    const status = form.querySelector('[data-address-directory-status]');
    const normalizeLocation = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/^(thanh pho|tinh|quan|huyen|thi xa|thi tran)\s+/, '')
        .replace(/[^a-z0-9]/g, '');

    const loadWards = async () => {
        const selected = ward.dataset.selected || '';
        ward.disabled = true;
        ward.innerHTML = `<option value="">${districtId.value ? 'Đang tải phường/xã…' : 'Chọn quận/huyện trước'}</option>`;
        if (!districtId.value) return;

        try {
            const url = new URL(form.dataset.wardsUrl, window.location.origin);
            url.searchParams.set('district_id', districtId.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('wards_failed');
            const data = await response.json();
            ward.innerHTML = '<option value="">Chọn phường/xã</option>';
            data.wards.forEach((item) => {
                ward.add(new Option(item.name, item.name, false, selected !== '' && normalizeLocation(item.name) === normalizeLocation(selected)));
            });
            ward.disabled = data.wards.length === 0;
            ward.dataset.selected = '';
            status.textContent = data.wards.length ? 'Đã tải danh sách phường/xã từ GHN.' : 'GHN chưa có danh sách phường/xã cho khu vực này.';
        } catch {
            ward.innerHTML = '<option value="">Không tải được phường/xã — vui lòng thử lại</option>';
            status.textContent = 'Tạm thời không kết nối được GHN. Vui lòng thử lại.';
        }
    };

    const loadDistricts = async () => {
        const selected = district.dataset.selected || '';
        district.disabled = true;
        district.innerHTML = `<option value="">${province.value ? 'Đang tải quận/huyện…' : 'Chọn tỉnh/thành phố trước'}</option>`;
        districtId.value = '';
        ward.disabled = true;
        ward.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
        if (!province.value) return;

        try {
            const url = new URL(form.dataset.districtsUrl, window.location.origin);
            url.searchParams.set('province', province.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('districts_failed');
            const data = await response.json();
            district.innerHTML = '<option value="">Chọn quận/huyện</option>';
            data.districts.forEach((item) => {
                const option = new Option(item.name, item.name, false, selected !== '' && normalizeLocation(item.name) === normalizeLocation(selected));
                option.dataset.id = item.id;
                district.add(option);
            });
            district.disabled = data.districts.length === 0;
            districtId.value = district.selectedOptions[0]?.dataset.id || '';
            district.dataset.selected = '';
            status.textContent = data.districts.length ? 'Chọn tiếp quận/huyện và phường/xã.' : 'GHN chưa có danh sách quận/huyện cho khu vực này.';
            if (districtId.value) await loadWards();
        } catch {
            district.innerHTML = '<option value="">Không tải được quận/huyện — vui lòng thử lại</option>';
            status.textContent = 'Tạm thời không kết nối được GHN. Vui lòng thử lại.';
        }
    };

    province.addEventListener('change', () => {
        district.dataset.selected = '';
        ward.dataset.selected = '';
        loadDistricts();
    });
    district.addEventListener('change', () => {
        districtId.value = district.selectedOptions[0]?.dataset.id || '';
        ward.dataset.selected = '';
        loadWards();
    });
    if (province.value) loadDistricts();
});

const petCatalog = document.querySelector('[data-pet-catalog]');
if (petCatalog) {
    const buttons = [...petCatalog.querySelectorAll('[data-pet-filter]')];
    const products = [...petCatalog.querySelectorAll('[data-pet-product]')];
    const status = petCatalog.querySelector('[data-pet-filter-status]');
    const catalogLink = petCatalog.querySelector('[data-pet-catalog-link]');

    const applyPetFilter = (petType) => {
        buttons.forEach((button) => {
            const active = button.dataset.petFilter === petType;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        const exactMatches = products.filter((item) => item.dataset.petProduct === petType);
        const sharedMatches = products.filter((item) => item.dataset.petProduct === 'all');
        const visible = petType === 'all' ? products.slice(0, 4) : [...exactMatches, ...sharedMatches].slice(0, 4);
        products.forEach((item) => { item.hidden = !visible.includes(item); });

        if (catalogLink) {
            const url = new URL(catalogLink.href);
            if (petType === 'all') url.searchParams.delete('pet_type');
            else url.searchParams.set('pet_type', petType);
            catalogLink.href = url.toString();
            catalogLink.firstChild.textContent = petType === 'dog' ? 'Xem tất cả sản phẩm cho chó ' : petType === 'cat' ? 'Xem tất cả sản phẩm cho mèo ' : 'Xem toàn bộ sản phẩm ';
        }

        if (status) {
            const label = petType === 'dog' ? 'cho chó' : petType === 'cat' ? 'cho mèo' : 'cho mọi thú cưng';
            status.textContent = `Đang hiển thị ${visible.length} sản phẩm ${label}.`;
        }
    };

    buttons.forEach((button) => button.addEventListener('click', () => applyPetFilter(button.dataset.petFilter)));
    document.querySelectorAll('[data-hero-pet]').forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        applyPetFilter(link.dataset.heroPet);
        petCatalog.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
    }));
    applyPetFilter('all');
}

const productOptions = document.querySelector('[data-product-options]');
if (productOptions) {
    const variantsNode = productOptions.querySelector('[data-product-variants]');
    if (variantsNode) {
        const variants = JSON.parse(variantsNode.textContent);
        const colorInputs = [...productOptions.querySelectorAll('[data-option-color]')];
        const sizeInputs = [...productOptions.querySelectorAll('[data-option-size]')];
        const variantId = productOptions.querySelector('[data-variant-id]');
        const price = productOptions.querySelector('[data-current-price]');
        const stock = productOptions.querySelector('[data-variant-stock]');
        const selectedColor = productOptions.querySelector('[data-selected-color]');
        const selectedSize = productOptions.querySelector('[data-selected-size]');
        const summary = productOptions.querySelector('[data-variant-summary] strong');
        const quantity = productOptions.querySelector('[data-quantity]');
        const purchaseButton = productOptions.querySelector('[data-purchase-button]');
        const minus = productOptions.querySelector('[data-quantity-minus]');
        const plus = productOptions.querySelector('[data-quantity-plus]');
        const money = new Intl.NumberFormat('vi-VN');

        const update = () => {
            const color = colorInputs.find((input) => input.checked)?.value;
            const size = sizeInputs.find((input) => input.checked)?.value;
            sizeInputs.forEach((input) => {
                input.disabled = !variants.some((variant) => variant.color === color && variant.size === input.value && variant.stock > 0);
            });
            const variant = variants.find((item) => item.color === color && item.size === size);
            const available = variant?.stock ?? 0;

            selectedColor.textContent = color || '';
            selectedSize.textContent = size || '';
            variantId.value = variant?.id ?? '';
            price.textContent = variant ? `${money.format(variant.price)}đ` : 'Chưa có lựa chọn';
            stock.textContent = available > 0 ? `Còn ${available} sản phẩm` : 'Lựa chọn này tạm hết hàng';
            stock.classList.toggle('low', available > 0 && available < 5);
            quantity.max = String(Math.min(20, available || 1));
            quantity.value = String(Math.min(Math.max(Number(quantity.value) || 1, 1), Number(quantity.max)));
            purchaseButton.disabled = available < 1;
            summary.textContent = variant?.name ?? 'Chưa có lựa chọn phù hợp';
        };

        [...colorInputs, ...sizeInputs].forEach((input) => input.addEventListener('change', update));
        minus?.addEventListener('click', () => {
            quantity.value = String(Math.max(1, (Number(quantity.value) || 1) - 1));
        });
        plus?.addEventListener('click', () => {
            quantity.value = String(Math.min(Number(quantity.max), (Number(quantity.value) || 1) + 1));
        });
        quantity?.addEventListener('change', () => {
            quantity.value = String(Math.min(Number(quantity.max), Math.max(1, Number(quantity.value) || 1)));
        });
        update();
    }
}

const avatarInput = document.querySelector('[data-avatar-input]');
const avatarPreview = document.querySelector('[data-avatar-preview]');
const avatarFilename = document.querySelector('[data-avatar-filename]');
if (avatarInput && avatarPreview) {
    let previewUrl;
    avatarInput.addEventListener('change', () => {
        const [file] = avatarInput.files;
        if (!file || !file.type.startsWith('image/')) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        avatarPreview.innerHTML = '';
        const image = new Image();
        image.src = previewUrl;
        image.alt = 'Xem trước ảnh đại diện mới';
        avatarPreview.append(image);
        if (avatarFilename) avatarFilename.textContent = file.name;
    });
}

const profileForm = document.querySelector('[data-profile-form]');
profileForm?.addEventListener('submit', () => {
    const button = profileForm.querySelector('[data-profile-submit]');
    if (!button) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.textContent = 'Đang lưu…';
});

document.querySelectorAll('[data-shipment-form]').forEach((form) => {
    const status = form.querySelector('[data-shipment-status]');
    const provider = form.querySelector('[data-shipment-provider]');
    const tracking = form.querySelector('[data-tracking-input]');
    const copyTracking = tracking?.parentElement?.querySelector('[data-copy-value]');
    const helper = form.querySelector('[data-tracking-help]');
    const impact = form.querySelector('[data-shipment-impact]');
    const submit = form.querySelector('[data-shipment-submit]');
    const awaitingCustomerReceipt = form.dataset.awaitingCustomerReceipt === 'true';
    if (!status || !provider || !tracking) return;

    const originalTrackingCode = tracking.value;
    const originalProvider = provider.dataset.originalProvider || '';
    const regenerateOnProviderChange = tracking.dataset.regenerateOnProviderChange === 'true';
    const trackingPrefix = (providerName) => {
        const normalized = providerName.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        if (normalized.includes('ghn')) return 'GHN';
        if (normalized.includes('ghtk')) return 'GHTK';
        if (normalized.includes('viettel')) return 'VTP';
        if (normalized.includes('j&t') || normalized.includes('jnt')) return 'JNT';
        if (normalized.includes('buu dien') || normalized.includes('vnpost')) return 'VNPOST';
        if (normalized.includes('demo')) return 'DEMO';
        return 'PAW-SHP';
    };

    const updateTrackingPreview = () => {
        const providerChanged = originalProvider !== '' && provider.value !== originalProvider;
        const willRegenerate = !originalTrackingCode || (providerChanged && regenerateOnProviderChange);
        if (willRegenerate) {
            tracking.value = '';
            tracking.placeholder = `${trackingPrefix(provider.value)}-YYMMDD-XXXXXX — tự tạo khi lưu`;
            if (copyTracking) copyTracking.disabled = true;
            if (helper) helper.textContent = `Bạn không cần nhập. Hệ thống sẽ cấp mã ${trackingPrefix(provider.value)} duy nhất ngay sau khi lưu.`;
            return;
        }

        tracking.value = originalTrackingCode;
        if (copyTracking) copyTracking.disabled = false;
        if (helper) helper.textContent = 'Mã do hệ thống cấp và được giữ nguyên sau khi kiện đã bắt đầu giao.';
    };

    const messages = {
        ready: 'Khởi tạo hồ sơ giao nhận. Trạng thái đơn vẫn là “Đang chuẩn bị” và khách chưa thấy đơn đang được giao.',
        packed: 'Xác nhận kiện đã đóng gói. Trạng thái đơn phía khách sẽ đổi thành “Đã đóng gói”.',
        shipped: 'Bắt đầu giao hàng. Hệ thống tự cấp mã vận đơn và khách sẽ thấy đơn ở trạng thái “Đang giao hàng”.',
        delivered: 'Xác nhận giao thành công. Hệ thống lưu thời điểm giao và bắt đầu tính hạn đổi trả.',
    };

    const updateShipmentGuidance = () => {
        updateTrackingPreview();
        if (impact) impact.textContent = awaitingCustomerReceipt
            ? 'Khách chưa xác nhận nhận hàng. Lần lưu này chỉ cập nhật thông tin vận chuyển và không thể hoàn tất đơn.'
            : (messages[status.value] || 'Hệ thống sẽ lưu thông tin vận chuyển và đồng bộ trạng thái đơn hàng.');
    };

    status.addEventListener('change', updateShipmentGuidance);
    provider.addEventListener('change', updateShipmentGuidance);
    form.addEventListener('submit', () => {
        if (!submit || !form.checkValidity()) return;
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = 'Đang lưu và đồng bộ…';
    });
    updateShipmentGuidance();
});

const adminConfirmDialog = document.getElementById('admin-confirm-dialog');
if (adminConfirmDialog) {
    const title = adminConfirmDialog.querySelector('#admin-confirm-title');
    const message = adminConfirmDialog.querySelector('#admin-confirm-message');
    const accept = adminConfirmDialog.querySelector('[data-confirm-accept]');
    const cancel = adminConfirmDialog.querySelector('[data-confirm-cancel]');
    let pendingForm = null;

    document.querySelectorAll('form[data-confirm-title]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') return;
            event.preventDefault();
            pendingForm = form;
            title.textContent = form.dataset.confirmTitle || 'Xác nhận thao tác';
            message.textContent = form.dataset.confirmMessage || 'Thao tác này sẽ được ghi vào nhật ký quản trị.';
            adminConfirmDialog.showModal();
        });
    });
    cancel?.addEventListener('click', () => {
        pendingForm = null;
        adminConfirmDialog.close();
    });
    accept?.addEventListener('click', () => {
        if (!pendingForm) return;
        const form = pendingForm;
        pendingForm = null;
        adminConfirmDialog.close();
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    });
    adminConfirmDialog.addEventListener('click', (event) => {
        if (event.target === adminConfirmDialog) {
            pendingForm = null;
            adminConfirmDialog.close();
        }
    });
}

document.querySelectorAll('[data-copy-value]').forEach((button) => {
    button.addEventListener('click', async () => {
        const value = button.dataset.copyValue;
        if (!value) return;
        try {
            await navigator.clipboard.writeText(value);
            const original = button.textContent;
            button.textContent = 'Đã sao chép';
            document.querySelector('[data-copy-feedback]')?.replaceChildren('Đã sao chép mã vào bộ nhớ tạm.');
            window.setTimeout(() => { button.textContent = original; }, 1800);
        } catch {
            document.querySelector('[data-copy-feedback]')?.replaceChildren('Không thể sao chép tự động. Vui lòng chọn và sao chép mã.');
        }
    });
});

document.querySelectorAll('[data-catalog-filter-form]').forEach((form) => {
    const toggle = form.querySelector('[data-catalog-filter-toggle]');
    const fields = form.querySelector('.catalog-filter-fields');
    if (!toggle || !fields) return;

    toggle.addEventListener('click', () => {
        const isOpen = fields.dataset.open === 'true';
        if (isOpen) {
            delete fields.dataset.open;
        } else {
            fields.dataset.open = 'true';
        }
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });
});
