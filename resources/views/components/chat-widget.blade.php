<section class="support-chat" data-chat-widget
    data-current-url="{{ route('chat.current') }}"
    data-create-url="{{ route('chat.conversations.store') }}"
    data-show-template="{{ route('chat.conversations.show', ['conversation' => '__UUID__']) }}"
    data-message-template="{{ route('chat.messages.store', ['conversation' => '__UUID__']) }}"
    data-read-template="{{ route('chat.read', ['conversation' => '__UUID__']) }}"
    aria-label="Hỗ trợ trực tuyến Pawora">
    <div class="support-chat__panel" id="pawora-support-panel" role="dialog" aria-modal="false" aria-labelledby="pawora-support-title" hidden data-chat-panel>
        <header class="support-chat__header">
            <div class="support-chat__brand">
                <span class="support-chat__mark" aria-hidden="true"><img src="{{ asset('media/pawora-p-logo.png') }}" alt=""></span>
                <span><strong id="pawora-support-title">Pawora hỗ trợ</strong><small><i aria-hidden="true"></i> Phản hồi trong ít phút</small></span>
            </div>
            <div class="support-chat__header-actions">
                <button type="button" class="support-chat__icon" aria-label="Tắt âm thanh thông báo" aria-pressed="false" data-chat-sound>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 9v6h4l5 4V5L9 9H5Z"/><path d="M17 9a4 4 0 0 1 0 6M19 6a8 8 0 0 1 0 12"/></svg>
                </button>
                <button type="button" class="support-chat__icon" aria-label="Thu nhỏ cửa sổ hỗ trợ" data-chat-close>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14"/></svg>
                </button>
            </div>
        </header>

        <div class="support-chat__connection" role="status" hidden data-chat-connection>Đang kết nối lại. Tin nhắn sẽ được gửi khi đường truyền ổn định.</div>
        <div class="support-chat__status sr-only" role="status" aria-live="polite" aria-atomic="true" data-chat-live></div>

        <div class="support-chat__start" data-chat-start>
            <div class="support-chat__welcome">
                <p class="eyebrow">Chăm sóc khách hàng</p>
                <h2>Xin chào! Pawora có thể giúp gì cho bạn?</h2>
                <p>Gửi câu hỏi về sản phẩm, đơn hàng, vận chuyển hoặc đổi trả. Đội ngũ Pawora sẽ tiếp nhận ngay.</p>
            </div>
            <form class="support-chat__start-form" data-chat-start-form novalidate>
                @guest
                    <label>Họ và tên <span aria-hidden="true">*</span><input name="name" autocomplete="name" required minlength="2" maxlength="100" value=""></label>
                    <div class="support-chat__contact-grid">
                        <label>Email<input name="email" type="email" autocomplete="email" inputmode="email" maxlength="150"></label>
                        <label>Số điện thoại<input name="phone" autocomplete="tel" inputmode="tel" maxlength="30"></label>
                    </div>
                    <p class="support-chat__hint">Nhập ít nhất email hoặc số điện thoại để Pawora có thể liên hệ lại.</p>
                @else
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">
                    <div class="support-chat__identity"><span>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><p><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></p></div>
                @endguest
                <label>Mã đơn hàng <small>(không bắt buộc)</small><input name="order_number" autocomplete="off" maxlength="60" placeholder="Ví dụ: PAW-2026-001"></label>
                <label>Bạn cần hỗ trợ gì? <span aria-hidden="true">*</span><textarea name="message" required minlength="2" maxlength="2000" rows="3" placeholder="Nhập câu hỏi của bạn..."></textarea></label>
                <div class="support-chat__errors" role="alert" hidden data-chat-errors></div>
                <button class="support-chat__primary" type="submit" data-chat-start-submit><span>Bắt đầu trò chuyện</span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 14-7-4 14-3-6-7-1Z"/><path d="m12 13 7-8"/></svg></button>
                <p class="support-chat__privacy"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 10V7a5 5 0 0 1 10 0v3M5 10h14v11H5z"/></svg> Nội dung được bảo vệ và chỉ dùng để hỗ trợ bạn.</p>
            </form>
        </div>

        <div class="support-chat__conversation" hidden data-chat-conversation>
            <div class="support-chat__timeline" aria-label="Nội dung cuộc trò chuyện" data-chat-messages>
                <div class="support-chat__empty" data-chat-empty><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5.5h16v11H9l-5 4v-15Z"/><path d="M8 10h8M8 13h5"/></svg><p>Cuộc trò chuyện của bạn sẽ xuất hiện tại đây.</p></div>
            </div>
            <form class="support-chat__composer" data-chat-message-form>
                <div class="support-chat__preview" hidden data-chat-image-preview><img alt="Xem trước hình ảnh sẽ gửi"><button type="button" aria-label="Bỏ hình ảnh" data-chat-image-remove>×</button></div>
                <div class="support-chat__composer-row">
                    <label class="support-chat__attach" aria-label="Đính kèm hình ảnh">
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-chat-image>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m12 5-6.5 6.5a4 4 0 0 0 5.7 5.7l7.1-7.1a2.8 2.8 0 0 0-4-4l-7.2 7.2a1.5 1.5 0 0 0 2.1 2.1l6.1-6.1"/></svg>
                    </label>
                    <label class="sr-only" for="pawora-chat-message">Nhập tin nhắn</label>
                    <textarea id="pawora-chat-message" name="message" rows="1" maxlength="2000" placeholder="Nhập tin nhắn..." data-chat-input></textarea>
                    <button class="support-chat__send" type="submit" aria-label="Gửi tin nhắn" data-chat-send><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12 14-7-4 14-3-6-7-1Z"/><path d="m12 13 7-8"/></svg></button>
                </div>
                <div class="support-chat__composer-meta"><span data-chat-composer-status>Sẵn sàng hỗ trợ</span><button type="button" data-chat-new>Cuộc trò chuyện mới</button></div>
            </form>
        </div>
    </div>

    <button class="support-chat__launcher" type="button" aria-label="Mở hỗ trợ trực tuyến" aria-controls="pawora-support-panel" aria-expanded="false" data-chat-open>
        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5.5h16v11H9l-5 4v-15Z"/><path d="M8 10h8M8 13h5"/></svg>
        <span class="support-chat__launcher-label">Cần hỗ trợ?</span>
        <span class="support-chat__badge" hidden data-chat-badge aria-label="0 tin nhắn chưa đọc">0</span>
    </button>
</section>
