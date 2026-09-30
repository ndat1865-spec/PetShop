export function initSepayPayment(element, browser = window) {
    const status = element.querySelector('[data-sepay-status]');
    const message = element.querySelector('[data-sepay-message]');
    const button = element.querySelector('[data-sepay-check]');
    const transfer = element.querySelector('[data-sepay-transfer]');
    let timer;
    let controller;
    let stopped = false;
    let checking = false;
    let failures = 0;

    const stop = (title, description) => {
        stopped = true;
        browser.clearTimeout(timer);
        status.textContent = title;
        message.textContent = description;
        transfer.hidden = true;
        button.hidden = true;
    };

    const check = async (manual = false) => {
        browser.clearTimeout(timer);
        if (stopped || checking || browser.document.hidden) return;
        checking = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        if (manual) message.textContent = 'Đang kiểm tra xác nhận thanh toán của bạn…';
        controller = new browser.AbortController();
        const timeout = browser.setTimeout(() => controller.abort(), 10000);

        try {
            const response = await browser.fetch(element.dataset.statusUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: controller.signal,
            });
            if ([401, 403, 404].includes(response.status)) {
                stop('Không thể kiểm tra thanh toán', 'Vui lòng mở lại đơn hàng từ tài khoản của bạn để kiểm tra thanh toán.');
                return;
            }
            if (!response.ok) throw new Error('Payment status unavailable');
            const result = await response.json();
            if (stopped) return;

            if (result.order_status === 'cancelled') {
                stop('Đơn hàng đã huỷ', result.payment_status === 'paid'
                    ? 'Pawora đã ghi nhận khoản chuyển của bạn. Vui lòng liên hệ hỗ trợ để xử lý thanh toán của đơn đã huỷ.'
                    : 'Vui lòng không chuyển tiền cho đơn đã huỷ. Bạn có thể xem chi tiết đơn hàng bên dưới.');
                return;
            }
            if (['failed', 'refund_pending', 'refunded'].includes(result.payment_status)) {
                stop('Thanh toán đã được cập nhật', 'Vui lòng xem chi tiết đơn hàng để theo dõi trạng thái thanh toán hiện tại.');
                return;
            }
            if (result.payment_status === 'paid') {
                const destination = new URL(result.redirect_url, browser.location.href);
                if (!result.redirect_url || destination.origin !== browser.location.origin) throw new Error('Invalid confirmation URL');
                stop('Đã thanh toán', 'Pawora đã nhận thanh toán. Đang chuyển đến trang đặt hàng thành công…');
                browser.location.replace(destination.href);
                return;
            }

            failures = 0;
            status.textContent = 'Đang chờ thanh toán';
            message.textContent = 'Pawora chưa nhận được xác nhận thanh toán. Trang sẽ tự cập nhật khi ngân hàng báo giao dịch thành công.';
        } catch {
            if (!stopped) {
                failures += 1;
                status.textContent = 'Đang chờ xác nhận';
                message.textContent = 'Chưa kết nối được để kiểm tra thanh toán. Pawora sẽ tự thử lại; bạn cũng có thể bấm Kiểm tra thanh toán.';
            }
        } finally {
            browser.clearTimeout(timeout);
            checking = false;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (!stopped && !browser.document.hidden) {
                timer = browser.setTimeout(() => check(), Math.min(5000 * (failures + 1), 30000));
            }
        }
    };

    const manualCheck = () => check(true);
    const visibilityChange = () => {
        browser.clearTimeout(timer);
        if (!browser.document.hidden) check();
    };
    const destroy = () => {
        stopped = true;
        browser.clearTimeout(timer);
        controller?.abort();
        button.removeEventListener('click', manualCheck);
        browser.document.removeEventListener('visibilitychange', visibilityChange);
    };

    button.hidden = false;
    button.addEventListener('click', manualCheck);
    browser.document.addEventListener('visibilitychange', visibilityChange);
    browser.addEventListener('pagehide', destroy, { once: true });
    browser.addEventListener('pageshow', (event) => {
        if (event.persisted) browser.location.reload();
    });
    check();

    return destroy;
}
