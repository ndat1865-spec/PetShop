<?php

namespace App\Http\Controllers;

use App\Models\ChatAutoReplyRule;
use App\Services\AdminAuditLogger;
use App\Services\ChatAutoReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminChatRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.chats.rules', [
            'rules' => ChatAutoReplyRule::orderByDesc('priority')->orderBy('id')->get(),
            'editing' => request('edit') ? ChatAutoReplyRule::find(request('edit')) : null,
        ]);
    }

    public function store(Request $request, AdminAuditLogger $audit): RedirectResponse
    {
        $data = $this->validated($request);
        $rule = ChatAutoReplyRule::create([...$data, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        $audit->record($request, 'chat.rule_created', 'Tạo quy tắc trả lời tự động', $rule, [], $rule->only(['name', 'keywords', 'match_type', 'priority', 'active']));

        return redirect()->route('admin.chat-rules')->with('success', 'Đã tạo quy tắc trả lời tự động.');
    }

    public function update(Request $request, ChatAutoReplyRule $rule, AdminAuditLogger $audit): RedirectResponse
    {
        $before = $rule->only(['name', 'keywords', 'match_type', 'response', 'priority', 'delay_seconds', 'once_per_conversation', 'active', 'working_time_mode', 'start_time', 'end_time', 'weekdays', 'stop_after_match']);
        $rule->update([...$this->validated($request), 'updated_by' => $request->user()->id]);
        $audit->record($request, 'chat.rule_updated', 'Cập nhật quy tắc trả lời tự động', $rule, $before, $rule->only(array_keys($before)));

        return redirect()->route('admin.chat-rules')->with('success', 'Đã cập nhật quy tắc.');
    }

    public function destroy(Request $request, ChatAutoReplyRule $rule, AdminAuditLogger $audit): RedirectResponse
    {
        $before = $rule->only(['name', 'keywords', 'match_type', 'priority', 'active']);
        $audit->record($request, 'chat.rule_deleted', 'Xóa quy tắc trả lời tự động', $rule, $before, ['deleted' => true]);
        $rule->delete();

        return redirect()->route('admin.chat-rules')->with('success', 'Đã xóa quy tắc.');
    }

    public function test(Request $request, ChatAutoReplyService $service): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        return response()->json(['matches' => $service->preview($data['message'])]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'keywords_text' => ['required', 'string', 'max:2000'],
            'match_type' => ['required', Rule::in(ChatAutoReplyRule::MATCH_TYPES)],
            'response' => ['required', 'string', 'max:3000'],
            'priority' => ['required', 'integer', 'between:1,999'],
            'delay_seconds' => ['required', 'integer', 'between:0,30'],
            'once_per_conversation' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'working_time_mode' => ['required', Rule::in(ChatAutoReplyRule::WORKING_TIME_MODES)],
            'start_time' => ['nullable', 'date_format:H:i', 'required_unless:working_time_mode,always'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_unless:working_time_mode,always'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'stop_after_match' => ['nullable', 'boolean'],
        ]);
        $keywords = collect(preg_split('/[\r\n,]+/', $data['keywords_text']))->map(fn ($keyword) => trim($keyword))->filter()->unique()->values()->all();
        abort_if($keywords === [], 422, 'Vui lòng nhập ít nhất một từ khóa.');
        unset($data['keywords_text']);

        return [
            ...$data,
            'keywords' => $keywords,
            'once_per_conversation' => $request->boolean('once_per_conversation'),
            'active' => $request->boolean('active'),
            'stop_after_match' => $request->boolean('stop_after_match'),
            'weekdays' => $data['weekdays'] ?? null,
            'start_time' => $data['working_time_mode'] === 'always' ? null : $data['start_time'],
            'end_time' => $data['working_time_mode'] === 'always' ? null : $data['end_time'],
        ];
    }
}
