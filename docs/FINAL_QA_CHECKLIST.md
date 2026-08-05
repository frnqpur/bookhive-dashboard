# BookHive Dashboard - Final QA Checklist

Use this checklist before publishing the project as an online portfolio demo.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

## Auth

- [ ] Login works for all demo accounts.
- [ ] Register page is public.
- [ ] Register role selection only shows Admin, Editor, Reviewer, Customer.
- [ ] Backend rejects `Super Admin` during registration.
- [ ] Logout works.
- [ ] Profile update works for normal users.
- [ ] Protected demo accounts cannot change demo email/password.
- [ ] private owner can update own profile safely.

## Roles

- [ ] Super Admin can access all safe admin features.
- [ ] Admin can manage users/roles/permissions/books/reviews/settings except protected/Super Admin actions.
- [ ] Editor can manage books and view review activity.
- [ ] Reviewer can create/edit own pending/rejected reviews.
- [ ] Customer can view books and create simple reviews.

## Protection

- [ ] private owner is not shown on public pages.
- [ ] private owner cannot be deleted by Admin demo.
- [ ] Demo accounts cannot be deleted by Admin demo.
- [ ] Admin demo cannot assign protected owner access.
- [ ] Admin demo cannot delete protected accounts.
- [ ] Core roles cannot be deleted.
- [ ] Core permissions cannot be deleted.
- [ ] Protected sample books/reviews cannot be destroyed by public/demo users.

## Dashboard

- [ ] Statistics cards render.
- [ ] Charts render responsively.
- [ ] Latest users/books/reviews render according to role.
- [ ] Latest activities/audit logs render for Admin/Super Admin.
- [ ] Quick actions match user permissions.

## Users

- [ ] List loads.
- [ ] Create works.
- [ ] Edit works.
- [ ] Empty password on edit does not change password.
- [ ] Delete/soft delete works for non-protected users.
- [ ] Role assignment works for allowed roles.
- [ ] Protected handling returns a clear 403/error message.

## Roles & Permissions

- [ ] Role list loads.
- [ ] Role create/edit/delete works for custom roles.
- [ ] Permission assignment works.
- [ ] Core role protection works.
- [ ] Permission list/create/edit/delete works for custom permissions.
- [ ] Core permission protection works.

## Books

- [ ] List loads.
- [ ] Search/sort/pagination works.
- [ ] Create works.
- [ ] Edit works.
- [ ] Delete/soft delete works for non-protected books.
- [ ] Cover upload accepts jpg/jpeg/png/webp.
- [ ] Cover preview works.
- [ ] Detail page renders.
- [ ] Average rating and approved review count are correct.
- [ ] Responsive mobile card layout works.

## Reviews

- [ ] Customer/Reviewer can create review.
- [ ] New review status is pending.
- [ ] My Reviews page loads.
- [ ] Owner can edit own pending/rejected review.
- [ ] Admin/Super Admin can approve review.
- [ ] Admin/Super Admin can reject review with moderation note.
- [ ] Approved review appears on book detail.
- [ ] Rejected review does not appear publicly.
- [ ] Average rating updates from approved reviews only.
- [ ] Protected seeded reviews cannot be damaged by public/demo users.

## Settings

- [ ] Settings page loads for allowed users.
- [ ] Admin can update non-protected settings.
- [ ] Super Admin can update protected settings.
- [ ] Public contact/developer information renders.

## Demo Reset

- [ ] `php artisan demo:reset` works.
- [ ] Manual reset page only opens for Super Admin.
- [ ] Confirmation keyword `RESET` is required.
- [ ] Scheduler is configured every six hours.
- [ ] Reset log is created.
- [ ] Audit log is created.
- [ ] private owner email/password are preserved.

## JWT API

- [ ] `POST /api/client/register` works for allowed roles.
- [ ] API rejects Super Admin role.
- [ ] `POST /api/client/login` returns token.
- [ ] `GET /api/client/me` works with bearer token.
- [ ] `POST /api/client/logout` invalidates token.
- [ ] Public books endpoints work.
- [ ] Create review API creates pending review.
- [ ] My reviews API works.
- [ ] Error response format is consistent.

## Frontend

- [ ] No broken pages.
- [ ] No broken links.
- [ ] No route name errors.
- [ ] No import errors.
- [ ] No missing Inertia components.
- [ ] English UI everywhere.
- [ ] Footer is visible in every layout: `© 2026 Developed by Frengki Josua Purba`.

## Responsive

Test widths:

- [ ] 360px
- [ ] 390px
- [ ] 768px
- [ ] 1024px
- [ ] 1366px
- [ ] 1920px

Check:

- [ ] Mobile drawer works.
- [ ] Desktop sidebar works.
- [ ] Tables convert to readable mobile cards.
- [ ] Forms are single-column on mobile.
- [ ] Charts do not overflow.
- [ ] Action buttons wrap cleanly.
