# Barangay Banlic: Community Complaint & Incident Report System (Laravel)

Covers **Bautista** (login, layout, shared structure) and **Aquino** (complainant side).
This folder is an *overlay*: copy it on top of a fresh Laravel project.

## Setup (Laravel 11 or 12, PHP 8.2+, MySQL via XAMPP)

```bash
composer create-project laravel/laravel barangay-system
cd barangay-system
# 1. Copy everything from this folder into the project (merge/overwrite when asked)
# 2. Create an empty database in phpMyAdmin named: barangay_complaint_db
```

Edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barangay_complaint_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log        # reset-password emails are written to storage/logs/laravel.log
```

Then:

```bash
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000

- **Admin login:** `admin@barangay.local` / `Admin@12345` (change it in Account Settings)
- **Residents:** use the Register page.

### Add the background photo
Put the Cabuyao street photo at `public/images/bg.jpg`. Until then the pages use a navy gradient.
`public/images/logo.png` was cropped from the mockup; replace it with a higher-resolution seal when you have one.

## Notes
- `migrate` replaces Laravel's default `users` table with ours (`user_id`, `password_hash`), matching `barangay_complaint_system.sql`. Use the migration **or** the SQL file, not both.
- Password reset uses Laravel's built-in `password_reset_tokens` table.
- Evidence files are saved on the **private** disk and served through `/attachments/{id}` (owner or admin only).
- Complaint URLs use the reference number, e.g. `/complaints/BRGY-2026-00012`.
- New complaints start as **Pending**, priority **Medium**; admins re-classify when reviewing.

## For teammates
**Base layout:** `@extends('layouts.app')` with sections `title`, `heading`, optional `subtitle` and `actions`, and `content`. Use `@push('scripts')` for page JS.

**Reusable components**
| Component | Use |
|---|---|
| `<x-status-badge :status="$complaint->status" />` | colored status label (Balbero can restyle) |
| `<x-status-timeline :complaint="$c" />` | step tracker (Balbero can restyle) |
| `<x-file-upload name="evidence" />` | drag-and-drop uploader (Bandong reuses it) |
| `<x-icon name="list" />` | sidebar/UI icons (add paths in `components/icon.blade.php`) |
| Confirmation modal | add `data-confirm="Message"` (+ `data-confirm-title`, `data-confirm-ok`, `data-confirm-type="danger"`) to a button inside a form |

**Admin routes:** add them inside the `admin` group in `routes/web.php`. The sidebar shows a link automatically once its route name exists:
`admin.complaints.index`, `admin.investigations.index`, `admin.users.index`, `admin.areas.index`, `admin.categories.index`, `admin.reports.index`, `admin.logs.index`.
Use `admin.complaints.show` so notifications link to the right page.

**Helpers**
- `App\Support\Audit::log('UPDATE', 'description', 'table', $id)` writes to `audit_logs`.
- `UserNotification::notifyMany($userIds, 'message', $complaintId)` fills the notification bell.
- Models for every table are in `app/Models` (`Complaint` has `status`, `category`, `history`, `currentAssignment`, `investigations`, `resolution`, etc.).
