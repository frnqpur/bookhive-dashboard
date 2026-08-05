# About Demo Screenshot Checklist — BookHive Dashboard

Checklist ini dipakai untuk memastikan halaman publik `/about-demo` siap ditampilkan ke recruiter tanpa membuka data sensitif, tanpa mengubah landing page, dan tanpa mengubah auth/database/RBAC/API/dashboard logic.

## Target halaman

- Route final: `/about-demo`
- Akses: publik, tanpa login
- Layout: mengikuti existing `PublicLayout` BookHive
- Bahasa: bilingual Indonesia/English
- Demo account: tidak ditampilkan di halaman ini karena sudah tersedia di halaman login

## Screenshot assets wajib

| File | Section | Status | Catatan |
|---|---|---|---|
| `public/images/demo/bookhive-dashboard-overview.webp` | Dashboard overview |  | Jangan tampilkan credential private |
| `public/images/demo/bookhive-book-catalog.webp` | Book catalog |  | Gunakan data demo/sample |
| `public/images/demo/bookhive-review-moderation.webp` | Review moderation |  | Hindari data user private |
| `public/images/demo/bookhive-role-management.webp` | Role management |  | Jangan tampilkan Super Admin credential |
| `public/images/demo/bookhive-mobile-overview.webp` | Mobile overview |  | Pastikan responsive view jelas |

## Konten halaman `/about-demo`

| Section | Expected content | Status |
|---|---|---|
| Hero | Judul BookHive Dashboard, ringkasan ID/EN, CTA portfolio dan contact |  |
| Project Overview | Penjelasan singkat dashboard buku/review |  |
| My Role | Full-Stack Web Developer |  |
| Key Features | RBAC, JWT API, audit log, moderation workflow |  |
| Tech Stack | Laravel, React, Inertia, Tailwind, MySQL/MariaDB, Spatie Permission, JWT Auth, Vite |  |
| Suggested Demo Flow | Login → Dashboard → Books → Reviews → Moderation → Users/Roles → API |  |
| Screenshots | Lima gambar dari `public/images/demo/` |  |
| Demo Notes | Credential demo ada di login, bukan di halaman ini |  |
| Developer Note | Frengki Josua Purba, source/env/credential private tidak diekspos |  |
| CTA | View Portfolio Page dan Contact Developer |  |

## Security check sebelum screenshot/video

| Check | Status | Catatan |
|---|---|---|
| Tidak ada `.env` terlihat |  |  |
| Tidak ada `APP_KEY`, `JWT_SECRET`, atau token penuh terlihat |  | Blur bila memakai Postman/API tool |
| Tidak ada credential Super Admin/private |  | Demo account publik cukup melalui login |
| Tidak ada database credential atau phpMyAdmin private |  |  |
| Tidak ada file path lokal sensitif |  |  |
| Tidak ada email pribadi yang tidak perlu dipublikasikan |  |  |
| Screenshot tidak broken setelah `npm run build` |  |  |

## Manual test checklist

| Test case | Expected result | Status |
|---|---|---|
| Buka `/about-demo` tanpa login | Halaman tampil, tidak redirect ke login |  |
| Klik `View Portfolio Page` | Membuka `https://frengkipurba.com/projects/bookhive-dashboard` di tab baru |  |
| Klik `Contact Developer` | Membuka `https://frengkipurba.com` di tab baru |  |
| Cek responsive mobile | Konten tetap terbaca dan grid screenshot turun rapi |  |
| Cek console browser | Tidak ada error JavaScript fatal |  |
| Cek gambar | Semua asset `.webp` tampil jika file sudah di-upload |  |
| Cek login page | Demo account tetap tersedia di login |  |
| Cek landing page `/` | Tidak berubah dari flow existing |  |

## File upload cPanel terkait halaman ini

Upload/update file berikut jika deploy manual ke cPanel:

```text
resources/js/Pages/Public/AboutDemo.jsx
app/Http/Controllers/PublicSite/PublicPageController.php
docs/about-demo-screenshot-checklist.md
public/images/demo/bookhive-dashboard-overview.webp
public/images/demo/bookhive-book-catalog.webp
public/images/demo/bookhive-review-moderation.webp
public/images/demo/bookhive-role-management.webp
public/images/demo/bookhive-mobile-overview.webp
public/build/   # hasil npm run build
```

## File yang tidak boleh ditimpa saat upload cPanel

```text
.env
.env.example
storage/
bootstrap/cache/
vendor/
node_modules/
database/*.sqlite
*.sql
```

Jangan upload SQL dump ke public web root. Jangan menimpa file environment production.
