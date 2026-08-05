# BookHive Dashboard

**BookHive Dashboard** is a production-like portfolio project for book review and library management. It is built with Laravel, React/Inertia, Tailwind CSS, MySQL/MariaDB, Spatie Laravel Permission, and JWT API authentication.

The project is designed as a public demo application for recruiters and hiring teams. Demo data is protected and resets automatically every six hours.

> © 2026 Developed by Frengki Josua Purba

## Features

- Modern responsive dashboard UI
- Role-based access control with Spatie Laravel Permission
- Public login and registration
- Public registration role selection: Admin, Editor, Reviewer, Customer
- Private owner account from `.env`
- Protected public demo accounts
- Book catalog management
- Cover image upload and preview
- Book detail pages with ratings and approved reviews
- Review workflow: pending, approved, rejected
- Review moderation with notes
- Average rating calculated from approved reviews only
- User, role, and permission management
- Protected core roles and permissions
- Settings management
- Audit logs
- Demo reset system every six hours
- Manual demo reset for the private owner only
- JWT API for public/protected integrations
- Public API documentation page and Markdown docs
- cPanel/shared-hosting deployment guide

## Tech Stack

- **Backend:** Laravel 10, PHP 8.1+
- **Frontend:** React, Inertia.js, Tailwind CSS, Vite
- **Database:** MySQL/MariaDB
- **Auth:** Laravel Breeze session auth + JWT API auth
- **RBAC:** Spatie Laravel Permission
- **API:** JSON responses with JWT bearer tokens
- **Hosting target:** shared hosting/cPanel

## Roles

### Private owner role
Private owner account. Full access. Credentials are loaded from `.env` and must never be shown publicly.

### Admin
Can manage users, roles, permissions, books, reviews, non-critical settings, audit logs, and API docs. Cannot assign/delete protected owner access or break protected/core records.

### Editor
Can manage books and view reviews. Cannot manage users, roles, permissions, or settings.

### Reviewer
Can view books, create reviews, and edit own pending/rejected reviews.

### Customer
Can view books, view approved reviews, create simple reviews, and update own profile.

## Public Demo Accounts

These accounts are safe to publish:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@demo.com` | `password` |
| Editor | `editor@demo.com` | `password` |
| Reviewer | `reviewer@demo.com` | `password` |
| Customer | `customer@demo.com` | `password` |

The private owner credentials are private and are not included in public pages, README demo credentials, or API docs.

## Demo Reset

The public demo environment resets automatically every six hours.

Protected by design:

- private owner is never deleted.
- private owner email/password are not modified during demo reset.
- Demo accounts are restored.
- Core roles and permissions are restored.
- Protected sample books/reviews are restored.
- Public-created demo data is cleaned safely.

Manual reset command:

```bash
php artisan demo:reset
```

Backward-compatible alias:

```bash
php artisan bookhive:demo-reset
```

Scheduler command for cPanel:

```bash
* * * * * php /home/USERNAME/bookhive-dashboard/artisan schedule:run >> /dev/null 2>&1
```

## Local Installation

```bash
git clone <your-repository-url>
cd bookhive-dashboard
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configure `.env`:

```env
APP_NAME="BookHive Dashboard"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookhive_dashboard
DB_USERNAME=root
DB_PASSWORD=

SUPER_ADMIN_NAME="Frengki Josua Purba"
SUPER_ADMIN_EMAIL="your-private-email@example.com"
SUPER_ADMIN_PASSWORD="strong-private-password"
```

Run database setup:

```bash
php artisan migrate:fresh --seed
```

Run development servers:

```bash
php artisan serve
npm run dev
```

Open:

```text
http://127.0.0.1:8000/login
```

## Production Build

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

For cPanel, see:

```text
DEPLOYMENT_CPANEL.md
```

## API Documentation

Public API docs page:

```text
/api-docs
```

Dashboard API docs page:

```text
/dashboard/api-docs
```

Markdown docs:

```text
docs/API.md
```

Postman collection:

```text
docs/bookhive_api.postman_collection.json
```

## Main API Endpoints

Public:

```text
POST /api/client/register
POST /api/client/login
GET  /api/client/books
GET  /api/client/books/{id-or-slug}
GET  /api/client/books/{id-or-slug}/reviews
```

Protected with `Authorization: Bearer <token>`:

```text
POST   /api/client/logout
GET    /api/client/me
GET    /api/client/my-reviews
POST   /api/client/books/{id-or-slug}/reviews
PATCH  /api/client/reviews/{review}
DELETE /api/client/reviews/{review}
```

## Testing

The test suite covers:

- Auth and register role selection
- Protected account behavior
- Role/permission enforcement
- Book CRUD basics
- Review workflow and average rating update
- Demo reset command
- JWT API review flow

Run:

```bash
php artisan test
```

The test environment uses SQLite in memory via `phpunit.xml`.

## Screenshots

Add screenshots after deployment:

```text
docs/screenshots/login.png
docs/screenshots/dashboard.png
docs/screenshots/books.png
docs/screenshots/book-detail.png
docs/screenshots/reviews.png
docs/screenshots/api-docs.png
```

## Timezone

BookHive uses `Asia/Jakarta` (WIB) by default. Keep `APP_TIMEZONE=Asia/Jakarta` in production so dashboard timestamps, audit logs, and demo reset times stay synchronized.

## Security Notes

- Do not commit `.env`.
- Keep `APP_DEBUG=false` in production.
- Keep the private owner email/password private.
- Public demo accounts are protected and resettable.
- Admin demo cannot assign protected owner access.
- Core roles and core permissions are protected.
- Public API does not expose private owner credentials.
- Use HTTPS in production.
- Run `php artisan demo:reset` instead of `migrate:fresh` for demo cleanup.

## Known Limitations

- Charts are lightweight SVG components, not a full BI dashboard.
- Email sending depends on production mail configuration.
- Queue workers are not required for the demo; `QUEUE_CONNECTION=sync` is acceptable for shared hosting.
- cPanel environments vary; some may require manual symlink or uploaded `vendor` folder.

## Developer

**Frengki Josua Purba**

© 2026 Developed by Frengki Josua Purba
