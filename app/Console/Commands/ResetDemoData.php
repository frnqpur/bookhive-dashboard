<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\DemoResetLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Database\Seeders\AppSettingSeeder;
use Database\Seeders\BookHiveSampleDataSeeder;
use Database\Seeders\CoreRolePermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * This command is intentionally not named migrate:fresh or db:wipe. It resets only
     * public demo data and keeps the private owner account and protected core records safe.
     *
     * @var string
     */
    protected $signature = 'demo:reset
        {--trigger=scheduled : Trigger source, for example scheduled or manual}
        {--user-id= : User id that triggered a manual reset}
        {--skip-storage-cleanup : Keep uploaded public book cover files even if they are no longer referenced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset the BookHive public demo environment without deleting or exposing the real Super Admin.';

    public function handle(): int
    {
        $trigger = in_array((string) $this->option('trigger'), ['scheduled', 'manual', 'system'], true)
            ? (string) $this->option('trigger')
            : 'scheduled';
        $actor = $this->option('user-id') ? User::find($this->option('user-id')) : null;

        $log = $this->createResetLog($actor?->id, $trigger);
        $summary = [
            'deleted_reviews' => 0,
            'deleted_books' => 0,
            'deleted_users' => 0,
            'deleted_settings' => 0,
            'deleted_custom_roles' => 0,
            'deleted_custom_permissions' => 0,
            'deleted_upload_files' => 0,
            'restored_demo_accounts' => 0,
            'restored_sample_books' => 0,
        ];

        try {
            DB::transaction(function () use (&$summary) {
                $this->restoreCoreRolesPermissions();

                $realSuperAdmin = $this->ensureRealSuperAdminIsSafe();

                $summary['deleted_reviews'] = $this->deletePublicReviews();
                [$summary['deleted_books'], $candidateCoverFiles] = $this->deletePublicBooks();
                $summary['deleted_users'] = $this->deletePublicRegisteredUsers();
                $summary['deleted_settings'] = $this->deletePublicMutableSettings();
                [$summary['deleted_custom_roles'], $summary['deleted_custom_permissions']] = $this->deleteCustomRolePermissionData();

                $this->restoreCoreRolesPermissions();
                $summary['restored_demo_accounts'] = $this->restoreProtectedDemoAccounts($realSuperAdmin);
                $this->restoreDefaultSettings();
                $this->restoreSampleBooksAndReviews();

                $summary['restored_sample_books'] = Book::where('is_seeded', true)->where('is_protected', true)->count();
                $summary['candidate_cover_files'] = $candidateCoverFiles;
            });

            if (! $this->option('skip-storage-cleanup')) {
                $summary['deleted_upload_files'] = $this->deleteUnreferencedPublicCoverUploads($summary['candidate_cover_files'] ?? []);
            }

            unset($summary['candidate_cover_files']);
            $this->markResetLog($log, 'success', 'Demo reset completed successfully.', $summary);

            $description = $trigger === 'manual'
                ? 'Manual demo reset by Super Admin.'
                : 'Auto demo reset completed by scheduler.';

            AuditLogger::record(
                $trigger === 'manual' ? 'manual demo reset' : 'auto demo reset',
                $log,
                $description,
                [],
                $summary,
                $actor
            );

            $this->components->info('BookHive demo reset completed safely. Real Super Admin was preserved.');
            $this->line('Deleted public users: ' . $summary['deleted_users']);
            $this->line('Deleted public books: ' . $summary['deleted_books']);
            $this->line('Deleted public reviews: ' . $summary['deleted_reviews']);
            $this->line('Restored protected demo accounts: ' . $summary['restored_demo_accounts']);
            $this->line('Restored protected sample books: ' . $summary['restored_sample_books']);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->markResetLog($log, 'failed', $exception->getMessage(), $summary);

            AuditLogger::record(
                $trigger === 'manual' ? 'manual demo reset' : 'auto demo reset',
                $log,
                'Demo reset failed.',
                [],
                ['message' => $exception->getMessage(), 'summary' => $summary],
                $actor
            );

            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function createResetLog(?int $actorId, string $trigger): ?DemoResetLog
    {
        if (! Schema::hasTable('demo_reset_logs')) {
            return null;
        }

        return DemoResetLog::create([
            'triggered_by' => $actorId,
            'trigger_type' => $trigger,
            'started_at' => now(),
            'status' => 'running',
            'message' => 'Demo reset started.',
        ]);
    }

    /** @param array<string, mixed> $summary */
    private function markResetLog(?DemoResetLog $log, string $status, string $message, array $summary): void
    {
        if (! $log) {
            return;
        }

        $payload = [
            'finished_at' => now(),
            'status' => $status,
            'message' => $message,
        ];

        if (Schema::hasColumn('demo_reset_logs', 'summary')) {
            $payload['summary'] = $summary;
        }

        $log->update($payload);
    }

    private function restoreCoreRolesPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->callSilent('db:seed', ['--class' => CoreRolePermissionSeeder::class, '--force' => true]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensureRealSuperAdminIsSafe(): ?User
    {
        $email = env('SUPER_ADMIN_EMAIL');

        if (! $email) {
            $existing = User::role(User::ROLE_SUPER_ADMIN)->where('is_demo', false)->first();
            if ($existing) {
                $existing->forceFill([
                    'is_protected' => true,
                    'is_demo' => false,
                    'status' => 'active',
                    'protected_reason' => 'Real Super Admin account. Hidden from public demo credentials and protected from destructive actions.',
                ])->save();
                $existing->syncRoles([User::ROLE_SUPER_ADMIN]);
            }

            return $existing;
        }

        $superAdmin = User::withTrashed()->where('email', $email)->first();

        if (! $superAdmin) {
            $superAdmin = User::create([
                'name' => env('SUPER_ADMIN_NAME', 'Frengki Josua Purba'),
                'email' => $email,
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', Str::random(40))),
                'email_verified_at' => now(),
                'is_protected' => true,
                'is_demo' => false,
                'protected_reason' => 'Real Super Admin account. Hidden from public demo credentials and protected from destructive actions.',
                'created_by' => null,
                'status' => 'active',
            ]);
        } else {
            if ($superAdmin->trashed()) {
                $superAdmin->restore();
            }

            // Do not change the real owner email or password during demo reset.
            $superAdmin->forceFill([
                'name' => $superAdmin->name ?: env('SUPER_ADMIN_NAME', 'Frengki Josua Purba'),
                'is_protected' => true,
                'is_demo' => false,
                'protected_reason' => 'Real Super Admin account. Hidden from public demo credentials and protected from destructive actions.',
                'status' => 'active',
            ])->save();
        }

        $superAdmin->syncRoles([User::ROLE_SUPER_ADMIN]);

        return $superAdmin;
    }

    private function deletePublicReviews(): int
    {
        $query = BookReview::withTrashed()->where('is_protected', false);
        $count = (clone $query)->count();
        $query->forceDelete();

        return $count;
    }

    /** @return array{0:int,1:array<int,string>} */
    private function deletePublicBooks(): array
    {
        $query = Book::withTrashed()->where('is_protected', false);
        $candidateCoverFiles = (clone $query)
            ->whereNotNull('cover_image')
            ->pluck('cover_image')
            ->filter(fn (?string $path) => $this->isManagedPublicCoverPath($path))
            ->values()
            ->all();

        $count = (clone $query)->count();
        $query->forceDelete();

        return [$count, $candidateCoverFiles];
    }

    private function deletePublicRegisteredUsers(): int
    {
        $realSuperAdminEmail = env('SUPER_ADMIN_EMAIL');
        $query = User::withTrashed()->where('is_protected', false);

        if ($realSuperAdminEmail) {
            $query->where('email', '!=', $realSuperAdminEmail);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $user->syncRoles([]);
            $user->syncPermissions([]);
            $user->forceDelete();
        }

        return $users->count();
    }

    private function deletePublicMutableSettings(): int
    {
        $query = AppSetting::where('is_protected', false);
        $count = (clone $query)->count();
        $query->delete();

        return $count;
    }

    /** @return array{0:int,1:int} */
    private function deleteCustomRolePermissionData(): array
    {
        $customRoleQuery = Role::where('is_core', false);
        $customPermissionQuery = Permission::where('is_core', false);

        $customRoleCount = (clone $customRoleQuery)->count();
        $customPermissionCount = (clone $customPermissionQuery)->count();

        $customRoleQuery->delete();
        $customPermissionQuery->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [$customRoleCount, $customPermissionCount];
    }

    private function restoreProtectedDemoAccounts(?User $realSuperAdmin): int
    {
        $restored = 0;

        foreach (User::DEMO_CREDENTIALS as $credential) {
            $user = User::withTrashed()->firstOrNew(['email' => $credential['email']]);

            $user->forceFill([
                'name' => 'Demo ' . $credential['role'],
                'password' => Hash::make($credential['password']),
                'email_verified_at' => $user->email_verified_at ?: now(),
                'is_protected' => true,
                'is_demo' => true,
                'protected_reason' => 'Public demo account. Login is published, so email/password/role/status are locked.',
                'created_by' => $realSuperAdmin?->id,
                'status' => 'active',
            ])->save();

            if ($user->trashed()) {
                $user->restore();
            }

            $user->syncRoles([$credential['role']]);
            $restored++;
        }

        return $restored;
    }

    private function restoreDefaultSettings(): void
    {
        $this->callSilent('db:seed', ['--class' => AppSettingSeeder::class, '--force' => true]);
    }

    private function restoreSampleBooksAndReviews(): void
    {
        $this->callSilent('db:seed', ['--class' => BookHiveSampleDataSeeder::class, '--force' => true]);
    }

    /** @param array<int,string> $candidateCoverFiles */
    private function deleteUnreferencedPublicCoverUploads(array $candidateCoverFiles): int
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('book-covers')) {
            return 0;
        }

        $referenced = Book::query()
            ->whereNotNull('cover_image')
            ->pluck('cover_image')
            ->filter(fn (?string $path) => $this->isManagedPublicCoverPath($path))
            ->values()
            ->all();

        $referenced = array_flip($referenced);
        $files = array_unique(array_merge($candidateCoverFiles, $disk->allFiles('book-covers')));
        $deleted = 0;

        foreach ($files as $file) {
            if (! $this->isManagedPublicCoverPath($file) || isset($referenced[$file])) {
                continue;
            }

            if ($disk->delete($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function isManagedPublicCoverPath(?string $path): bool
    {
        return is_string($path)
            && $path !== ''
            && str_starts_with($path, 'book-covers/')
            && ! str_contains($path, '..');
    }
}
