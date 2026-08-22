import './bootstrap';

const menuButton = document.querySelector('[data-menu-button]');
const mobilePanel = document.querySelector('[data-mobile-panel]');
menuButton?.addEventListener('click', () => {
    const open = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!open));
    mobilePanel.hidden = open;
    mobilePanel.toggleAttribute('data-open', !open);
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

document.querySelectorAll('[data-cart-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-cart-button]');
        if (!button || button.disabled) return;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Đang thêm…';
    });
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
