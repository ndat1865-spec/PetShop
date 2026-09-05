import { setupChatComposer } from './chat-composer';

const adminChat = document.querySelector('[data-admin-chat]');

if (adminChat) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const selectedUuid = adminChat.dataset.selected;
    const messagesBox = adminChat.querySelector('[data-admin-chat-messages]');
    const listBox = adminChat.querySelector('[data-admin-chat-list]');
    const replyForm = adminChat.querySelector('[data-admin-chat-reply]');
    const status = adminChat.querySelector('[data-admin-chat-status]');
    const live = adminChat.querySelector('[data-admin-chat-live]');
    const settingsForm = adminChat.querySelector('[data-chat-settings]');
    const assignForm = adminChat.querySelector('[data-chat-assign]');
    const infoToggle = adminChat.querySelector('[data-chat-info-toggle]');
    const infoPanel = adminChat.querySelector('[data-chat-info-panel]');
    const imageInput = replyForm?.querySelector('input[type="file"]');
    const imagePreview = replyForm?.querySelector('[data-admin-image-preview]');
    const composer = setupChatComposer(replyForm, `pawora_admin_draft_${adminChat.dataset.userId}_${selectedUuid}`);
    const newMessages = adminChat.querySelector('[data-admin-new-messages]');
    const acceptButton = adminChat.querySelector('[data-chat-accept]');
    let messageSignature = '';
    let listSignature = '';
    let refreshAgain = false;
    let forceBottom = true;
    let refreshing = false;
    let previewUrl = null;
    let refreshTimer = null;

    const endpoint = (template, uuid) => template.replace('__UUID__', uuid);
    const jsonHeaders = { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf };
    const formHeaders = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf };
    const say = (message) => {
        if (live) live.textContent = message;
        window.setTimeout(() => { if (live?.textContent === message) live.textContent = ''; }, 4000);
    };
    const parseError = async (response) => {
        const payload = await response.json().catch(() => ({}));
        return payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'Không thể hoàn tất yêu cầu.';
    };
    const formatRelative = (value) => {
        if (!value) return '';
        const seconds = Math.round((new Date(value).getTime() - Date.now()) / 1000);
        const abs = Math.abs(seconds);
        const formatter = new Intl.RelativeTimeFormat('vi', { numeric: 'auto' });
        if (abs < 60) return formatter.format(seconds, 'second');
        if (abs < 3600) return formatter.format(Math.round(seconds / 60), 'minute');
        if (abs < 86400) return formatter.format(Math.round(seconds / 3600), 'hour');
        return formatter.format(Math.round(seconds / 86400), 'day');
    };
    const formatTime = (value) => new Intl.DateTimeFormat('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(value));

    const renderMessages = (messages) => {
        if (!messagesBox) return;
        const signature = JSON.stringify(messages.map(({ id, read_at }) => [id, read_at]));
        if (signature === messageSignature && !forceBottom) return;
        const atBottom = forceBottom || messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 64;
        const oldTop = messagesBox.scrollTop;
        const oldLast = messagesBox.lastElementChild?.dataset.messageId;
        messageSignature = signature;
        messagesBox.replaceChildren();
        messages.forEach((message) => {
            const article = document.createElement('article');
            article.className = `chat-bubble chat-bubble--${message.sender_type}`;
            article.dataset.messageId = message.id;
            const sender = document.createElement('span');
            sender.textContent = message.sender_name;
            article.append(sender);
            if (message.attachment_url) {
                const link = document.createElement('a');
                link.href = message.attachment_url;
                link.target = '_blank';
                link.rel = 'noopener';
                const image = document.createElement('img');
                image.src = message.attachment_url;
                image.alt = message.sender_type === 'customer' ? 'Hình ảnh khách hàng gửi' : 'Hình ảnh Pawora gửi';
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
        });
        if (atBottom) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
            if (newMessages) newMessages.hidden = true;
        } else {
            messagesBox.scrollTop = oldTop;
            if (newMessages && String(messages.at(-1)?.id) !== oldLast) newMessages.hidden = false;
        }
        forceBottom = false;
    };

    const threadLink = (conversation) => {
        const href = new URL(window.location.href);
        href.searchParams.set('conversation', conversation.uuid);
        href.searchParams.delete('page');
        const link = document.createElement('a');
        link.href = href.toString();
        link.className = `chat-thread${conversation.uuid === selectedUuid ? ' is-active' : ''}${conversation.admin_unread_count > 0 ? ' is-unread' : ''}`;
        link.dataset.conversationLink = conversation.uuid;
        const avatar = document.createElement('span');
        avatar.className = 'chat-thread__avatar';
        avatar.textContent = conversation.name.slice(0, 1).toUpperCase();
        const body = document.createElement('span');
        body.className = 'chat-thread__body';
        const top = document.createElement('span');
        const name = document.createElement('strong');
        name.textContent = conversation.name;
        const time = document.createElement('time');
        time.textContent = formatRelative(conversation.last_message_at);
        top.append(name, time);
        const preview = document.createElement('small');
        preview.textContent = conversation.latest_message || 'Chưa có tin nhắn';
        const meta = document.createElement('span');
        const state = document.createElement('i');
        state.className = `ops-status ops-status--${conversation.status}`;
        state.textContent = conversation.status_label;
        meta.append(state);
        if (conversation.support_mode !== 'bot') {
            const support = document.createElement('i');
            support.className = 'chat-support-badge';
            support.textContent = conversation.support_label;
            meta.append(support);
        }
        if (conversation.assignee_name) {
            const assignee = document.createElement('em');
            assignee.textContent = conversation.assignee_name;
            meta.append(assignee);
        }
        body.append(top, preview, meta);
        link.append(avatar, body);
        if (conversation.admin_unread_count > 0) {
            const badge = document.createElement('b');
            badge.className = 'chat-thread__badge';
            badge.textContent = conversation.admin_unread_count > 99 ? '99+' : String(conversation.admin_unread_count);
            link.append(badge);
        }
        return link;
    };

    const refresh = async ({ announce = false } = {}) => {
        if (refreshing) { refreshAgain = true; return; }
        refreshing = true;
        try {
            const url = new URL(adminChat.dataset.feedUrl);
            const current = new URL(window.location.href);
            ['q', 'status', 'unread', 'assigned', 'support'].forEach((key) => {
                if (current.searchParams.has(key)) url.searchParams.set(key, current.searchParams.get(key));
            });
            if (selectedUuid) url.searchParams.set('conversation', selectedUuid);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error('refresh_failed');
            const payload = await response.json();
            Object.entries(payload.stats || {}).forEach(([key, value]) => {
                const output = adminChat.querySelector(`[data-chat-stat="${key}"]`);
                if (output) output.textContent = value;
            });
            const nextListSignature = JSON.stringify(payload.conversations);
            if (listBox && nextListSignature !== listSignature) {
                listSignature = nextListSignature;
                const listScrollTop = listBox.scrollTop;
                listBox.replaceChildren(...payload.conversations.map(threadLink));
                listBox.scrollTop = listScrollTop;
            }
            if (selectedUuid && payload.selected) {
                renderMessages(payload.messages || []);
                const selected = payload.selected;
                adminChat.querySelector('[data-chat-support-label]').textContent = `${selected.support_label} · ${selected.assignee_name || 'Chưa phân công'}`;
                adminChat.querySelector('[data-chat-handoff-reason]').textContent = selected.handoff_reason_label || '';
                if (acceptButton) acceptButton.hidden = Boolean(selected.assigned_to) || ['closed', 'spam'].includes(selected.status);
                if (settingsForm && !settingsForm.contains(document.activeElement)) {
                    settingsForm.elements.status.value = selected.status;
                    settingsForm.elements.priority.value = selected.priority;
                    settingsForm.querySelector('[type=checkbox]').checked = selected.bot_enabled;
                }
                if (assignForm && !assignForm.contains(document.activeElement)) assignForm.elements.assigned_to.value = selected.assigned_to || '';
            }
            if (announce) say('Hộp thư vừa được cập nhật.');
        } catch {
            if (status) status.textContent = 'Không tải được tin nhắn. Đang thử kết nối lại.';
        } finally {
            refreshing = false;
            if (refreshAgain) { refreshAgain = false; refresh(); }
        }
    };

    const startPolling = () => {
        window.clearInterval(refreshTimer);
        refreshTimer = window.setInterval(() => {
            if (!document.hidden && !composer?.busy) refresh();
        }, 4000);
    };

    if (window.Echo) {
        window.Echo.private('chat.admin')
            .listen('.chat.message', () => refresh({ announce: true }))
            .listen('.chat.conversation.updated', () => refresh());
    }
    startPolling();
    const fitViewport = () => {
        const height = window.innerWidth <= 760 ? window.visualViewport?.height : window.innerHeight;
        adminChat.closest('.admin-workspace').style.setProperty('--admin-chat-height', `${height || window.innerHeight}px`);
    };
    window.visualViewport?.addEventListener('resize', fitViewport);
    window.addEventListener('resize', fitViewport);
    fitViewport();

    replyForm?.querySelectorAll('[data-quick-reply]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = replyForm.querySelector('textarea');
            input.value = button.dataset.quickReply;
            composer?.save();
            input.focus();
        });
    });
    replyForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const input = replyForm.querySelector('textarea');
        if (composer?.busy || (!input.value.trim() && !imageInput.files?.length)) return;
        const data = new FormData(replyForm);
        data.set('client_message_id', composer.id());
        composer.setBusy(true);
        status.textContent = 'Đang gửi trả lời...';
        try {
            const response = await fetch(endpoint(adminChat.dataset.replyTemplate, selectedUuid), { method: 'POST', headers: formHeaders, credentials: 'same-origin', body: data });
            if (!response.ok) throw new Error(await parseError(response));
            replyForm.reset();
            composer.clear();
            forceBottom = true;
            removeAdminPreview();
            status.textContent = 'Đã gửi. Bot đã tạm dừng trong hội thoại này.';
            await refresh();
            input.focus();
        } catch (error) {
            status.textContent = `${error.message} Nội dung vẫn được giữ để gửi lại.`;
        } finally {
            composer.setBusy(false);
        }
    });

    const removeAdminPreview = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.hidden = true;
    };
    imageInput?.addEventListener('change', () => {
        const file = imageInput.files?.[0];
        if (!file) return removeAdminPreview();
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
            status.textContent = 'Chỉ nhận ảnh JPG, PNG hoặc WEBP tối đa 5MB.';
            return removeAdminPreview();
        }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        imagePreview.querySelector('img').src = previewUrl;
        imagePreview.hidden = false;
    });
    imagePreview?.querySelector('button')?.addEventListener('click', removeAdminPreview);

    const submitJsonForm = async (form, url, successMessage) => {
        const values = Object.fromEntries(new FormData(form).entries());
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(url, { method: 'PATCH', headers: jsonHeaders, credentials: 'same-origin', body: JSON.stringify(values) });
            if (!response.ok) throw new Error(await parseError(response));
            say(successMessage);
            await refresh();
        } catch (error) {
            say(error.message);
        } finally {
            button.disabled = false;
        }
    };
    settingsForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitJsonForm(settingsForm, endpoint(adminChat.dataset.updateTemplate, selectedUuid), 'Đã lưu trạng thái hội thoại.');
    });
    assignForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitJsonForm(assignForm, endpoint(adminChat.dataset.assignTemplate, selectedUuid), 'Đã cập nhật người phụ trách.');
    });
    newMessages?.addEventListener('click', () => {
        messagesBox.scrollTop = messagesBox.scrollHeight;
        newMessages.hidden = true;
    });
    messagesBox?.addEventListener('scroll', () => {
        if (messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 64) newMessages.hidden = true;
    });
    acceptButton?.addEventListener('click', async () => {
        acceptButton.disabled = true;
        try {
            const response = await fetch(endpoint(adminChat.dataset.acceptTemplate, selectedUuid), { method: 'POST', headers: formHeaders, credentials: 'same-origin' });
            if (!response.ok) throw new Error(await parseError(response));
            say('Bạn đã tiếp nhận hội thoại. Bot đã tạm dừng.');
            await refresh();
        } catch (error) { say(error.message); }
        finally { acceptButton.disabled = false; }
    });
    const closeInfo = () => {
        infoPanel?.classList.remove('is-open');
        infoToggle?.setAttribute('aria-expanded', 'false');
        infoToggle?.focus();
    };
    adminChat.querySelector('[data-chat-info-close]')?.addEventListener('click', closeInfo);
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && infoPanel?.classList.contains('is-open')) closeInfo(); });
    infoToggle?.addEventListener('click', () => {
        const open = infoPanel.classList.toggle('is-open');
        infoToggle.setAttribute('aria-expanded', String(open));
        if (open) infoPanel.querySelector('button, select')?.focus();
    });

    if (selectedUuid) {
        fetch(endpoint(adminChat.dataset.readTemplate, selectedUuid), { method: 'POST', headers: formHeaders, credentials: 'same-origin' }).catch(() => {});
        messagesBox?.scrollTo({ top: messagesBox.scrollHeight });
    }
}

const rulesPage = document.querySelector('[data-chat-rules]');
if (rulesPage) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const schedule = rulesPage.querySelector('[data-rule-schedule]');
    const scheduleFields = rulesPage.querySelector('[data-rule-schedule-fields]');
    const syncSchedule = () => {
        const enabled = schedule.value !== 'always';
        scheduleFields.hidden = !enabled;
        scheduleFields.querySelectorAll('input').forEach((input) => { input.disabled = !enabled; });
    };
    schedule?.addEventListener('change', syncSchedule);
    if (schedule && scheduleFields) syncSchedule();

    const testForm = rulesPage.querySelector('[data-rule-test-form]');
    const result = rulesPage.querySelector('[data-rule-test-result]');
    testForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = testForm.querySelector('button');
        button.disabled = true;
        result.textContent = 'Đang kiểm tra quy tắc...';
        try {
            const response = await fetch(rulesPage.dataset.testUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message: testForm.elements.message.value }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Không thể kiểm tra.');
            result.replaceChildren();
            if (!payload.matches.length) {
                result.textContent = 'Không có quy tắc nào khớp. Bot sẽ không tự tạo câu trả lời.';
            } else {
                payload.matches.forEach((match) => {
                    const item = document.createElement('article');
                    const title = document.createElement('strong');
                    title.textContent = match.name + (match.priority ? ` · ưu tiên ${match.priority}` : '') + (match.action === 'handoff' ? ' · Chuyển nhân viên' : '');
                    const copy = document.createElement('p');
                    copy.textContent = match.action === 'handoff' ? 'Hội thoại sẽ chuyển sang chờ nhân viên, bot tạm dừng. ' + match.response : match.response;
                    item.append(title, copy);
                    result.append(item);
                });
            }
        } catch (error) {
            result.textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });
}
