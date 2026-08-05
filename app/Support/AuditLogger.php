<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLogger
{
    /**
     * Record an audit log without interrupting the user workflow if logging fails.
     *
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     */
    public static function record(
        string $action,
        ?Model $entity = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $user = null,
        ?Request $request = null,
    ): ?AuditLog {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return null;
            }

            $request ??= request();
            $user ??= Auth::user();
            $entityType = $entity ? get_class($entity) : null;
            $entityId = $entity?->getKey();

            return AuditLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'auditable_type' => $entityType,
                'auditable_id' => $entityId,
                'description' => $description,
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }
}
