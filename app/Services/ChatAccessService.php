<?php

namespace App\Services;

use App\Models\ChatConversation;
use Illuminate\Http\Request;

class ChatAccessService
{
    public function authorizeCustomer(Request $request, ChatConversation $conversation): void
    {
        $user = $request->user();
        if ($user && $conversation->user_id && (int) $conversation->user_id === (int) $user->id) {
            return;
        }

        abort_unless($conversation->matchesGuestToken($request->header('X-Chat-Token')), 403);
    }
}
