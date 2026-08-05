<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('audit-logs.view'), 403, 'You do not have permission to view audit logs.');

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $logs = AuditLog::with('user:id,name,email')
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('entity_type', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($validated['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($validated['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($validated['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'entity_type' => class_basename((string) ($log->entity_type ?: $log->auditable_type)),
                'entity_id' => $log->entity_id ?: $log->auditable_id,
                'auditable_type' => class_basename((string) ($log->entity_type ?: $log->auditable_type)),
                'auditable_id' => $log->entity_id ?: $log->auditable_id,
                'description' => $log->description,
                'user' => $log->user?->name,
                'user_email' => $log->user?->email,
                'ip_address' => $log->ip_address,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'created_at' => $log->created_at ? $log->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
            ]);

        return Inertia::render('global/AuditLogs/Index', [
            'pageTitle' => 'Audit Logs',
            'pageDescription' => 'Review login, CRUD, role, permission, moderation, settings, and demo reset activity recorded by BookHive.',
            'logs' => $logs,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'action' => $validated['action'] ?? '',
                'user_id' => $validated['user_id'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
