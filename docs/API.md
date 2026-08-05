# BookHive Dashboard API

BookHive Dashboard exposes a small public API for browsing published books and a JWT-protected API for account-based review workflows. The API is designed for portfolio testing and public demo use.

## Base URL

Local development:

```text
http://127.0.0.1:8000/api/client
```

Production:

```text
https://your-domain.com/api/client
```

## Response Format

All new API endpoints use this JSON shape:

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {}
}
```

Errors use the same shape:

```json
{
  "success": false,
  "message": "Validation failed.",
  "data": {
    "errors": {
      "role": ["The selected role is invalid."]
    }
  }
}
```

## Authentication

BookHive uses JWT bearer tokens for protected API routes.

1. Send `POST /api/client/login` with email and password.
2. Copy the returned `data.token` value.
3. Send protected requests with this header:

```http
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

Logout invalidates the current token where supported by the JWT driver.

## Demo Credentials

These demo accounts are public-safe. private owner credentials are private and are never shown in API docs, login pages, public pages, README demo credentials, or public examples.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@demo.com` | `password` |
| Editor | `editor@demo.com` | `password` |
| Reviewer | `reviewer@demo.com` | `password` |
| Customer | `customer@demo.com` | `password` |

## Demo Reset Notice

The public demo environment resets automatically every 6 hours. Protected default accounts, core roles, core permissions, and seeded sample data are protected from destructive public/demo actions.

## Public Endpoints

### Register

```http
POST /api/client/register
```

Allowed roles: `Admin`, `Editor`, `Reviewer`, `Customer`.

Protected owner access is rejected by backend validation.

Request:

```json
{
  "name": "Demo Reviewer",
  "email": "demo-reviewer@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "Reviewer"
}
```

Success:

```json
{
  "success": true,
  "message": "User registered successfully.",
  "data": {
    "token": "JWT_TOKEN",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 13,
      "name": "Demo Reviewer",
      "email": "demo-reviewer@example.com",
      "roles": ["Reviewer"],
      "permissions": ["dashboard.view", "books.view"]
    }
  }
}
```

### Login

```http
POST /api/client/login
```

Request:

```json
{
  "email": "reviewer@demo.com",
  "password": "password"
}
```

Success:

```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "token": "JWT_TOKEN",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 3,
      "name": "Demo Reviewer",
      "email": "reviewer@demo.com",
      "roles": ["Reviewer"],
      "permissions": ["dashboard.view", "books.view"]
    },
    "redirect_url": "/dashboard"
  }
}
```

### List Books

```http
GET /api/client/books
```

Query parameters:

| Parameter | Description |
|---|---|
| `search` | Optional search term |
| `sort_field` | One of safe book sort fields |
| `sort_order` | `asc` or `desc` |
| `per_page` | 1 to 50 |
| `page` | Page number |

Example:

```bash
curl "http://127.0.0.1:8000/api/client/books?search=clean&per_page=8" \
  -H "Accept: application/json"
```

### Book Detail

```http
GET /api/client/books/{id-or-slug}
```

Only published books are returned.

### Book Reviews

```http
GET /api/client/books/{id-or-slug}/reviews
```

Only approved reviews are returned. Pending and rejected reviews are not public.

## Protected JWT Endpoints

### Logout

```http
POST /api/client/logout
```

Headers:

```http
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

### Current User

```http
GET /api/client/me
```

### My Reviews

```http
GET /api/client/my-reviews
```

Optional filter:

```text
?status=pending
```

### Create Review

```http
POST /api/client/books/{id-or-slug}/reviews
```

Request:

```json
{
  "rating": 5,
  "title": "Great read",
  "body": "Useful and easy to follow."
}
```

Notes:

- The target book must be published.
- New reviews always start as `pending`.
- Average rating and total reviews update only after approval.
- Approval/rejection is handled in the dashboard by authorized moderators.

### Update Own Review

```http
PATCH /api/client/reviews/{review}
```

Request:

```json
{
  "rating": 4,
  "title": "Updated title",
  "body": "Updated review body."
}
```

Rules:

- You can only update your own review.
- The review must be `pending` or `rejected`.
- Approved reviews cannot be edited through the public API.
- Protected seeded reviews cannot be modified by public/demo users.

### Delete Own Review

```http
DELETE /api/client/reviews/{review}
```

Rules:

- You can only delete your own review.
- The review must be `pending` or `rejected`.
- Protected seeded reviews cannot be deleted by public/demo users.

## Error Examples

### Missing Token

```json
{
  "success": false,
  "message": "JWT token is missing. Send an Authorization: Bearer <token> header.",
  "data": {
    "code": "token_missing"
  }
}
```

### Expired Token

```json
{
  "success": false,
  "message": "JWT token has expired. Please log in again.",
  "data": {
    "code": "token_expired"
  }
}
```

### Forbidden

```json
{
  "success": false,
  "message": "You can only update your own pending or rejected reviews through the API.",
  "data": null
}
```

### Not Found

```json
{
  "success": false,
  "message": "Book not found.",
  "data": null
}
```

## cURL Examples

Login:

```bash
curl -X POST http://127.0.0.1:8000/api/client/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"reviewer@demo.com","password":"password"}'
```

Get current user:

```bash
curl http://127.0.0.1:8000/api/client/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Create a review:

```bash
curl -X POST http://127.0.0.1:8000/api/client/books/1/reviews \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"rating":5,"title":"Great read","body":"Useful and easy to follow."}'
```

## Demo Reset Notes

The public demo environment resets automatically every 6 hours using Laravel Scheduler.

Production reset command:

```bash
php artisan demo:reset
```

cPanel should run Laravel Scheduler, not `migrate:fresh`:

```bash
* * * * * php /home/USER/path-to-project/artisan schedule:run >> /dev/null 2>&1
```

The reset keeps the private owner private and protected. private owner credentials are never included in public API docs, demo credentials, reset logs, or public pages.
