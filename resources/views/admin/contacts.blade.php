@extends('layouts.admin')
@section('title', 'Liên hệ Pawora Admin')
@section('admin-title', 'Hộp thư hỗ trợ')

@section('admin-content')
<div class="admin-moderation-page">
    <section class="ops-stat-grid ops-stat-grid--four" aria-label="Thống kê liên hệ">
        <article class="is-warning"><span>Yêu cầu mới</span><strong>{{ number_format((int) $stats->new_count) }}</strong><small>Chưa có người xử lý</small></article>
        <article><span>Đang xử lý</span><strong>{{ number_format((int) $stats->in_progress) }}</strong><small>Đã được tiếp nhận</small></article>
        <article class="is-success"><span>Đã giải quyết</span><strong>{{ number_format((int) $stats->resolved) }}</strong><small>Hoàn tất hỗ trợ</small></article>
        <article><span>Spam</span><strong>{{ number_format((int) $stats->spam) }}</strong><small>Nội dung không hợp lệ</small></article>
    </section>

    <form class="ops-filter ops-filter--contacts" method="get" action="{{ route('admin.contacts') }}" aria-label="Lọc yêu cầu liên hệ">
        <label><span>Tìm kiếm</span><input name="q" value="{{ request('q') }}" maxlength="100" placeholder="Tên, email, SĐT, mã đơn, nội dung"></label>
        <label><span>Trạng thái</span><select name="status"><option value="">Tất cả trạng thái</option>@foreach(\App\Models\ContactMessage::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label><span>Chủ đề</span><select name="topic"><option value="">Tất cả chủ đề</option>@foreach(\App\Models\ContactMessage::TOPIC_LABELS as $value => $label)<option value="{{ $value }}" @selected(request('topic') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label><span>Từ ngày</span><input type="date" name="from" value="{{ request('from') }}"></label>
        <label><span>Đến ngày</span><input type="date" name="to" value="{{ request('to') }}"></label>
        <div><button class="button primary small" type="submit">Áp dụng</button><a href="{{ route('admin.contacts') }}">Xóa lọc</a></div>
    </form>

    <section class="admin-panel contact-inbox" aria-labelledby="contact-inbox-title">
        <header class="panel-title"><div><p class="eyebrow">Customer care</p><h2 id="contact-inbox-title">Yêu cầu hỗ trợ</h2></div><span>{{ $messages->total() }} kết quả</span></header>
        @forelse($messages as $contact)
            <article class="contact-ticket">
                <header>
                    <div class="contact-ticket__identity"><span>{{ mb_strtoupper(mb_substr($contact->name, 0, 1)) }}</span><div><strong>{{ $contact->name }}</strong><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@if($contact->phone)<small>{{ $contact->phone }}</small>@endif</div></div>
                    <div><small>Chủ đề</small><strong>{{ $contact->topicLabel() }}</strong></div>
                    <div><small>Mã đơn</small>@if($contact->order)<a href="{{ route('admin.orders.show', $contact->order) }}">{{ $contact->order_number }}</a>@else<span>{{ $contact->order_number ?: 'Không cung cấp' }}</span>@endif</div>
                    <div><small>Tiếp nhận</small><time datetime="{{ $contact->created_at->toAtomString() }}">{{ $contact->created_at->format('H:i · d/m/Y') }}</time></div>
                    <span class="ops-status is-{{ $contact->status }}">{{ $contact->statusLabel() }}</span>
                </header>
                <details class="contact-ticket__body" @if($loop->first && request('q')) open @endif><summary>Xem nội dung và xử lý <span aria-hidden="true">⌄</span></summary><div><blockquote>{{ $contact->message }}</blockquote>
                    @if($contact->admin_note)<aside><strong>Ghi chú nội bộ</strong><p>{{ $contact->admin_note }}</p></aside>@endif
                    @if(auth()->user()->hasPermission('contacts.manage'))
                        <form method="post" action="{{ route('admin.contacts.update', $contact) }}" data-confirm-title="Cập nhật yêu cầu liên hệ?" data-confirm-message="Trạng thái, người phụ trách và ghi chú nội bộ sẽ được lưu vào nhật ký quản trị.">
                            @csrf @method('patch')
                            <label><span>Trạng thái</span><select name="status" required>@foreach(\App\Models\ContactMessage::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected($contact->status === $value)>{{ $label }}</option>@endforeach</select></label>
                            <label><span>Người phụ trách</span><select name="assigned_to"><option value="">Chưa gán</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected($contact->assigned_to === $member->id)>{{ $member->name }}</option>@endforeach</select></label>
                            <label class="is-full"><span>Ghi chú nội bộ</span><textarea name="admin_note" rows="3" maxlength="2000">{{ $contact->admin_note }}</textarea></label>
                            <button class="button secondary small" type="submit">Lưu xử lý</button>
                        </form>
                    @else
                        <p class="contact-ticket__read-only">Bạn có quyền xem nhưng không có quyền thay đổi yêu cầu này.</p>
                    @endif
                </div></details>
            </article>
        @empty
            <div class="ops-empty"><strong>Không có yêu cầu phù hợp.</strong><span>Hộp thư sẽ hiển thị các nội dung khách gửi từ trang liên hệ.</span></div>
        @endforelse
        {{ $messages->links('pagination::simple-default') }}
    </section>
</div>
@endsection
