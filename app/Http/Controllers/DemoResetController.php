<?php

namespace App\Http\Controllers;

use App\Models\DemoResetLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class DemoResetController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the real Super Admin can access demo reset tools.');
        abort_unless($request->user()->can('demo-reset.manage'), 403, 'You do not have permission to manage demo resets.');

        $lastSuccessfulReset = DemoResetLog::with('triggeredBy:id,name')
            ->where('status', 'success')
            ->latest('finished_at')
            ->first();

        $lastReset = $lastSuccessfulReset ? [
            'id' => $lastSuccessfulReset->id,
            'trigger_type' => $lastSuccessfulReset->trigger_type,
            'status' => $lastSuccessfulReset->status,
            'message' => $lastSuccessfulReset->message,
            'triggered_by' => $lastSuccessfulReset->triggeredBy?->name,
            'started_at' => $lastSuccessfulReset->started_at ? $lastSuccessfulReset->started_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
            'finished_at' => $lastSuccessfulReset->finished_at ? $lastSuccessfulReset->finished_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
            'summary' => $lastSuccessfulReset->summary ?? [],
        ] : null;

        $nextResetAt = $lastSuccessfulReset?->finished_at?->copy()->addHours(6);

        $logs = DemoResetLog::with('triggeredBy:id,name')
            ->latest()
            ->paginate(10)
            ->through(fn (DemoResetLog $log) => [
                'id' => $log->id,
                'trigger_type' => $log->trigger_type,
                'status' => $log->status,
                'message' => $log->message,
                'summary' => $log->summary ?? [],
                'triggered_by' => $log->triggeredBy?->name,
                'started_at' => $log->started_at ? $log->started_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
                'finished_at' => $log->finished_at ? $log->finished_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
                'created_at' => $log->created_at ? $log->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB' : null,
            ]);

        return Inertia::render('global/DemoReset/Index', [
            'pageTitle' => 'Demo Environment',
            'pageDescription' => 'Safely reset public demo data while preserving the private Super Admin, protected demo accounts, core roles, core permissions, settings, and protected sample records.',
            'logs' => $logs,
            'lastReset' => $lastReset,
            'nextReset' => $nextResetAt ? [
                'at' => $nextResetAt->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB',
                'human' => $nextResetAt->isPast() ? 'Due on the next scheduler run' : $nextResetAt->diffForHumans(),
            ] : null,
            'serverTime' => now()->timezone(config('app.timezone'))->format('Y-m-d H:i') . ' WIB',
            'resetUrl' => route('dashboard.demoReset.run'),
            'requiredConfirmation' => 'RESET',
            'artisanCommand' => 'php artisan demo:reset',
            'cronCommand' => 'php /home/USER/path-to-project/artisan schedule:run >> /dev/null 2>&1',
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Only the real Super Admin can manually reset the demo environment.');
        abort_unless($request->user()->can('demo-reset.manage'), 403, 'You do not have permission to reset demo data.');

        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESET'],
        ], [
            'confirmation.in' => 'Type RESET exactly to confirm the demo environment reset.',
        ]);

        $exitCode = Artisan::call('demo:reset', [
            '--trigger' => 'manual',
            '--user-id' => $request->user()->id,
        ]);

        if ($exitCode !== 0) {
            return back()->with('error', trim(Artisan::output()) ?: 'Demo reset failed. Check demo reset logs for details.');
        }

        return back()->with('success', 'Demo environment reset completed successfully. Protected demo accounts, core roles, permissions, and the real Super Admin were preserved.');
    }
}
