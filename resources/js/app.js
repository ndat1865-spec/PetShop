import './bootstrap';
import './chat-widget';
import './admin-chat';
import './product-content-admin';
import { initSepayPayment } from './sepay-payment';
import '../css/sepay-payment.css';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.querySelectorAll('[data-sepay-payment]').forEach((element) => initSepayPayment(element));

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

const homeHero = document.querySelector('[data-home-hero]');
if (homeHero) {
    const root = document.documentElement;
    root.classList.add('gsap-ready');

    const heroVideo = homeHero.querySelector('[data-home-hero-media] video');
    const heroCopy = homeHero.querySelector('[data-home-hero-copy]');
    const heroKicker = homeHero.querySelector('[data-hero-kicker]');
    const heroLines = homeHero.querySelectorAll('[data-hero-line]');
    const heroDescription = homeHero.querySelector('[data-hero-description]');
    const heroActions = homeHero.querySelector('[data-hero-actions]');
    const heroPetNav = homeHero.querySelector('[data-hero-pet-nav]');
    const homeBridge = document.querySelector('[data-home-bridge]');
    const homeBridgeInner = homeBridge?.querySelector('.home-bridge__inner');
    const homeMetrics = [...(homeBridge?.querySelectorAll('dt') ?? [])];
    const homeNumberFormatter = new Intl.NumberFormat('vi-VN');
    homeMetrics.forEach((metric) => { metric.dataset.originalValue = metric.textContent.trim(); });
    const homeMotion = gsap.matchMedia();
    let heroIntroPlayed = false;

    homeMotion.add(
        {
            isDesktop: '(min-width: 768px)',
            isMobile: '(max-width: 767px)',
            reduceMotion: '(prefers-reduced-motion: reduce)',
        },
        (context) => {
            const { isDesktop, isMobile, reduceMotion } = context.conditions;
            const heroEntranceTargets = [heroKicker, ...heroLines, heroDescription, heroActions, heroPetNav].filter(Boolean);

            if (reduceMotion) {
                root.classList.remove('home-motion-pending');
                homeHero.classList.remove('is-intro-animating');
                gsap.set([heroVideo, heroCopy, homeBridgeInner, ...heroEntranceTargets].filter(Boolean), { clearProps: 'all' });
                homeMetrics.forEach((metric) => { metric.textContent = metric.dataset.originalValue; });
                return undefined;
            }

            if (!heroIntroPlayed) {
                homeHero.classList.add('is-intro-animating');
                gsap.set(heroKicker, { autoAlpha: 0, y: 14 });
                gsap.set(heroLines, { yPercent: 112 });
                gsap.set([heroDescription, heroActions], { autoAlpha: 0, y: 20 });
                gsap.set(heroPetNav, { autoAlpha: 0, y: 18 });
                root.classList.remove('home-motion-pending');

                const intro = gsap.timeline({
                    defaults: { duration: isMobile ? 0.44 : 0.5, ease: 'power2.out' },
                    onComplete: () => homeHero.classList.remove('is-intro-animating'),
                });
                intro
                    .addLabel('start', 0)
                    .to(heroKicker, { autoAlpha: 1, y: 0 }, 'start+=0.04')
                    .to(heroLines, { yPercent: 0, stagger: 0.07 }, 'start+=0.1')
                    .to(heroDescription, { autoAlpha: 1, y: 0 }, 'start+=0.34')
                    .to(heroActions, { autoAlpha: 1, y: 0 }, 'start+=0.46')
                    .to(heroPetNav, { autoAlpha: 1, y: 0, duration: 0.46 }, 'start+=0.58')
                    .set([heroKicker, ...heroLines, heroDescription, heroActions, heroPetNav], { clearProps: 'opacity,visibility,transform' });
                heroIntroPlayed = true;
            } else {
                homeHero.classList.remove('is-intro-animating');
                gsap.set(heroEntranceTargets, { clearProps: 'opacity,visibility,transform' });
                gsap.set(heroVideo, { clearProps: 'transform' });
            }

            if (isDesktop) {
                const heroExit = gsap.timeline({
                    scrollTrigger: {
                        trigger: homeHero,
                        start: 'top top',
                        end: 'bottom top',
                        scrub: 0.8,
                    },
                });
                heroExit
                    .to(heroVideo, { yPercent: 8, scale: 1.04, ease: 'none' }, 0)
                    .to(heroCopy, { y: -64, autoAlpha: 0.34, ease: 'none' }, 0);
                if (homeBridgeInner) {
                    heroExit.fromTo(homeBridgeInner, { y: 48 }, { y: 0, ease: 'none' }, 0);
                }
            }

            const headings = [...document.querySelectorAll('[data-home-reveal]')];
            const featuredProducts = [...document.querySelectorAll('[data-home-product-card]:not([hidden])')];
            const newProducts = [...document.querySelectorAll('[data-home-product-group] > .product-card')];
            const productCards = [...featuredProducts, ...newProducts];
            const categoryCards = [...document.querySelectorAll('[data-home-category-card]')];
            const categoryImages = categoryCards.map((card) => card.querySelector('img')).filter(Boolean);
            const promises = [...document.querySelectorAll('[data-home-promise]')];

            gsap.set(headings, { autoAlpha: 0, y: 30 });
            gsap.set(productCards, { autoAlpha: 0, y: 24 });
            gsap.set(categoryCards, { autoAlpha: 0, y: 28, clipPath: 'inset(8% 0% 8% 0%)' });
            gsap.set(categoryImages, { scale: 1.055 });
            gsap.set(promises, { autoAlpha: 0, y: 20 });

            const revealAnimations = [];
            const revealTypes = new Map([
                ...headings.map((element) => [element, 'heading']),
                ...productCards.map((element) => [element, 'product']),
                ...categoryCards.map((element) => [element, 'category']),
                ...promises.map((element) => [element, 'promise']),
                ...homeMetrics.map((element) => [element, 'metric']),
            ]);

            const animateBatch = (targets, vars = {}) => {
                if (!targets.length) return;
                const animation = gsap.to(targets, {
                    autoAlpha: 1,
                    y: 0,
                    clipPath: 'inset(0% 0% 0% 0%)',
                    duration: 0.7,
                    ease: 'power3.out',
                    stagger: 0.08,
                    overwrite: 'auto',
                    ...vars,
                    onComplete: () => gsap.set(targets, { clearProps: 'opacity,visibility,transform,clipPath' }),
                });
                revealAnimations.push(animation);
            };

            const revealObserver = 'IntersectionObserver' in window
                ? new IntersectionObserver((entries, observer) => {
                    const groups = { heading: [], product: [], category: [], promise: [], metric: [] };
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        groups[revealTypes.get(entry.target)]?.push(entry.target);
                        observer.unobserve(entry.target);
                    });

                    animateBatch(groups.heading, { duration: 0.72 });
                    animateBatch(groups.product);
                    animateBatch(groups.category);
                    animateBatch(groups.promise, { duration: 0.58, stagger: 0.07 });

                    const visibleCategoryImages = groups.category.map((card) => card.querySelector('img')).filter(Boolean);
                    if (visibleCategoryImages.length) {
                        revealAnimations.push(gsap.to(visibleCategoryImages, {
                            scale: 1,
                            duration: 0.95,
                            ease: 'power2.out',
                            stagger: 0.06,
                            onComplete: () => gsap.set(visibleCategoryImages, { clearProps: 'transform' }),
                        }));
                    }

                    groups.metric.forEach((metric) => {
                        if (metric.dataset.counted === 'true') return;
                        const rawValue = metric.dataset.originalValue;
                        const match = rawValue.match(/^([\d.,]+)(.*)$/);
                        if (!match) return;
                        const target = Number(match[1].replace(/[^\d]/g, ''));
                        const suffix = match[2];
                        const counter = { value: 0 };
                        revealAnimations.push(gsap.to(counter, {
                            value: target,
                            duration: 1.05,
                            ease: 'power2.out',
                            snap: { value: 1 },
                            onUpdate: () => {
                                metric.textContent = `${homeNumberFormatter.format(counter.value)}${suffix}`;
                            },
                            onComplete: () => {
                                metric.dataset.counted = 'true';
                                metric.textContent = rawValue;
                            },
                        }));
                    });
                }, { threshold: 0.01, rootMargin: '0px 0px -12%' })
                : null;

            if (revealObserver) {
                revealTypes.forEach((type, element) => revealObserver.observe(element));
            } else {
                gsap.set([...headings, ...productCards, ...categoryCards, ...promises], { clearProps: 'opacity,visibility,transform,clipPath' });
                gsap.set(categoryImages, { clearProps: 'transform' });
            }

            return () => {
                revealObserver?.disconnect();
                revealAnimations.forEach((animation) => animation.kill());
            };
        },
    );

    window.addEventListener('pagehide', () => {
        root.classList.remove('home-motion-pending');
        homeHero.classList.remove('is-intro-animating');
        homeMotion.revert();
    }, { once: true });
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

document.querySelectorAll('[data-checkout-review-dialog]').forEach((dialog) => {
    const form = dialog.closest('form');
    const closeButton = dialog.querySelector('[data-checkout-review-close]');
    const editButton = dialog.querySelector('[data-checkout-review-edit]');
    const confirmButton = dialog.querySelector('[data-checkout-review-confirm]');
    let opener = null;

    if (!form || typeof dialog.showModal !== 'function') return;

    const fieldValue = (name) => form.elements.namedItem(name)?.value?.trim() || '—';
    const selectedText = (selector) => {
        const field = form.querySelector(selector);
        return field?.selectedOptions?.[0]?.textContent?.trim() || field?.value?.trim() || '—';
    };
    const outputText = (selector, fallback = '—') => form.querySelector(selector)?.textContent?.trim() || fallback;
    const fillReview = () => {
        const name = fieldValue('customer_name');
        const phone = fieldValue('phone');
        const email = fieldValue('email');
        const address = [fieldValue('address_line'), selectedText('[data-ward]'), selectedText('[data-district]'), selectedText('[data-province]')]
            .filter((value) => value !== '—')
            .join(', ');
        const payment = form.querySelector('[name="payment_method"]:checked')?.closest('.payment-option')?.querySelector('strong')?.textContent?.trim() || '—';

        dialog.querySelector('[data-review-contact]').textContent = `${name} · ${phone} · ${email}`;
        dialog.querySelector('[data-review-address]').textContent = address || '—';
        dialog.querySelector('[data-review-payment]').textContent = payment;
        dialog.querySelector('[data-review-discount]').textContent = outputText('[data-discount-amount]', '0đ');
        dialog.querySelector('[data-review-shipping]').textContent = outputText('[data-shipping-fee]', 'Chưa tính');
        const shippingDiscountRow = dialog.querySelector('[data-review-shipping-discount-row]');
        if (shippingDiscountRow) {
            shippingDiscountRow.hidden = form.querySelector('[data-shipping-discount-row]')?.hidden ?? true;
            dialog.querySelector('[data-review-shipping-discount]').textContent = outputText('[data-shipping-discount-amount]', '0đ');
        }
        dialog.querySelector('[data-review-total]').textContent = outputText('[data-order-total]');
    };
    const closeReview = () => {
        if (dialog.open) dialog.close();
    };

    form.addEventListener('submit', (event) => {
        if (form.dataset.checkoutConfirmed === 'true') return;
        event.preventDefault();
        fillReview();
        opener = form.querySelector('[data-submit-button]');
        dialog.showModal();
        document.body.classList.add('checkout-review-open');
        requestAnimationFrame(() => editButton?.focus());
    });
    closeButton?.addEventListener('click', closeReview);
    editButton?.addEventListener('click', closeReview);
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) closeReview();
    });
    dialog.addEventListener('close', () => {
        document.body.classList.remove('checkout-review-open');
        opener?.focus();
        opener = null;
    });
    dialog.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') return;
        const focusable = [...dialog.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
            .filter((element) => element.offsetParent !== null);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
    confirmButton?.addEventListener('click', () => {
        if (form.dataset.checkoutConfirmed === 'true') return;
        form.dataset.checkoutConfirmed = 'true';
        confirmButton.disabled = true;
        confirmButton.setAttribute('aria-busy', 'true');
        confirmButton.textContent = 'Đang tạo đơn…';
        form.requestSubmit();
    });
});

document.querySelectorAll('[data-submit-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        const button = form.querySelector('[data-submit-button]');
        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = button.dataset.loadingLabel || 'Đang xử lý…';
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
    const couponStatus = shippingForm.querySelector('[data-coupon-status]');
    const discountRow = shippingForm.querySelector('[data-discount-row]');
    const discountOutput = shippingForm.querySelector('[data-discount-amount]');
    const shippingDiscountRow = shippingForm.querySelector('[data-shipping-discount-row]');
    const shippingDiscountOutput = shippingForm.querySelector('[data-shipping-discount-amount]');
    const couponDialog = shippingForm.querySelector('[data-coupon-dialog]');
    const couponDialogTitle = shippingForm.querySelector('[data-coupon-dialog-title]');
    const couponDialogDescription = shippingForm.querySelector('[data-coupon-dialog-description]');
    const couponClose = shippingForm.querySelector('[data-coupon-close]');
    const couponConfirm = shippingForm.querySelector('[data-coupon-confirm]');
    const pendingLabel = shippingForm.querySelector('[data-coupon-pending-label]');
    const pendingDiscountOutput = shippingForm.querySelector('[data-coupon-pending-discount]');
    const manualCoupon = shippingForm.querySelector('[data-manual-coupon]');
    const manualCouponCheck = shippingForm.querySelector('[data-manual-coupon-check]');
    const manualCouponStatus = shippingForm.querySelector('[data-manual-coupon-status]');
    const voucherOptions = [...shippingForm.querySelectorAll('[data-voucher]')];
    const voucherCount = shippingForm.querySelector('[data-voucher-count]');
    const voucherEmpty = shippingForm.querySelector('[data-voucher-empty]');
    const subtotal = Number(shippingForm.dataset.subtotal || 0);
    const slotCopy = {
        order: { title: 'Chọn mã giảm giá', description: 'Giảm trực tiếp vào tiền hàng. Mỗi đơn dùng 1 mã.', placeholder: 'Nhập mã giảm giá' },
        shipping: { title: 'Chọn mã vận chuyển', description: 'Giảm phí giao hàng, dùng được cùng mã giảm giá.', placeholder: 'Nhập mã vận chuyển' },
    };
    // Mỗi ô giữ một mã: "order" trừ vào tiền hàng, "shipping" trừ vào phí giao hàng.
    const couponSlots = Object.fromEntries([...shippingForm.querySelectorAll('[data-coupon-slot]')].map((root) => {
        const name = root.dataset.couponSlot;
        return [name, {
            name,
            emptyCopy: root.dataset.emptyCopy || 'Chọn hoặc nhập mã',
            input: root.querySelector('[data-coupon-code]'),
            trigger: root.querySelector('[data-coupon-open]'),
            applied: root.querySelector('[data-coupon-applied]'),
            appliedCode: root.querySelector('[data-coupon-applied-code]'),
            appliedDiscount: root.querySelector('[data-coupon-applied-discount]'),
            triggerCopy: root.querySelector('[data-coupon-trigger-copy]'),
            openers: root.querySelectorAll('[data-coupon-open], [data-coupon-change]'),
            remove: root.querySelector('[data-coupon-remove]'),
            storageKey: name === 'order' ? 'pawora_checkout_coupon' : `pawora_checkout_coupon_${name}`,
            code: '',
            discount: 0,
            headline: '',
        }];
    }));
    let currentShippingFee = null;
    let activeSlot = 'order';
    let pendingCouponCode = '';
    let couponDialogOpener = null;
    const money = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
    const normalizeLocation = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/^(thanh pho|tinh|quan|huyen|thi xa|thi tran)\s+/, '')
        .replace(/[^a-z0-9]/g, '');

    const hasAddress = () => Boolean(province.value && districtId.value && wardCode.value);
    const orderDiscount = () => (couponSlots.order?.code ? couponSlots.order.discount : 0);
    const shippingDiscount = () => (couponSlots.shipping?.code && currentShippingFee !== null ? couponSlots.shipping.discount : 0);

    const renderTotals = () => {
        const discount = orderDiscount();
        const shipDiscount = shippingDiscount();
        discountRow.hidden = discount <= 0;
        discountOutput.textContent = `-${money.format(discount)}`;
        shippingDiscountRow.hidden = shipDiscount <= 0;
        shippingDiscountOutput.textContent = `-${money.format(shipDiscount)}`;
        totalOutput.textContent = money.format(subtotal - discount + (currentShippingFee || 0) - shipDiscount);
    };

    const appliedCopy = (slot) => {
        if (slot.name !== 'shipping') return `Giảm ${money.format(slot.discount)}`;
        if (currentShippingFee === null) return `${slot.headline} · tính khi chọn địa chỉ`;
        return slot.discount > 0 ? `Giảm ${money.format(slot.discount)} phí ship` : 'Đơn đang được miễn phí ship';
    };

    const renderSlot = (slot) => {
        const hasCode = Boolean(slot.code);
        slot.input.value = slot.code;
        if (hasCode) sessionStorage.setItem(slot.storageKey, slot.code);
        else sessionStorage.removeItem(slot.storageKey);
        slot.trigger.hidden = hasCode;
        slot.applied.hidden = !hasCode;
        slot.appliedCode.textContent = slot.code;
        slot.appliedDiscount.textContent = hasCode ? appliedCopy(slot) : '';
        slot.triggerCopy.textContent = hasCode ? `Đang dùng ${slot.code}` : slot.emptyCopy;
    };

    const applyCoupon = (slotName, code = '', discount = 0, headline = '') => {
        const slot = couponSlots[slotName];
        if (!slot) return;
        slot.code = String(code || '').trim().toUpperCase();
        slot.discount = Number(discount || 0);
        slot.headline = headline || '';
        renderSlot(slot);
        renderTotals();
    };

    const setQuoteEmpty = () => {
        quoteBox.textContent = 'Chọn đủ tỉnh/thành phố, quận/huyện và phường/xã để xem phí giao hàng.';
        feeOutput.textContent = 'Chưa tính';
        currentShippingFee = null;
        if (couponSlots.shipping) renderSlot(couponSlots.shipping);
        renderTotals();
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
        if (!hasAddress()) {
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
            if (couponSlots.order?.code) url.searchParams.set('coupon_code', couponSlots.order.code);
            if (couponSlots.shipping?.code) url.searchParams.set('shipping_coupon_code', couponSlots.shipping.code);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            const quote = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(quote.message || '');
            currentShippingFee = Number(quote.fee);
            if (couponSlots.order?.code) couponSlots.order.discount = Number(quote.discount || 0);
            if (couponSlots.shipping?.code) couponSlots.shipping.discount = Number(quote.shipping_discount || 0);
            Object.values(couponSlots).forEach(renderSlot);
            feeOutput.textContent = quote.free_shipping ? 'Miễn phí' : money.format(quote.fee);
            renderTotals();
            quoteBox.innerHTML = `<strong>${quote.free_shipping ? 'Miễn phí giao hàng' : `Phí giao hàng ${money.format(quote.fee)}`}</strong><span>Dự kiến nhận trong ${quote.estimated_days}.</span>`;
        } catch (error) {
            currentShippingFee = null;
            quoteBox.textContent = error.message || 'Chưa tính được phí giao hàng. Vui lòng chọn lại tỉnh/thành phố.';
            feeOutput.textContent = 'Chưa tính';
            renderTotals();
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

    const pendingHint = (slotName, discount, headline) => (slotName === 'shipping'
        ? headline || 'Giảm phí vận chuyển'
        : `Dự kiến giảm ${money.format(Number(discount || 0))}`);

    const selectPendingCoupon = (code = '', hint = '') => {
        pendingCouponCode = String(code || '').trim().toUpperCase();
        pendingLabel.textContent = pendingCouponCode ? `Đã chọn ${pendingCouponCode}` : 'Chưa chọn mã';
        pendingDiscountOutput.textContent = pendingCouponCode ? hint : 'Chọn một ưu đãi để tiếp tục';
        couponConfirm.disabled = !pendingCouponCode;
        voucherOptions.forEach((voucher) => {
            const selected = voucher.dataset.voucher === pendingCouponCode;
            voucher.classList.toggle('is-selected', selected);
            voucher.setAttribute('aria-pressed', String(selected));
        });
    };

    const previewCoupon = async (code, slotName) => {
        const normalizedCode = String(code || '').trim().toUpperCase();
        if (!normalizedCode) throw new Error('Vui lòng nhập hoặc chọn một mã giảm giá.');
        const url = new URL(shippingForm.dataset.couponPreviewUrl, window.location.origin);
        url.searchParams.set('coupon_code', normalizedCode);
        url.searchParams.set('slot', slotName);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Mã giảm giá không hợp lệ hoặc chưa đủ điều kiện.');
        return data;
    };

    const openCouponDialog = (slotName, opener) => {
        if (!couponDialog?.showModal) return;
        activeSlot = slotName;
        couponDialogOpener = opener;
        const copy = slotCopy[slotName] || slotCopy.order;
        couponDialogTitle.textContent = copy.title;
        couponDialogDescription.textContent = copy.description;
        manualCoupon.placeholder = copy.placeholder;
        let visible = 0;
        voucherOptions.forEach((voucher) => {
            voucher.hidden = voucher.dataset.slot !== slotName;
            if (!voucher.hidden) visible += 1;
        });
        voucherCount.textContent = `${visible} mã`;
        voucherEmpty.hidden = visible > 0;
        const slot = couponSlots[slotName];
        selectPendingCoupon(slot.code, slot.code ? pendingHint(slotName, slot.discount, slot.headline) : '');
        manualCoupon.value = '';
        manualCouponStatus.textContent = 'Mã sẽ được kiểm tra trực tiếp với hệ thống.';
        manualCouponStatus.classList.remove('is-error', 'is-success');
        couponDialog.showModal();
        document.body.classList.add('coupon-dialog-open');
        requestAnimationFrame(() => {
            const selectedVoucher = couponDialog.querySelector('[data-voucher].is-selected:not([hidden])');
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

    couponClose?.addEventListener('click', closeCouponDialog);
    couponDialog?.addEventListener('click', (event) => {
        if (event.target === couponDialog) closeCouponDialog();
    });
    couponDialog?.addEventListener('close', () => {
        document.body.classList.remove('coupon-dialog-open');
        couponDialogOpener?.focus();
        couponDialogOpener = null;
    });

    voucherOptions.forEach((voucher) => {
        voucher.addEventListener('click', () => {
            if (voucher.disabled || voucher.dataset.eligible !== 'true') return;
            selectPendingCoupon(voucher.dataset.voucher, pendingHint(voucher.dataset.slot, voucher.dataset.discount, voucher.dataset.headline));
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
            const data = await previewCoupon(code, activeSlot);
            const hint = pendingHint(activeSlot, data.discount, data.headline);
            selectPendingCoupon(data.code, hint);
            manualCouponStatus.textContent = `${data.code} hợp lệ. ${hint}.`;
            manualCouponStatus.classList.add('is-success');
        } catch (error) {
            selectPendingCoupon('', '');
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
        const slotName = activeSlot;
        setCouponBusy(true);
        manualCouponStatus.textContent = 'Đang xác nhận ưu đãi với hệ thống...';
        manualCouponStatus.classList.remove('is-error', 'is-success');
        try {
            const data = await previewCoupon(pendingCouponCode, slotName);
            applyCoupon(slotName, data.code, data.discount, data.headline);
            couponStatus.textContent = slotName === 'shipping'
                ? `Đã áp dụng ${data.code}: ${data.headline}.`
                : `Đã áp dụng ${data.code}. Bạn được giảm ${money.format(data.discount)}.`;
            if (hasAddress()) await loadShippingQuote();
            closeCouponDialog();
        } catch (error) {
            manualCouponStatus.textContent = error.message || 'Không thể áp dụng mã giảm giá. Vui lòng thử lại.';
            manualCouponStatus.classList.add('is-error');
        } finally {
            setCouponBusy(false);
        }
    });

    Object.values(couponSlots).forEach((slot) => {
        slot.openers.forEach((opener) => opener.addEventListener('click', () => openCouponDialog(slot.name, opener)));
        slot.remove?.addEventListener('click', async () => {
            applyCoupon(slot.name);
            couponStatus.textContent = slot.name === 'shipping' ? 'Đã bỏ mã vận chuyển.' : 'Đã bỏ mã giảm giá.';
            if (hasAddress()) await loadShippingQuote();
        });

        const restorable = slot.input?.value || sessionStorage.getItem(slot.storageKey) || '';
        if (!restorable) return;
        previewCoupon(restorable, slot.name)
            .then((data) => {
                applyCoupon(slot.name, data.code, data.discount, data.headline);
                couponStatus.textContent = `Đã khôi phục mã ${data.code}.`;
                if (hasAddress()) loadShippingQuote();
            })
            .catch(() => {
                applyCoupon(slot.name);
                couponStatus.textContent = 'Mã ưu đãi trước đó không còn hợp lệ.';
            });
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

document.querySelectorAll('[data-shipment-form]').forEach((form) => {
    const status = form.querySelector('[data-shipment-status]');
    const provider = form.querySelector('[data-shipment-provider]');
    const tracking = form.querySelector('[data-tracking-input]');
    const copyTracking = tracking?.parentElement?.querySelector('[data-copy-value]');
    const helper = form.querySelector('[data-tracking-help]');
    const impact = form.querySelector('[data-shipment-impact]');
    const submit = form.querySelector('[data-shipment-submit]');
    const defaultSubmitLabel = submit?.textContent.trim() || '';
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
        delivered: 'Xác nhận giao thành công và hoàn tất đơn, kể cả khi khách chưa bấm xác nhận. Hệ thống lưu thời điểm giao, bắt đầu tính hạn đổi trả, ghi nhận COD đã thanh toán và không thể đổi lại trạng thái.',
    };

    const updateShipmentGuidance = () => {
        updateTrackingPreview();
        if (impact) impact.textContent = messages[status.value] || 'Hệ thống sẽ lưu thông tin vận chuyển và đồng bộ trạng thái đơn hàng.';
        if (submit) submit.textContent = status.value === 'delivered' ? 'Xác nhận đã giao và hoàn tất đơn' : defaultSubmitLabel;
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

const catalogHero = document.querySelector('[data-catalog-hero]');
if (catalogHero) {
    const catalogMotion = gsap.matchMedia();
    const categories = document.querySelector('[data-catalog-categories]');
    const toolbar = document.querySelector('[data-catalog-toolbar]');
    const activeFilters = document.querySelector('[data-catalog-active-filters]');
    const resultsMeta = document.querySelector('[data-catalog-results-meta]');
    const notice = document.querySelector('[data-catalog-notice]');
    const cards = gsap.utils.toArray('[data-catalog-card]');
    const pagination = document.querySelector('[data-catalog-pagination]');
    const emptyState = document.querySelector('[data-catalog-empty]');

    catalogMotion.add(
        {
            isMobile: '(max-width: 767px)',
            reduceMotion: '(prefers-reduced-motion: reduce)',
        },
        (context) => {
            const { isMobile, reduceMotion } = context.conditions;
            const form = document.querySelector('[data-catalog-filter-form]');
            const toggle = form?.querySelector('[data-catalog-filter-toggle]');
            const fields = form?.querySelector('.catalog-filter-fields');
            let removeFilterListener = null;

            if (reduceMotion) {
                gsap.set([catalogHero, categories, toolbar, activeFilters, resultsMeta, notice, cards, pagination, emptyState].flat().filter(Boolean), { clearProps: 'all' });
            } else {
                gsap.timeline({ defaults: { ease: 'power3.out' } })
                    .fromTo('[data-catalog-hero-kicker]', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: 0.45 })
                    .fromTo('[data-catalog-hero-title]', { autoAlpha: 0, y: 28 }, { autoAlpha: 1, y: 0, duration: 0.7 }, '-=0.2')
                    .fromTo('[data-catalog-hero-copy]', { autoAlpha: 0, y: 18 }, { autoAlpha: 1, y: 0, duration: 0.55 }, '-=0.4')
                    .fromTo(categories, { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: 0.5 }, '-=0.3');

                [toolbar, activeFilters, resultsMeta, notice, pagination, emptyState].filter(Boolean).forEach((element) => {
                    gsap.fromTo(element, { autoAlpha: 0, y: 20 }, {
                        autoAlpha: 1,
                        y: 0,
                        duration: 0.55,
                        ease: 'power2.out',
                        clearProps: 'transform,opacity,visibility',
                        scrollTrigger: { trigger: element, start: 'top 90%', once: true },
                    });
                });

                if (cards.length) {
                    gsap.set(cards, { autoAlpha: 0, y: 22, willChange: 'transform,opacity' });
                    ScrollTrigger.batch(cards, {
                        start: 'top 91%',
                        once: true,
                        interval: 0.08,
                        batchMax: isMobile ? 2 : 4,
                        onEnter: (batch) => gsap.to(batch, {
                            autoAlpha: 1,
                            y: 0,
                            duration: 0.58,
                            stagger: 0.07,
                            ease: 'power3.out',
                            clearProps: 'transform,opacity,visibility,willChange',
                            overwrite: true,
                        }),
                    });
                }
            }

            if (isMobile && toggle && fields) {
                form.dataset.motionReady = 'true';
                const startsOpen = fields.dataset.open === 'true';
                const filterTimeline = gsap.timeline({
                    paused: true,
                    defaults: { duration: reduceMotion ? 0.01 : 0.28, ease: 'power2.out' },
                    onReverseComplete: () => gsap.set(fields, { display: 'none' }),
                })
                    .set(fields, { display: 'grid', transformOrigin: 'top center' })
                    .fromTo(fields, { autoAlpha: 0, y: -8, scaleY: 0.985 }, { autoAlpha: 1, y: 0, scaleY: 1 });

                if (startsOpen) filterTimeline.progress(1).pause();
                else gsap.set(fields, { display: 'none', autoAlpha: 0 });

                const onFilterToggle = () => {
                    const willOpen = toggle.getAttribute('aria-expanded') !== 'true';
                    toggle.setAttribute('aria-expanded', String(willOpen));
                    if (willOpen) {
                        fields.dataset.open = 'true';
                        gsap.set(fields, { display: 'grid' });
                        filterTimeline.play();
                    } else {
                        delete fields.dataset.open;
                        filterTimeline.reverse();
                    }
                };
                toggle.addEventListener('click', onFilterToggle);
                removeFilterListener = () => toggle.removeEventListener('click', onFilterToggle);
            }

            return () => removeFilterListener?.();
        },
    );

    let refreshCall;
    const refreshCatalog = () => {
        refreshCall?.kill();
        refreshCall = gsap.delayedCall(0.12, () => ScrollTrigger.refresh());
    };
    document.fonts?.ready.then(refreshCatalog);
    document.querySelectorAll('[data-catalog-card] img').forEach((image) => {
        if (!image.complete) image.addEventListener('load', refreshCatalog, { once: true });
    });
    window.addEventListener('load', refreshCatalog, { once: true });
    window.addEventListener('pagehide', () => catalogMotion.revert(), { once: true });
}
