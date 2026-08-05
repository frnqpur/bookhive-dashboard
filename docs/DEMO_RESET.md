# BookHive Demo Environment Reset

BookHive is intended to run as a public portfolio demo. Visitors can sign in with demo accounts or register their own public account, so the demo environment needs a safe reset workflow.

The reset system restores public demo data without running `migrate:fresh` in production.

## Manual Command

Run this from the project root:

```bash
php artisan demo:reset
```

A backward-compatible alias is also available:

```bash
php artisan bookhive:demo-reset
```

Use the new command name for documentation and cPanel setup.

## What the Reset Does

`php artisan demo:reset` safely performs these actions:

1. Deletes public registered users that are not protected and are not the private owner.
2. Deletes non-protected public-created reviews.
3. Deletes non-protected public-created books.
4. Deletes non-protected mutable settings and restores default settings.
5. Deletes custom non-core roles and permissions created during public demo use.
6. Restores the core roles:
   - Super Admin
   - Admin
   - Editor
   - Reviewer
   - Customer
7. Restores core permissions and role-permission assignments.
8. Restores protected demo accounts:
   - `admin@demo.com / password`
   - `editor@demo.com / password`
   - `reviewer@demo.com / password`
   - `customer@demo.com / password`
9. Restores protected sample books and reviews.
10. Writes a demo reset log and audit log entry.
11. Cleans unreferenced uploaded public book cover files under `storage/app/public/book-covers` only when no remaining book references them.

## What the Reset Never Does

The reset command does **not**:

- run `migrate:fresh`, `db:wipe`, or destructive schema commands;
- delete the private owner account;
- change the private owner email;
- change the private owner password if the account already exists;
- expose the private owner email in command output, public pages, demo credentials, or reset logs;
- delete protected demo accounts;
- delete core roles or core permissions;
- delete protected seeded BookHive sample records;
- delete storage files that are still referenced by remaining books.

## Scheduler

BookHive schedules the reset every six hours in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('demo:reset')->everySixHours();
}
```

## cPanel Cron

On shared hosting/cPanel, create a cron job that runs Laravel's scheduler every minute:

```bash
* * * * * php /home/USER/path-to-project/artisan schedule:run >> /dev/null 2>&1
```

Replace `/home/USER/path-to-project` with the real absolute path to the Laravel project root.

Do **not** set cron to run `migrate:fresh`. The scheduler will call `demo:reset` every six hours.

## Manual Dashboard Reset

Super Admin can manually reset from:

```text
Dashboard > Demo Reset
```

The dashboard page requires typing:

```text
RESET
```

before the reset is executed.

Manual reset is restricted to Super Admin. Admin, Editor, Reviewer, and Customer cannot access the reset page.

## Logs

Reset activity is stored in:

- `demo_reset_logs`
- `audit_logs`

The logs include action status, trigger type, summary counts, timestamps, and actor name when available. The private owner email is not shown in public reset output.

## Local Test

```bash
php artisan migrate:fresh --seed
php artisan demo:reset
php artisan schedule:run
```

Then verify:

```bash
php artisan tinker
```

```php
\App\Models\User::where('email', 'admin@demo.com')->first()->is_protected;
\App\Models\User::role('Super Admin')->where('is_demo', false)->count();
\App\Models\DemoResetLog::latest()->first();
```
