@extends('layouts.admin')
@section('title','Nhật ký quản trị — Pawora Admin')
@section('admin-title','Nhật ký quản trị')
@section('admin-content')
<form class="admin-filter" method="get" action="{{ route('admin.audit-logs') }}">
    <label class="sr-only" for="audit-event">Loại thao tác</label>
    <input id="audit-event" name="event" value="{{ request('event') }}" placeholder="Ví dụ: user.role_changed">
    <label class="sr-only" for="audit-actor">Người thực hiện</label>
    <select id="audit-actor" name="actor"><option value="">Tất cả người thực hiện</option>@foreach($actors as $actor)<option value="{{ $actor->id }}" @selected((string) request('actor') === (string) $actor->id)>{{ $actor->name }}</option>@endforeach</select>
    <button class="button secondary small" type="submit">Lọc nhật ký</button>
</form>
<section class="admin-panel audit-panel" aria-labelledby="audit-title">
    <div class="panel-title"><div><p class="eyebrow">Accountability</p><h2 id="audit-title">Thao tác nhạy cảm</h2></div><span>{{ $logs->total() }} bản ghi</span></div>
    <div class="table-wrap"><table><thead><tr><th>Thời gian</th><th>Người thực hiện</th><th>Thao tác</th><th>Đối tượng</th><th>Thay đổi</th><th>IP</th></tr></thead><tbody>
    @forelse($logs as $log)<tr><td>{{ $log->created_at->timezone(config('app.timezone'))->format('H:i d/m/Y') }}</td><td><strong>{{ $log->actor?->name ?? 'Hệ thống' }}</strong><small>{{ $log->actor?->email }}</small></td><td><strong>{{ $log->description }}</strong><small>{{ $log->event }}</small></td><td>{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : 'Hệ thống' }}</td><td><details><summary>Xem dữ liệu</summary><pre>{{ json_encode(['trước' => $log->old_values, 'sau' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></td><td>{{ $log->ip_address ?: '—' }}</td></tr>
    @empty<tr><td colspan="6"><div class="admin-empty"><strong>Chưa có thao tác nào.</strong><span>Nhật ký sẽ xuất hiện khi admin thực hiện hành động nhạy cảm.</span></div></td></tr>@endforelse
    </tbody></table></div>{{ $logs->links('pagination::simple-default') }}
</section>
@endsection
