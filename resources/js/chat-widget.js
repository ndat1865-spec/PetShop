const widget = document.querySelector('[data-chat-widget]');

if (widget) {
    const panel = widget.querySelector('[data-chat-panel]');
    const launcher = widget.querySelector('[data-chat-open]');
    const closeButton = widget.querySelector('[data-chat-close]');
    const soundButton = widget.querySelector('[data-chat-sound]');
    const connection = widget.querySelector('[data-chat-connection]');
    const live = widget.querySelector('[data-chat-live]');
    const startView = widget.querySelector('[data-chat-start]');
    const conversationView = widget.querySelector('[data-chat-conversation]');
    const startForm = widget.querySelector('[data-chat-start-form]');
    const startSubmit = widget.querySelector('[data-chat-start-submit]');
    const messageForm = widget.querySelector('[data-chat-message-form]');
    const messageInput = widget.querySelector('[data-chat-input]');
    const sendButton = widget.querySelector('[data-chat-send]');
    const messagesBox = widget.querySelector('[data-chat-messages]');
    const emptyState = widget.querySelector('[data-chat-empty]');
    const errors = widget.querySelector('[data-chat-errors]');
    const badge = widget.querySelector('[data-chat-badge]');
    const composerStatus = widget.querySelector('[data-chat-composer-status]');
    const imageInput = widget.querySelector('[data-chat-image]');
    const imagePreview = widget.querySelector('[data-chat-image-preview]');
    const imageRemove = widget.querySelector('[data-chat-image-remove]');
    const newButton = widget.querySelector('[data-chat-new]');
    const storageKey = 'pawora_chat_session_v1';
    const soundKey = 'pawora_chat_sound';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let session = null;
    let lastMessageId = 0;
    let pollingTimer = null;
    let subscribedChannel = null;
    let previewUrl = null;
    let soundEnabled = localStorage.getItem(soundKey) !== 'off';

    const endpoint = (template, uuid) => template.replace('__UUID__', uuid);
    const requestHeaders = (json = true) => ({
        Accept: 'application/json',
        ...(json ? { 'Content-Type': 'application/json' } : {}),
        'X-CSRF-TOKEN': csrf,
        ...(session?.token ? { 'X-Chat-Token': session.token } : {}),
    });
    const setLive = (message) => {
        live.textContent = message;
        window.setTimeout(() => { if (live.textContent === message) live.textContent = ''; }, 4000);
    };
    const setBadge = (count) => {
        const value = Math.max(0, Number(count) || 0);
        badge.hidden = value === 0;
        badge.textContent = value > 99 ? '99+' : String(value);
        badge.setAttribute('aria-label', `${value} tin nhắn chưa đọc`);
    };
    const setOpen = (open) => {
        panel.hidden = !open;
        launcher.setAttribute('aria-expanded', String(open));
        launcher.setAttribute('aria-label', open ? 'Đóng hỗ trợ trực tuyến' : 'Mở hỗ trợ trực tuyến');
        widget.classList.toggle('is-open', open);
        if (open) {
            window.setTimeout(() => (session ? messageInput : startForm.querySelector('input:not([type="hidden"]), textarea'))?.focus(), 60);
            if (session) markRead();
        } else {
            launcher.focus();
        }
    };
    const setConversationView = (active) => {
        startView.hidden = active;
        conversationView.hidden = !active;
    };
    const parseErrors = async (response) => {
        const payload = await response.json().catch(() => ({}));
        if (payload.errors) return Object.values(payload.errors).flat().join(' ');
        return payload.message || 'Không thể hoàn tất yêu cầu. Vui lòng thử lại.';
    };
    const formatTime = (value) => new Intl.DateTimeFormat('vi-VN', { hour: '2-digit', minute: '2-digit' }).format(new Date(value));

    const renderMessage = (message, { announce = false } = {}) => {
        if (!message?.id || messagesBox.querySelector(`[data-message-id="${message.id}"]`)) return;
        if (message.deliver_at && new Date(message.deliver_at).getTime() > Date.now()) {
            window.setTimeout(() => loadMessages(), Math.max(250, new Date(message.deliver_at).getTime() - Date.now() + 100));
            return;
        }
        emptyState.hidden = true;
        const article = document.createElement('article');
        article.className = `support-chat__message support-chat__message--${message.sender_type}`;
        article.dataset.messageId = message.id;
        const sender = document.createElement('span');
        sender.textContent = message.sender_name || (message.sender_type === 'customer' ? 'Bạn' : 'Pawora');
        article.append(sender);
        if (message.attachment_url) {
            const link = document.createElement('a');
            link.href = message.attachment_url;
            link.target = '_blank';
            link.rel = 'noopener';
            const image = document.createElement('img');
            image.src = message.attachment_url;
            image.alt = message.sender_type === 'customer' ? 'Hình ảnh bạn đã gửi' : 'Hình ảnh Pawora gửi';
            link.append(image);
            article.append(link);
        }
        if (message.content) {
            const text = document.createElement('p');
            text.textContent = message.content;
            article.append(text);
        }
        const time = document.createElement('time');
        time.dateTime = message.created_at;
        time.textContent = formatTime(message.created_at);
        article.append(time);
        messagesBox.append(article);
        lastMessageId = Math.max(lastMessageId, Number(message.id));
        messagesBox.scrollTop = messagesBox.scrollHeight;
        if (announce && message.sender_type !== 'customer') {
            setLive(`Tin nhắn mới từ ${message.sender_name || 'Pawora'}`);
            if (soundEnabled) playSound();
        }
    };

    const renderPayload = (payload, announce = false) => {
        session.channelKey = payload.conversation?.channel_key || session.channelKey;
        payload.messages?.forEach((message) => renderMessage(message, { announce }));
        setBadge(payload.conversation?.customer_unread_count || 0);
        composerStatus.textContent = `${payload.conversation?.status_label || 'Đang hỗ trợ'} · ${window.Echo ? 'Realtime' : 'Tự động cập nhật'}`;
        if (!panel.hidden && payload.conversation?.customer_unread_count) markRead();
    };

    const loadMessages = async ({ announce = false } = {}) => {
        if (!session?.uuid) return;
        try {
            const url = new URL(endpoint(widget.dataset.showTemplate, session.uuid));
            if (lastMessageId) url.searchParams.set('since_id', lastMessageId);
            const response = await fetch(url, { headers: requestHeaders(false), credentials: 'same-origin' });
            if (response.status === 403 || response.status === 404) {
                clearSession();
                return;
            }
            if (!response.ok) throw new Error('load_failed');
            const payload = await response.json();
            connection.hidden = true;
            renderPayload(payload, announce);
        } catch {
            connection.hidden = false;
            composerStatus.textContent = 'Mất kết nối · đang thử lại';
        }
    };

    const startPolling = () => {
        window.clearInterval(pollingTimer);
        pollingTimer = window.setInterval(() => loadMessages({ announce: true }), 3500);
    };

    const subscribeRealtime = () => {
        if (!window.Echo || !session?.channelKey || subscribedChannel === session.channelKey) return;
        if (subscribedChannel) window.Echo.leave(`chat.conversation.${subscribedChannel}`);
        subscribedChannel = session.channelKey;
        window.Echo.channel(`chat.conversation.${session.channelKey}`)
            .listen('.chat.message', (event) => {
                if (event.conversation_uuid !== session.uuid) return;
                renderMessage(event.message, { announce: true });
                if (!panel.hidden && event.message?.sender_type !== 'customer') markRead();
            })
            .listen('.chat.conversation.updated', (event) => {
                if (event.conversation?.uuid !== session.uuid) return;
                setBadge(event.conversation.customer_unread_count || 0);
                composerStatus.textContent = `${event.conversation.status_label} · Realtime`;
            });
        const wsConnection = window.Echo.connector?.pusher?.connection;
        wsConnection?.bind('connected', () => { connection.hidden = true; composerStatus.textContent = 'Đã kết nối realtime'; });
        wsConnection?.bind('disconnected', () => { connection.hidden = false; composerStatus.textContent = 'Đang dùng cập nhật dự phòng'; });
        wsConnection?.bind('error', () => { connection.hidden = false; });
    };

    const saveSession = () => localStorage.setItem(storageKey, JSON.stringify(session));
    const clearSession = () => {
        if (subscribedChannel && window.Echo) window.Echo.leave(`chat.conversation.${subscribedChannel}`);
        subscribedChannel = null;
        session = null;
        lastMessageId = 0;
        localStorage.removeItem(storageKey);
        messagesBox.querySelectorAll('[data-message-id]').forEach((element) => element.remove());
        emptyState.hidden = false;
        setBadge(0);
        setConversationView(false);
    };

    const restoreSession = async () => {
        try { session = JSON.parse(localStorage.getItem(storageKey)); } catch { session = null; }
        if (!session?.uuid) {
            const response = await fetch(widget.dataset.currentUrl, { headers: requestHeaders(false), credentials: 'same-origin' }).catch(() => null);
            const payload = response?.ok ? await response.json() : null;
            if (payload?.conversation) {
                session = { uuid: payload.conversation.uuid, token: '', channelKey: payload.conversation.channel_key };
                saveSession();
            }
        }
        if (!session?.uuid) return;
        setConversationView(true);
        await loadMessages();
        subscribeRealtime();
        startPolling();
    };

    const markRead = async () => {
        if (!session?.uuid) return;
        setBadge(0);
        fetch(endpoint(widget.dataset.readTemplate, session.uuid), { method: 'POST', headers: requestHeaders(false), credentials: 'same-origin' }).catch(() => {});
    };

    const playSound = () => {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const context = new AudioContext();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.frequency.value = 660;
            gain.gain.setValueAtTime(0.0001, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.08, context.currentTime + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.18);
            oscillator.connect(gain).connect(context.destination);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.2);
        } catch { /* The browser can block audio until user interaction. */ }
    };

    launcher.addEventListener('click', () => setOpen(panel.hidden));
    closeButton.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !panel.hidden) setOpen(false); });
    soundButton.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        localStorage.setItem(soundKey, soundEnabled ? 'on' : 'off');
        soundButton.setAttribute('aria-pressed', String(!soundEnabled));
        soundButton.setAttribute('aria-label', soundEnabled ? 'Tắt âm thanh thông báo' : 'Bật âm thanh thông báo');
        soundButton.classList.toggle('is-muted', !soundEnabled);
    });

    startForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        errors.hidden = true;
        if (!startForm.reportValidity()) return;
        const values = Object.fromEntries(new FormData(startForm).entries());
        if (!values.email?.trim() && !values.phone?.trim()) {
            errors.textContent = 'Vui lòng nhập email hoặc số điện thoại.';
            errors.hidden = false;
            return;
        }
        startSubmit.disabled = true;
        startSubmit.setAttribute('aria-busy', 'true');
        startSubmit.querySelector('span').textContent = 'Đang kết nối...';
        try {
            const response = await fetch(widget.dataset.createUrl, {
                method: 'POST', headers: requestHeaders(), credentials: 'same-origin',
                body: JSON.stringify({ ...values, client_message_id: crypto.randomUUID() }),
            });
            if (!response.ok) throw new Error(await parseErrors(response));
            const payload = await response.json();
            session = { uuid: payload.conversation.uuid, token: payload.access_token, channelKey: payload.conversation.channel_key };
            saveSession();
            setConversationView(true);
            renderPayload(payload);
            subscribeRealtime();
            startPolling();
            setLive('Đã bắt đầu cuộc trò chuyện với Pawora.');
            messageInput.focus();
        } catch (error) {
            errors.textContent = error.message || 'Không thể kết nối. Vui lòng thử lại.';
            errors.hidden = false;
        } finally {
            startSubmit.disabled = false;
            startSubmit.removeAttribute('aria-busy');
            startSubmit.querySelector('span').textContent = 'Bắt đầu trò chuyện';
        }
    });

    messageForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!messageInput.value.trim() && !imageInput.files?.length) return;
        const data = new FormData(messageForm);
        data.set('client_message_id', crypto.randomUUID());
        sendButton.disabled = true;
        composerStatus.textContent = 'Đang gửi...';
        try {
            const response = await fetch(endpoint(widget.dataset.messageTemplate, session.uuid), {
                method: 'POST', headers: requestHeaders(false), credentials: 'same-origin', body: data,
            });
            if (!response.ok) throw new Error(await parseErrors(response));
            const payload = await response.json();
            renderMessage(payload.message);
            messageForm.reset();
            removePreview();
            messageInput.style.height = '';
            composerStatus.textContent = 'Đã gửi · đang chờ Pawora phản hồi';
            messageInput.focus();
        } catch (error) {
            composerStatus.textContent = `${error.message || 'Gửi thất bại.'} Nội dung vẫn được giữ, hãy nhấn Gửi lại.`;
            setLive('Không gửi được tin nhắn. Bạn có thể thử lại.');
        } finally {
            sendButton.disabled = false;
        }
    });

    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = `${Math.min(messageInput.scrollHeight, 104)}px`;
    });
    const removePreview = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        imageInput.value = '';
        imagePreview.hidden = true;
    };
    imageInput.addEventListener('change', () => {
        const file = imageInput.files?.[0];
        if (!file) return removePreview();
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            composerStatus.textContent = 'Chỉ nhận ảnh JPG, PNG hoặc WEBP tối đa 5MB.';
            return removePreview();
        }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        imagePreview.querySelector('img').src = previewUrl;
        imagePreview.hidden = false;
    });
    imageRemove.addEventListener('click', removePreview);
    newButton.addEventListener('click', () => {
        if (window.confirm('Bắt đầu cuộc trò chuyện mới? Lịch sử hiện tại vẫn được lưu tại Pawora.')) clearSession();
    });

    soundButton.setAttribute('aria-pressed', String(!soundEnabled));
    soundButton.classList.toggle('is-muted', !soundEnabled);
    restoreSession();
}
