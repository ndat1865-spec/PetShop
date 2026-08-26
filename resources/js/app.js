import './bootstrap';

const menuButton = document.querySelector('[data-menu-button]');
const mobilePanel = document.querySelector('[data-mobile-panel]');
menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!open));
    mobilePanel.hidden = open;
    mobilePanel.toggleAttribute('data-open', !open);
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
    const subtotal = Number(shippingForm.dataset.subtotal || 0);
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
        totalOutput.textContent = money.format(subtotal);
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
            ward.innerHTML = '<option value="">Không tải được danh sách — vui lòng thử lại</option>';
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
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('quote_failed');
            const quote = await response.json();
            feeOutput.textContent = quote.free_shipping ? 'Miễn phí' : money.format(quote.fee);
            totalOutput.textContent = money.format(subtotal + Number(quote.fee));
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
