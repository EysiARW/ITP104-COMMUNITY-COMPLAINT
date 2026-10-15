# Barangay Banlic CCIRS - Engineering & UX Guidelines

## 1. Timezone & Temporal Validation
- **Default Timezone:** The application operates exclusively in Philippine Standard Time (`Asia/Manila`, UTC+8). Ensure `APP_TIMEZONE=Asia/Manila` is maintained in configuration and environment variables.
- **Clock Drift Tolerance:** When validating that dates/times are not in the future (`before_or_equal`), always provide a 5-minute grace tolerance (e.g., `now()->addMinutes(5)`) to prevent false rejections caused by slight differences between client device clocks and the server clock.
- **Friendly Validation Messages:** Use clear, non-robotic error copy: *"The incident date and time cannot be in the future."*

## 2. Cloud Deployment & File Storage (Railway / Docker)
- **Persistent Volume Requirement:** Local container filesystems are ephemeral. Any uploaded user files (`storage/app/`) will be erased on redeployment unless a Railway volume is mounted to `/app/storage/app`.
- **Graceful Missing File Fallbacks:** When serving attachments, always check multiple storage disks and direct filesystem roots. If a physical file is missing from disk, do not terminate with a blank 404 error:
  - For image thumbnail requests (`<img>`), return an inline SVG placeholder.
  - For direct browser tab navigation, render a styled, branded notice explaining the situation with a direct return button.

## 3. Form Input Responsiveness & Pickers
- **Avoid Sluggish Unified Datetime Pickers:** Avoid native `<input type="datetime-local">` on iOS/macOS Safari due to wheel inertia delays.
- **Zero-Delay Split Pattern:** Prefer split controls (Date input + 12h time selector + instant AM/PM toggle + 1-tap shortcut chips) that sync automatically to a hidden ISO field (`YYYY-MM-DDTHH:mm`).

## 4. Database Schema & Migration Standards
- **Live Database vs SQL Dump:** The live application connects to MySQL via `.env`. The root `barangay_complaint_system.sql` is a reference schema and initial dump; live table adjustments must also be maintained via Laravel migrations.

## 5. Authentication Navigation & History Isolation (Back-Button & Landing Guardrails)
- **Single Exit via Sign Out:** Once authenticated, users must not be able to navigate back into the login screen (`/login`), registration (`/register`), or the public landing page (`/`) using browser Back buttons or history traversal. Explicit **Sign Out / Log Out** is the only valid way to return to guest or landing pages.
- **Public Landing Redirection:** The root URL (`/`) must check `auth()->check()` and immediately redirect active sessions to their designated role dashboard (`admin.dashboard` for Admin, `dashboard` for Residents).
- **Bfcache Invalidation & Anti-Cache Headers:** All web responses must serve strict anti-cache headers (`Cache-Control: no-cache, no-store, max-age=0, must-revalidate`, `Pragma: no-cache`). In layout JavaScript, pages restored from browser Back-Forward Cache (bfcache) must trigger a fresh reload via `window.addEventListener('pageshow', (e) => e.persisted && window.location.reload())`.
- **Main Dashboard Back Trap:** The root dashboard view should trap history popstate events so that pressing the Back button on the dashboard stays on the dashboard rather than exiting the application.

## 6. Reports & Analytics Export Standards
- **Three Dedicated Actions:** The Reports dashboard (`/admin/reports`) must provide three distinct, filter-preserving actions in the top-right header:
  1. **Export PDF:** Opens the formatted print layout in PDF mode (`mode=pdf`) configured to prompt the browser's "Save as PDF" dialog with a clean `.pdf` document title.
  2. **Print:** Opens the printable preview with print CSS and automatically triggers the print dialog (`autoprint=1`).
  3. **Export CSV:** Directly streams filtered tabular records as `.csv`.


## 5. Cloud Reverse Proxy & Mixed Content (SSL / PaaS)
- **Trust Proxies Requirement:** When deployed on PaaS environments (Railway, Docker, Render, Cloudflare), SSL terminates at the edge proxy. Always configure `$middleware->trustProxies(at: '*')` in `bootstrap/app.php` so Laravel correctly inspects `X-Forwarded-Proto` and `X-Forwarded-Host`.
- **Force HTTPS in Production:** Enforce `\Illuminate\Support\Facades\URL::forceScheme('https')` inside `AppServiceProvider::boot()` whenever `app()->environment('production')` to guarantee all `asset()` and `route()` helpers produce secure HTTPS URLs and prevent browser mixed-content blocking.

## 6. Authentication Back-Navigation & Session Security
- **Strict Anti-Cache Web Middleware:** Apply `Cache-Control: no-cache, no-store, max-age=0, must-revalidate` and `Pragma: no-cache` globally on all web responses (via `PreventBackHistory` middleware) so browser history cannot serve stale authenticated views after logout.
- **Authenticated Guest Route Redirection:** Authenticated users navigating to guest endpoints (`/`, `/login`, `/register`) must immediately redirect to their designated dashboard (`admin.dashboard` or `dashboard`).
- **bfcache Revalidation:** Include a client-side `pageshow` event handler listening for `event.persisted` to trigger an immediate `window.location.reload()` if a page is restored from browser back-forward cache.
