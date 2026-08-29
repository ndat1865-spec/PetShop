<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminAuditLogger
{
    public function record(
        Request $request,
        string $event,
        string $description,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
    ): AdminAuditLog {
        return AdminAuditLog::create([
            'actor_user_id' => $request->user()?->id,
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => $this->withoutSecrets($oldValues) ?: null,
            'new_values' => $this->withoutSecrets($newValues) ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
        ]);
    }

    private function withoutSecrets(array $values): array
    {
        return collect($values)->except([
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'secret',
            'api_key',
        ])->all();
    }
}
