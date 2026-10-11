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
