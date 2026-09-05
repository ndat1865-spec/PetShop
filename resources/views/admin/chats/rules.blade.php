@extends('layouts.admin')
@section('title','Trả lời tự động | Pawora Admin')
@section('admin-title','Trả lời tự động')
@section('admin-content')
@php
    $formRule = $editing;
    $weekdayLabels = [1=>'Thứ 2',2=>'Thứ 3',3=>'Thứ 4',4=>'Thứ 5',5=>'Thứ 6',6=>'Thứ 7',7=>'Chủ nhật'];
@endphp
<div class="chat-rules-page" data-chat-rules data-test-url="{{ route('admin.chat-rules.test') }}">
    <header class="chat-rules-hero">
        <div><p class="eyebrow">Tự động hóa hỗ trợ</p><h2>Phản hồi đúng câu hỏi, đúng thời điểm</h2><p>Quy tắc ưu tiên cao được kiểm tra trước. Bot chỉ phản hồi tin của khách và không tự tạo vòng lặp.</p></div>
        <a href="{{ route('admin.chats') }}"><x-admin-icon name="chat" /> Mở hộp thư</a>
    </header>

    <section class="chat-rules-stats" aria-label="Thống kê quy tắc">
        <article><span>Tổng quy tắc</span><strong>{{ $rules->count() }}</strong></article>
        <article><span>Đang bật</span><strong>{{ $rules->where('active',true)->count() }}</strong></article>
        <article><span>Lượt kích hoạt</span><strong>{{ number_format($rules->sum('trigger_count'),0,',','.') }}</strong></article>
        <article><span>Phản hồi một lần</span><strong>{{ $rules->where('once_per_conversation',true)->count() }}</strong></article>
    </section>

    <form class="chat-rule-filters" method="get" action="{{ route('admin.chat-rules') }}">
        <label>Tìm quy tắc<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, từ khóa, câu trả lời"></label>
        <label>Chủ đề<select name="category"><option value="">Tất cả chủ đề</option>@foreach(\App\Models\ChatAutoReplyRule::CATEGORIES as $key=>$label)<option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label>Trạng thái<select name="active"><option value="">Tất cả</option><option value="1" @selected(($filters['active'] ?? '') === '1')>Đang bật</option><option value="0" @selected(($filters['active'] ?? '') === '0')>Đã tắt</option></select></label>
        <button type="submit">Lọc quy tắc</button><a href="#rule-tester-title">Thử câu hỏi ↓</a>
    </form>
    <div class="chat-rules-layout">
        <section class="chat-rules-list" aria-labelledby="rules-list-title">
            <header><div><p class="eyebrow">Danh sách</p><h2 id="rules-list-title">Bộ quy tắc hiện tại</h2></div><span>{{ $rules->count() }} quy tắc</span></header>
            @forelse($rules as $rule)
                <article @class(['chat-rule-card','is-disabled'=>!$rule->active])>
                    <div class="chat-rule-card__priority"><span>Ưu tiên</span><strong>{{ $rule->priority }}</strong></div>
                    <div class="chat-rule-card__body">
                        <header><div><h3>{{ $rule->name }}</h3><small>{{ \App\Models\ChatAutoReplyRule::CATEGORIES[$rule->category] ?? 'Hỗ trợ chung' }}{{ $rule->handoff ? ' · Chuyển nhân viên' : '' }}</small><p><span class="ops-status {{ $rule->active ? 'ops-status--approved' : 'ops-status--hidden' }}">{{ $rule->active ? 'Đang bật' : 'Đã tắt' }}</span><span>{{ $rule->match_type === 'exact' ? 'Khớp chính xác' : 'Có chứa từ khóa' }}</span><span>{{ $rule->working_time_mode === 'always' ? 'Mọi lúc' : ($rule->working_time_mode === 'in_hours' ? 'Trong giờ' : 'Ngoài giờ') }}</span></p></div><strong>{{ number_format($rule->trigger_count,0,',','.') }} lượt</strong></header>
                        <div class="chat-rule-keywords">@foreach($rule->keywords as $keyword)<span>{{ $keyword }}</span>@endforeach</div>
                        <blockquote>{{ $rule->response }}</blockquote>
                        <footer><span>Trễ {{ $rule->delay_seconds }} giây · {{ $rule->once_per_conversation ? 'Một lần/hội thoại' : 'Có thể lặp' }}</span><div><form method="post" action="{{ route('admin.chat-rules.toggle',$rule) }}">@csrf @method('patch')<input type="hidden" name="active" value="{{ $rule->active ? '0' : '1' }}"><button type="submit">{{ $rule->active ? 'Tắt' : 'Bật' }}</button></form><a href="{{ route('admin.chat-rules',['edit'=>$rule->id]) }}">Chỉnh sửa</a><form method="post" action="{{ route('admin.chat-rules.destroy',$rule) }}" data-confirm-title="Xóa quy tắc?" data-confirm-message="Quy tắc {{ $rule->name }} sẽ bị xóa và không thể tự động trả lời nữa.">@csrf @method('delete')<button type="submit">Xóa</button></form></div></footer>
                    </div>
                </article>
            @empty
                <div class="chat-rules-empty"><x-admin-icon name="chat-rules" /><h3>Chưa có quy tắc</h3><p>Tạo quy tắc đầu tiên từ biểu mẫu bên cạnh.</p></div>
            @endforelse
        </section>

        <aside class="chat-rule-editor">
            <form method="post" action="{{ $formRule ? route('admin.chat-rules.update',$formRule) : route('admin.chat-rules.store') }}">
                @csrf @if($formRule)@method('patch')@endif
                <header><div><p class="eyebrow">{{ $formRule ? 'Chỉnh sửa' : 'Quy tắc mới' }}</p><h2>{{ $formRule ? $formRule->name : 'Tạo câu trả lời' }}</h2></div>@if($formRule)<a href="{{ route('admin.chat-rules') }}" aria-label="Đóng biểu mẫu chỉnh sửa">×</a>@endif</header>
                <label>Tên quy tắc <span aria-hidden="true">*</span><input name="name" required maxlength="120" value="{{ old('name',$formRule?->name) }}" placeholder="Ví dụ: Chính sách giao hàng"></label>
                <label>Chủ đề<select name="category">@foreach(\App\Models\ChatAutoReplyRule::CATEGORIES as $key=>$label)<option value="{{ $key }}" @selected(old('category',$formRule?->category ?? 'general') === $key)>{{ $label }}</option>@endforeach</select></label>
                <label>Từ khóa <span aria-hidden="true">*</span><textarea name="keywords_text" required rows="3" maxlength="2000" placeholder="phí ship, phí vận chuyển, giao hàng">{{ old('keywords_text',$formRule ? implode(', ',$formRule->keywords) : '') }}</textarea><small>Ngăn cách bằng dấu phẩy hoặc xuống dòng.</small></label>
                <div class="chat-rule-editor__grid">
                    <label>Kiểu khớp<select name="match_type"><option value="contains" @selected(old('match_type',$formRule?->match_type ?? 'contains')==='contains')>Có chứa từ khóa</option><option value="exact" @selected(old('match_type',$formRule?->match_type)==='exact')>Khớp chính xác</option></select></label>
                    <label>Mức ưu tiên<input name="priority" type="number" min="1" max="999" required value="{{ old('priority',$formRule?->priority ?? 50) }}"></label>
                </div>
                <label>Nội dung trả lời <span aria-hidden="true">*</span><textarea name="response" required rows="5" maxlength="3000" placeholder="Nội dung Pawora sẽ tự động gửi cho khách...">{{ old('response',$formRule?->response) }}</textarea></label>
                <div class="chat-rule-editor__grid">
                    <label>Thời gian hoạt động<select name="working_time_mode" data-rule-schedule><option value="always" @selected(old('working_time_mode',$formRule?->working_time_mode ?? 'always')==='always')>Mọi lúc</option><option value="in_hours" @selected(old('working_time_mode',$formRule?->working_time_mode)==='in_hours')>Trong khung giờ</option><option value="outside_hours" @selected(old('working_time_mode',$formRule?->working_time_mode)==='outside_hours')>Ngoài khung giờ</option></select></label>
                    <label>Độ trễ (giây)<input name="delay_seconds" type="number" min="0" max="30" required value="{{ old('delay_seconds',$formRule?->delay_seconds ?? 1) }}"></label>
                </div>
                <div class="chat-rule-schedule" data-rule-schedule-fields @if(old('working_time_mode',$formRule?->working_time_mode ?? 'always')==='always') hidden @endif>
                    <div><label>Từ<input type="time" name="start_time" value="{{ old('start_time',$formRule?->start_time ? substr($formRule->start_time,0,5) : '08:00') }}"></label><label>Đến<input type="time" name="end_time" value="{{ old('end_time',$formRule?->end_time ? substr($formRule->end_time,0,5) : '18:00') }}"></label></div>
                    <fieldset><legend>Ngày áp dụng</legend><div>@foreach($weekdayLabels as $day=>$label)<label><input type="checkbox" name="weekdays[]" value="{{ $day }}" @checked(in_array($day,old('weekdays',$formRule?->weekdays ?? [1,2,3,4,5,6,7])))>{{ $label }}</label>@endforeach</div></fieldset>
                </div>
                <div class="chat-rule-switches">
                    <label><input type="hidden" name="handoff" value="0"><input type="checkbox" name="handoff" value="1" @checked(old('handoff',$formRule?->handoff ?? false))><span><strong>Chuyển nhân viên khi khớp</strong><small>Dành cho xác minh thanh toán, sự cố và khiếu nại</small></span></label>
                    <label><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active',$formRule?->active ?? true))><span><strong>Bật quy tắc</strong><small>Sẵn sàng kiểm tra tin nhắn mới</small></span></label>
                    <label><input type="hidden" name="once_per_conversation" value="0"><input type="checkbox" name="once_per_conversation" value="1" @checked(old('once_per_conversation',$formRule?->once_per_conversation ?? true))><span><strong>Một lần mỗi hội thoại</strong><small>Tránh lặp câu trả lời</small></span></label>
                    <label hidden><input type="hidden" name="stop_after_match" value="0"><input type="checkbox" name="stop_after_match" value="1" @checked(old('stop_after_match',$formRule?->stop_after_match ?? true))><span><strong>Dừng sau khi khớp</strong><small>Không chạy các quy tắc thấp hơn</small></span></label>
                </div>
                <button class="chat-rule-save" type="submit">{{ $formRule ? 'Lưu thay đổi' : 'Tạo quy tắc' }}</button>
            </form>

            <section class="chat-rule-tester" aria-labelledby="rule-tester-title">
                <p class="eyebrow">Thử nghiệm an toàn</p><h2 id="rule-tester-title">Câu hỏi sẽ khớp gì?</h2><p>Không gửi tin thật hoặc tăng thống kê. Mỗi câu chỉ chọn một kết quả; hội thoại thật còn áp dụng giới hạn lặp 60 giây và chế độ nhân viên.</p>
                <form data-rule-test-form><label class="sr-only" for="rule-test-message">Nhập câu hỏi thử</label><textarea id="rule-test-message" name="message" rows="3" maxlength="2000" required placeholder="Ví dụ: Pawora có miễn phí ship không?"></textarea><button type="submit">Thử quy tắc</button></form>
                <div class="chat-rule-test-result" role="status" aria-live="polite" data-rule-test-result>Chưa có nội dung thử nghiệm.</div>
            </section>
        </aside>
    </div>
</div>
@endsection
