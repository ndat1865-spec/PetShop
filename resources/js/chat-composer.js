// Shared typing and retry behavior for customer and staff composers.
export function setupChatComposer(form, draftKey) {
    if (!form) return null;
    const input = form.querySelector('textarea');
    let pending = null;
    let composing = false;
    let busy = false;
    try {
        const saved = JSON.parse(sessionStorage.getItem(draftKey));
        if (saved) { input.value = saved.text || ''; pending = saved.pending || null; }
    } catch { /* Storage can be unavailable in a private browsing session. */ }
    const resize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 104)}px`;
    };
    const save = () => {
        try { sessionStorage.setItem(draftKey, JSON.stringify({ text: input.value, pending })); } catch { /* Keep the in-memory draft. */ }
        resize();
    };
    input.addEventListener('input', save);
    input.addEventListener('compositionstart', () => { composing = true; });
    input.addEventListener('compositionend', () => { composing = false; });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && !composing && event.keyCode !== 229) {
            event.preventDefault();
            if (!busy) form.requestSubmit();
        }
    });
    window.addEventListener('pagehide', save);
    requestAnimationFrame(resize);
    return {
        save,
        id() {
            const file = form.querySelector('input[type=file]')?.files?.[0];
            const fingerprint = JSON.stringify([input.value, file?.name, file?.size, file?.lastModified]);
            if (pending?.fingerprint !== fingerprint) pending = { fingerprint, id: crypto.randomUUID() };
            save();
            return pending.id;
        },
        clear() { pending = null; input.value = ''; save(); },
        setBusy(value) {
            busy = value;
            input.readOnly = value;
            form.querySelectorAll('button, input[type=file]').forEach((element) => { element.disabled = value; });
            form.setAttribute('aria-busy', String(value));
        },
        get busy() { return busy; },
    };
}
