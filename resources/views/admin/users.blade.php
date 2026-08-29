@extends('layouts.admin')
@section('title','Tài khoản & phân quyền — Pawora Admin')
@section('admin-title','Tài khoản & phân quyền')
@section('admin-content')
<section class="role-overview" aria-label="Thống kê vai trò">
    @foreach(\App\Models\User::ROLE_LABELS as $role => $label)
        <article><span>{{ $label }}</span><strong>{{ number_format($roleCounts[$role] ?? 0) }}</strong><small>{{ $role === 'admin' ? 'Toàn quyền hệ thống' : ($role === 'staff' ? 'Vận hành cửa hàng' : 'Mua hàng và theo dõi đơn') }}</small></article>
    @endforeach
</section>

<form class="admin-filter account-filter" method="get" action="{{ route('admin.users') }}">
    <label class="sr-only" for="account-q">Tìm tài khoản</label>
    <input id="account-q" name="q" value="{{ request('q') }}" placeholder="Tên, email hoặc số điện thoại">
    <label class="sr-only" for="account-role">Lọc theo vai trò</label>
    <select id="account-role" name="role">
        <option value="">Tất cả vai trò</option>
        @foreach(\App\Models\User::ROLE_LABELS as $role => $label)<option value="{{ $role }}" @selected(request('role') === $role)>{{ $label }}</option>@endforeach
    </select>
    <button class="button secondary small" type="submit">Lọc tài khoản</button>
    @if(request()->hasAny(['q','role']))<a class="text-link" href="{{ route('admin.users') }}">Xóa bộ lọc</a>@endif
</form>

<section class="admin-panel account-access-panel" aria-labelledby="account-list-title">
    <div class="panel-title"><div><p class="eyebrow">Access control</p><h2 id="account-list-title">Danh sách tài khoản</h2></div><span>{{ $users->total() }} tài khoản</span></div>
    <div class="permission-note"><strong>Quy tắc phân quyền</strong><span>Admin quản lý toàn bộ và chỉnh quyền. Staff vận hành cửa hàng nhưng không thể chỉnh tài khoản. Customer chỉ sử dụng khu mua hàng.</span></div>
    <div class="table-wrap">
        <table class="account-table">
            <thead><tr><th>Tài khoản</th><th>Liên hệ</th><th>Hoạt động</th><th>Vai trò hiện tại</th><th>Trạng thái</th><th>Cập nhật quyền</th><th>Kiểm soát truy cập</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><div class="account-person"><span>@if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($user->name,0,1)) }}@endif</span><div><strong>{{ $user->name }}</strong><small>Tham gia {{ $user->created_at->format('d/m/Y') }}</small></div></div></td>
                        <td><strong>{{ $user->email }}</strong><small>{{ $user->phone ?: 'Chưa có số điện thoại' }}</small></td>
                        <td><strong>{{ $user->orders_count }} đơn</strong><small>{{ number_format($user->total_spent ?: 0,0,',','.') }}đ đã mua</small></td>
                        <td><span class="role-badge role-badge--{{ $user->role }}">{{ $user->roleLabel() }}</span></td>
                        <td><span @class(['account-status-badge','is-active'=>$user->is_active,'is-locked'=>!$user->is_active])>{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</span>@if($user->deactivated_at)<small>{{ $user->deactivated_at->format('d/m/Y H:i') }}</small>@endif</td>
                        <td>
                            @if(auth()->user()->is($user))
                                <span class="self-role-note">Tài khoản hiện tại</span>
                            @else
                                <form class="role-form" method="post" action="{{ route('admin.users.role.update', $user) }}" data-confirm-title="Xác nhận thay đổi quyền" data-confirm-message="Quyền mới có hiệu lực ngay sau khi lưu và sẽ được ghi vào nhật ký quản trị.">
                                    @csrf @method('patch')
                                    <label class="sr-only" for="role-{{ $user->id }}">Vai trò của {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role">
                                        @foreach(\App\Models\User::ROLE_LABELS as $role => $label)<option value="{{ $role }}" @selected($user->role === $role)>{{ $label }}</option>@endforeach
                                    </select>
                                    <button class="button secondary small" type="submit">Lưu quyền</button>
                                </form>
                            @endif
                        </td>
                        <td>@if(auth()->user()->is($user))<span class="self-role-note">Không thể tự khóa</span>@else<details class="account-status-control"><summary>{{ $user->is_active ? 'Khóa tài khoản' : 'Mở lại tài khoản' }}</summary><form method="post" action="{{ route('admin.users.status.update', $user) }}" data-confirm-title="{{ $user->is_active ? 'Khóa tài khoản này?' : 'Mở lại tài khoản này?' }}" data-confirm-message="Thay đổi có hiệu lực ngay và được ghi vào nhật ký quản trị.">@csrf @method('patch')<input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}"><label class="sr-only" for="status-reason-{{ $user->id }}">Lý do</label><input id="status-reason-{{ $user->id }}" name="reason" minlength="5" maxlength="255" placeholder="Lý do thay đổi" required><button @class(['button','small','danger-button'=>$user->is_active,'secondary'=>!$user->is_active]) type="submit">{{ $user->is_active ? 'Xác nhận khóa' : 'Mở tài khoản' }}</button></form></details>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="admin-empty"><strong>Không tìm thấy tài khoản.</strong><span>Hãy thử từ khóa khác hoặc xóa bộ lọc.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links('pagination::simple-default') }}
</section>
@endsection
