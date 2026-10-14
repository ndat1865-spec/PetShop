<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\ShopSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        $contactOrder = null;
        if ($request->user() && $request->filled('order')) {
            $contactOrder = Order::query()
                ->where('user_id', $request->user()->id)
                ->where('number', $request->string('order')->trim()->toString())
                ->first();
        }

        return view('pages.contact', [
            'contactUser' => $request->user(),
            'contactOrder' => $contactOrder,
            'supportEmail' => ShopSetting::get('support_email', 'ndat1865@gmail.com'),
            'topics' => ContactMessage::TOPIC_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return redirect()->route('contact')->with('success', 'Pawora đã nhận yêu cầu và sẽ phản hồi sớm.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+().\s-]+$/'],
            'topic' => ['required', Rule::in(array_keys(ContactMessage::TOPIC_LABELS))],
            'order_number' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email để Pawora phản hồi.',
            'email.email' => 'Email chưa đúng định dạng.',
            'phone.regex' => 'Số điện thoại chứa ký tự chưa hợp lệ.',
            'topic.required' => 'Vui lòng chọn chủ đề cần hỗ trợ.',
            'message.required' => 'Vui lòng mô tả nội dung cần hỗ trợ.',
            'message.min' => 'Nội dung cần ít nhất 20 ký tự.',
            'message.max' => 'Nội dung không được vượt quá 2.000 ký tự.',
        ]);

        ContactMessage::create([
            ...$data,
            'user_id' => $request->user()?->id,
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'order_number' => filled($data['order_number'] ?? null) ? mb_strtoupper(trim($data['order_number'])) : null,
            'message' => trim($data['message']),
            'status' => 'new',
        ]);

        return redirect()->route('contact')->with('success', 'Pawora đã nhận yêu cầu. Đội ngũ hỗ trợ sẽ phản hồi trong vòng một ngày làm việc.');
    }
}
