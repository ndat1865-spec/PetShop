import test from 'node:test';
import assert from 'node:assert/strict';
import { initSepayPayment } from '../../resources/js/sepay-payment.js';

const settle = () => new Promise((resolve) => setImmediate(resolve));
const response = (body, status = 200) => ({ status, ok: status === 200, json: async () => body });
const unpaid = { payment_status: 'unpaid', order_status: 'pending', redirect_url: null };
const paid = { payment_status: 'paid', order_status: 'confirmed', redirect_url: '/dat-hang/1/thanh-cong?token=test' };

function eventTarget() {
    const listeners = new Map();
    return {
        addEventListener: (event, callback) => listeners.set(event, callback),
        removeEventListener: (event) => listeners.delete(event),
        dispatch: (event) => listeners.get(event)?.({}),
    };
}

function fixture(fetchResponse = async () => response(unpaid), hidden = false) {
    const timers = new Map();
    const requests = [];
    const redirects = [];
    let timerId = 0;
    const fields = Object.fromEntries(['status', 'message', 'check', 'transfer'].map((name) => [name, {
        ...eventTarget(), textContent: '', hidden: false, disabled: false,
        setAttribute() {}, removeAttribute() {},
    }]));
    const root = {
        dataset: { statusUrl: '/thanh-toan/sepay/1/trang-thai?token=test' },
        querySelector: (selector) => fields[selector.match(/data-sepay-(\w+)/)[1]],
    };
    const browser = {
        ...eventTarget(),
        document: { ...eventTarget(), hidden },
        location: { href: 'https://pawora.test/thanh-toan/sepay/1', origin: 'https://pawora.test', replace: (url) => redirects.push(url), reload() {} },
        AbortController,
        fetch: (url, options) => { requests.push({ url, options }); return fetchResponse(); },
        setTimeout: (callback, delay) => { const id = ++timerId; timers.set(id, { callback, delay }); return id; },
        clearTimeout: (id) => timers.delete(id),
    };
    const destroy = initSepayPayment(root, browser);
    const poll = () => {
        assert.equal(timers.size, 1, 'Only one next check should be scheduled');
        const [id, timer] = timers.entries().next().value;
        timers.delete(id);
        return timer.callback();
    };
    return { fields, browser, requests, redirects, timers, poll, destroy };
}

test('waits for verified payment before navigating to the shared confirmation page', async () => {
    let result = unpaid;
    const flow = fixture(async () => response(result));
    await settle();
    assert.deepEqual(flow.redirects, []);
    assert.equal(flow.fields.transfer.hidden, false);
    assert.equal(flow.requests[0].options.cache, 'no-store');
    assert.equal(flow.requests[0].options.credentials, 'same-origin');
    result = paid;
    await flow.poll();
    assert.deepEqual(flow.redirects, ['https://pawora.test/dat-hang/1/thanh-cong?token=test']);
    assert.equal(flow.fields.transfer.hidden, true);
    assert.equal(flow.timers.size, 0);
});

test('cancelled and refunded orders stop checks and never display successful checkout', async () => {
    for (const result of [{ ...paid, order_status: 'cancelled' }, { ...unpaid, payment_status: 'refunded' }]) {
        const flow = fixture(async () => response(result));
        await settle();
        assert.deepEqual(flow.redirects, []);
        assert.equal(flow.fields.transfer.hidden, true);
        assert.equal(flow.fields.check.hidden, true);
        assert.equal(flow.timers.size, 0);
    }
});

test('a network error can recover through manual checking without duplicate timers', async () => {
    let offline = true;
    const flow = fixture(async () => {
        if (offline) throw new Error('Offline');
        return response(paid);
    });
    await settle();
    assert.match(flow.fields.message.textContent, /tự thử lại/);
    assert.equal(flow.fields.check.disabled, false);
    assert.equal(flow.timers.size, 1);
    offline = false;
    await flow.fields.check.dispatch('click');
    assert.equal(flow.redirects.length, 1);
    assert.equal(flow.timers.size, 0);
});

test('hidden pages pause checking and refresh as soon as the customer returns', async () => {
    const flow = fixture(undefined, true);
    await settle();
    assert.equal(flow.requests.length, 0);
    flow.browser.document.hidden = false;
    flow.browser.document.dispatch('visibilitychange');
    await settle();
    assert.equal(flow.requests.length, 1);
    flow.browser.document.hidden = true;
    flow.browser.document.dispatch('visibilitychange');
    assert.equal(flow.timers.size, 0);
    flow.destroy();
});

test('access errors stop checks and a foreign confirmation URL cannot redirect the customer', async () => {
    const denied = fixture(async () => response({}, 403));
    await settle();
    assert.equal(denied.timers.size, 0);
    assert.equal(denied.fields.transfer.hidden, true);
    const foreign = fixture(async () => response({ ...paid, redirect_url: 'https://other.test/' }));
    await settle();
    assert.deepEqual(foreign.redirects, []);
    assert.match(foreign.fields.message.textContent, /Chưa kết nối/);
    foreign.destroy();
});

test('manual clicks do not create concurrent requests and leaving stops the next check', async () => {
    let resolve;
    const flow = fixture(() => new Promise((done) => { resolve = done; }));
    await flow.fields.check.dispatch('click');
    assert.equal(flow.requests.length, 1);
    const signal = flow.requests[0].options.signal;
    flow.browser.dispatch('pagehide');
    assert.equal(signal.aborted, true);
    resolve(response(paid));
    await settle();
    assert.deepEqual(flow.redirects, []);
    assert.equal(flow.timers.size, 0);
});
