<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Services\AdminAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(ContactMessage::STATUSES)],
            'topic' => ['nullable', Rule::in(array_keys(ContactMessage::TOPIC_LABELS))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $messages = ContactMessage::query()
            ->with(['user', 'assignee', 'order'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $term = $filters['q'];
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%')
                    ->orWhere('order_number', 'like', '%'.$term.'%')
                    ->orWhere('message', 'like', '%'.$term.'%'));
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['topic'] ?? null), fn ($query) => $query->where('topic', $filters['topic']))
            ->when(filled($filters['from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->orderByRaw("FIELD(status, 'new', 'in_progress', 'resolved', 'spam')")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts', [
            'messages' => $messages,
            'staff' => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->where('is_active', true)->orderBy('name')->get(),
            'stats' => ContactMessage::query()->selectRaw("SUM(status = 'new') as new_count")
                ->selectRaw("SUM(status = 'in_progress') as in_progress")
                ->selectRaw("SUM(status = 'resolved') as resolved")
                ->selectRaw("SUM(status = 'spam') as spam")
                ->first(),
        ]);
    }

    public function update(Request $request, ContactMessage $contact, AdminAuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ContactMessage::STATUSES)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->where('is_active', true))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $before = $contact->only(['status', 'assigned_to', 'admin_note', 'resolved_at']);
        $contact->update([
            ...$data,
            'assigned_to' => $data['assigned_to'] ?? null,
            'admin_note' => filled($data['admin_note'] ?? null) ? trim($data['admin_note']) : null,
            'resolved_at' => $data['status'] === 'resolved' ? ($contact->resolved_at ?? now()) : null,
        ]);
        $audit->record($request, 'contact.updated', 'Cập nhật yêu cầu liên hệ', $contact, $before, $contact->only(['status', 'assigned_to', 'admin_note', 'resolved_at']));

        return back()->with('success', 'Đã cập nhật yêu cầu liên hệ.');
    }
}
