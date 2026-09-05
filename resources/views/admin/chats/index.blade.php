@extends('layouts.admin')
@section('title','Tin nhắn khách hàng | Pawora Admin')
@section('admin-title','Tin nhắn khách hàng')
@section('admin-content')
<div class="chat-ops" data-admin-chat
    data-feed-url="{{ route('admin.chats.data') }}"
    data-reply-template="{{ route('admin.chats.reply', ['conversation' => '__UUID__']) }}"
    data-read-template="{{ route('admin.chats.read', ['conversation' => '__UUID__']) }}"
    data-update-template="{{ route('admin.chats.update', ['conversation' => '__UUID__']) }}"
    data-assign-template="{{ route('admin.chats.assign', ['conversation' => '__UUID__']) }}"
    data-accept-template="{{ route('admin.chats.accept', ['conversation' => '__UUID__']) }}"
    data-user-id="{{ auth()->id() }}"
    data-mobile-list="{{ request()->filled('conversation') ? 'false' : 'true' }}"
    data-selected="{{ $selected?->uuid }}"
    data-can-reply="{{ auth()->user()->hasPermission('chats.reply') ? 'true' : 'false' }}">
    <section class="chat-ops__overview" aria-label="Tình trạng hộp thư">
        <div><span>Chờ nhân viên</span><strong data-chat-stat="handoff">{{ $stats['handoff'] }}</strong></div>
        <div><span>Chưa đọc</span><strong data-chat-stat="unread">{{ $stats['unread'] }}</strong></div>
        <div><span>Đang xử lý</span><strong data-chat-stat="open">{{ $stats['open'] }}</strong></div>
        <div><span>Chờ khách</span><strong data-chat-stat="waiting">{{ $stats['waiting'] }}</strong></div>
        <a href="{{ route('admin.chat-rules') }}"><x-admin-icon name="chat-rules" /><span>Quy tắc tự động</span></a>
    </section>

    <div class="chat-inbox">
        <aside class="chat-inbox__list" aria-label="Danh sách hội thoại">
            <form class="chat-inbox__filters" action="{{ route('admin.chats') }}" method="get">
                <label class="chat-inbox__search"><x-admin-icon name="search" /><span class="sr-only">Tìm hội thoại</span><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, email, SĐT, mã đơn..."></label>
                <div>
                    <label><span>Trạng thái</span><select name="status"><option value="">Tất cả</option>@foreach(\App\Models\ChatConversation::STATUS_LABELS as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '')===$value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Phân công</span><select name="assigned"><option value="">Tất cả</option><option value="mine" @selected(($filters['assigned'] ?? '')==='mine')>Của tôi</option><option value="unassigned" @selected(($filters['assigned'] ?? '')==='unassigned')>Chưa phân công</option></select></label>
                </div>
                <label class="chat-support-filter"><span>Hình thức hỗ trợ</span><select name="support"><option value="">Tất cả hỗ trợ</option>@foreach(['waiting'=>'Chờ nhân viên','human'=>'Nhân viên hỗ trợ','bot'=>'Bot hỗ trợ'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['support'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="chat-inbox__unread"><input type="checkbox" name="unread" value="1" @checked(($filters['unread'] ?? '')==='1')> Chỉ hiện chưa đọc</label>
                <button type="submit">Áp dụng bộ lọc</button>
            </form>
            <div class="chat-inbox__items" data-admin-chat-list>
                @forelse($conversations as $conversation)
                    @php($latest = $conversation->latestMessage)
                    <a @class(['chat-thread','is-active'=>$selected?->id===$conversation->id,'is-unread'=>$conversation->admin_unread_count>0])
                       href="{{ route('admin.chats', array_merge(request()->except(['page','conversation']), ['conversation'=>$conversation->uuid])) }}"
                       data-conversation-link="{{ $conversation->uuid }}">
                        <span class="chat-thread__avatar">{{ mb_strtoupper(mb_substr($conversation->guest_name,0,1)) }}</span>
                        <span class="chat-thread__body"><span><strong>{{ $conversation->guest_name }}</strong><time>{{ $conversation->last_message_at?->diffForHumans(short:true) }}</time></span><small>{{ \Illuminate\Support\Str::limit($latest?->content ?: ($latest?->message_type === 'image' ? 'Đã gửi một hình ảnh' : 'Chưa có tin nhắn'), 58) }}</small><span><i class="ops-status ops-status--{{ $conversation->status }}">{{ $conversation->statusLabel() }}</i>@if(!$conversation->bot_enabled)<i class="chat-support-badge">{{ $conversation->supportLabel() }}</i>@endif @if($conversation->assignee)<em>{{ $conversation->assignee->name }}</em>@endif</span></span>
                        @if($conversation->admin_unread_count)<b class="chat-thread__badge">{{ $conversation->admin_unread_count }}</b>@endif
                    </a>
                @empty
                    <div class="chat-inbox__empty"><x-admin-icon name="chat" /><strong>Chưa có hội thoại</strong><p>Hội thoại mới từ website sẽ xuất hiện tại đây.</p></div>
                @endforelse
            </div>
            @if($conversations->hasPages())<div class="chat-inbox__pagination">{{ $conversations->links() }}</div>@endif
        </aside>

        <section class="chat-inbox__conversation" aria-label="Nội dung hội thoại">
            @if($selected)
                <header class="chat-conversation-head">
                    <a class="chat-mobile-back" href="{{ route('admin.chats', request()->except('conversation')) }}" aria-label="Quay lại danh sách"><x-admin-icon name="back" /></a>
                    <span class="chat-thread__avatar">{{ mb_strtoupper(mb_substr($selected->guest_name,0,1)) }}</span>
                    <div><strong>{{ $selected->guest_name }}</strong><span data-chat-support-label>{{ $selected->supportLabel() }} · {{ $selected->assignee?->name ?? 'Chưa phân công' }}</span></div>
                    <button type="button" class="chat-info-toggle" aria-label="Mở thông tin khách hàng" aria-controls="chat-customer-panel" aria-expanded="false" data-chat-info-toggle><x-admin-icon name="users" /></button>
                </header>
                <div class="chat-handoff-bar"><span data-chat-handoff-reason>{{ $selected->handoffReasonLabel() }}</span>@if(auth()->user()->hasPermission('chats.reply'))<button type="button" data-chat-accept @if($selected->assigned_to || in_array($selected->status,['closed','spam'])) hidden @endif>Tiếp nhận</button>@endif</div>
                <div class="chat-conversation-messages" data-admin-chat-messages>
                    @foreach($selected->messages as $message)
                        <article class="chat-bubble chat-bubble--{{ $message->sender_type }}" data-message-id="{{ $message->id }}">
                            <span>{{ $message->sender_type === 'customer' ? $selected->guest_name : ($message->sender_type === 'bot' ? 'Pawora tự động' : ($message->sender?->name ?? 'Hệ thống')) }}</span>
                            @if($message->message_type === 'image' && $message->attachment_path)
                                <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('chat.attachments.show', now()->addMinutes(30), ['message'=>$message]) }}" target="_blank" rel="noopener"><img src="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('chat.attachments.show', now()->addMinutes(30), ['message'=>$message]) }}" alt="Hình ảnh do {{ $message->sender_type === 'customer' ? 'khách hàng' : 'Pawora' }} gửi"></a>
                            @endif
                            @if($message->content)<p>{{ $message->content }}</p>@endif
                            <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->format('H:i · d/m/Y') }}</time>
                        </article>
                    @endforeach
                </div>
                <button type="button" class="chat-new-messages" data-admin-new-messages hidden>Tin nhắn mới ↓</button>
                @if(auth()->user()->hasPermission('chats.reply'))
                    <form class="chat-admin-composer" data-admin-chat-reply enctype="multipart/form-data">
                        <div class="chat-quick-replies" aria-label="Câu trả lời nhanh">@foreach($quickReplies as $reply)<button type="button" data-quick-reply="{{ $reply }}">{{ \Illuminate\Support\Str::limit($reply,38) }}</button>@endforeach</div>
                        <div class="chat-admin-preview" hidden data-admin-image-preview><img alt="Xem trước ảnh đính kèm"><button type="button" aria-label="Bỏ ảnh đính kèm">×</button></div>
                        <div class="chat-admin-composer__row">
                            <label class="chat-admin-attach" aria-label="Đính kèm hình ảnh"><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><x-admin-icon name="attachment" /></label>
                            <label class="sr-only" for="admin-chat-message">Nhập nội dung trả lời</label>
                            <textarea id="admin-chat-message" name="message" rows="1" maxlength="2000" placeholder="Nhập câu trả lời cho khách..."></textarea>
                            <button type="submit"><span>Gửi</span><x-admin-icon name="send" /></button>
                        </div>
                        <p role="status" data-admin-chat-status>Trả lời thủ công sẽ tạm dừng bot trong hội thoại này.</p>
                    </form>
                @else
                    <div class="chat-view-only">Bạn có quyền xem nhưng chưa được phép trả lời hội thoại.</div>
                @endif
            @else
                <div class="chat-conversation-empty"><x-admin-icon name="chat" /><h2>Chọn một hội thoại</h2><p>Nội dung trao đổi và thông tin khách hàng sẽ xuất hiện tại đây.</p></div>
            @endif
        </section>

        <aside class="chat-inbox__details" id="chat-customer-panel" aria-label="Thông tin khách hàng" data-chat-info-panel>
            @if($selected)
                <header><button type="button" class="chat-info-close" data-chat-info-close aria-label="Đóng thông tin khách hàng">×</button><div class="chat-profile-avatar">{{ mb_strtoupper(mb_substr($selected->guest_name,0,1)) }}</div><h2>{{ $selected->guest_name }}</h2><p>{{ $selected->user ? 'Khách hàng đã đăng nhập' : 'Khách vãng lai' }}</p></header>
                <section><h3>Thông tin liên hệ</h3><dl><div><dt>Email</dt><dd>{{ $selected->guest_email ?: 'Chưa cung cấp' }}</dd></div><div><dt>Số điện thoại</dt><dd>{{ $selected->guest_phone ?: 'Chưa cung cấp' }}</dd></div><div><dt>Mã đơn hàng</dt><dd>@if($linkedOrder)<a href="{{ route('admin.orders.show',$linkedOrder) }}">{{ $linkedOrder->number }} →</a>@else{{ $selected->order_number ?: 'Không có' }}@endif</dd></div><div><dt>Bắt đầu</dt><dd>{{ $selected->created_at?->format('H:i · d/m/Y') }}</dd></div></dl></section>
                @if(auth()->user()->hasPermission('chats.assign'))
                    <form data-chat-assign><h3>Người phụ trách</h3><label><span class="sr-only">Chọn người phụ trách</span><select name="assigned_to"><option value="">Chưa phân công</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected($selected->assigned_to===$member->id)>{{ $member->name }} · {{ $member->roleLabel() }}</option>@endforeach</select></label><button type="submit">Lưu phân công</button></form>
                @endif
                @if(auth()->user()->hasPermission('chats.manage'))
                    <form data-chat-settings><h3>Xử lý hội thoại</h3><label>Trạng thái<select name="status">@foreach(\App\Models\ChatConversation::STATUS_LABELS as $value=>$label)<option value="{{ $value }}" @selected($selected->status===$value)>{{ $label }}</option>@endforeach</select></label><label>Mức ưu tiên<select name="priority">@foreach(\App\Models\ChatConversation::PRIORITY_LABELS as $value=>$label)<option value="{{ $value }}" @selected($selected->priority===$value)>{{ $label }}</option>@endforeach</select></label><label class="chat-bot-toggle"><input type="hidden" name="bot_enabled" value="0"><input type="checkbox" name="bot_enabled" value="1" @checked($selected->bot_enabled)><span><strong>Bật lại bot</strong><small>Chủ động kết thúc chế độ nhân viên hỗ trợ</small></span></label><button type="submit">Lưu thay đổi</button></form>
                @endif
            @else
                <div class="chat-details-empty">Chưa có thông tin để hiển thị.</div>
            @endif
        </aside>
    </div>
    <div class="sr-only" role="status" aria-live="polite" data-admin-chat-live></div>
</div>
@endsection
