<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Evidence files live on the private disk; they are only served to the owner or an admin. */
class AttachmentController extends Controller
{
    public function show(Request $request, Attachment $attachment)
    {
        $user = auth()->user();
        $owns = (int) ($attachment->complaint?->complainant_id) === (int) $user->user_id;

        abort_unless($user->isAdmin() || $owns, 403);

        $path = $attachment->file_path;
        $resolved = static::resolveFilePath($path);

        if ($resolved) {
            if (isset($resolved['disk']) && isset($resolved['relative'])) {
                try {
                    return $resolved['disk']->response(
                        $resolved['relative'],
                        $attachment->file_name,
                        ['Content-Disposition' => 'inline']
                    );
                } catch (\Throwable) {
                    // Fall back to direct file response below if response streaming fails
                }
            }

            if (isset($resolved['full_path']) && file_exists($resolved['full_path'])) {
                $mime = mime_content_type($resolved['full_path']) ?: ($attachment->file_type ?? 'application/octet-stream');
                return response()->file($resolved['full_path'], [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="' . addslashes($attachment->file_name) . '"',
                ]);
            }
        }

        // When requested as an image asset (e.g. <img> thumbnail on the complaint view)
        if ($attachment->isImage() && ($request->header('Sec-Fetch-Dest') === 'image' || str_contains($request->header('Accept', ''), 'image/'))) {
            return static::placeholderSvg($attachment->file_name);
        }

        // When viewed directly in the browser, render a user-friendly view with metadata and fix instructions
        return response()->view('attachments.missing', [
            'attachment' => $attachment,
            'complaint' => $attachment->complaint,
        ], 404);
    }

    /**
     * Resolves a stored file path across multiple disks and filesystem directories.
     */
    public static function resolveFilePath(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        $raw = str_replace('\\', '/', $path);
        $candidates = array_unique(array_filter([
            $path,
            $raw,
            ltrim($raw, '/'),
            preg_replace('#^(storage/|app/|private/|public/)#', '', ltrim($raw, '/')),
        ]));

        $diskNames = array_unique(['local', 'public', config('filesystems.default', 'local')]);

        foreach ($diskNames as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                foreach ($candidates as $c) {
                    if ($disk->exists($c)) {
                        $full = null;
                        try {
                            $full = $disk->path($c);
                        } catch (\Throwable) {
                            $full = null;
                        }

                        return [
                            'disk' => $disk,
                            'relative' => $c,
                            'full_path' => $full,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Ignore missing disk drivers
            }
        }

        $baseDirs = [
            storage_path('app/private'),
            storage_path('app'),
            storage_path('app/public'),
            public_path('storage'),
            storage_path(),
        ];

        foreach ($baseDirs as $base) {
            foreach ($candidates as $c) {
                $full = rtrim($base, '/') . '/' . ltrim($c, '/');
                if (file_exists($full) && is_file($full)) {
                    return [
                        'full_path' => $full,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Returns an inline SVG placeholder when an image file cannot be located on server storage.
     */
    public static function placeholderSvg(string $fileName)
    {
        $safeName = htmlspecialchars(mb_strimwidth($fileName, 0, 24, '...'));
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="200" viewBox="0 0 320 200">
    <rect width="320" height="200" fill="#f8fafc" stroke="#e2e8f0" rx="8"/>
    <circle cx="160" cy="70" r="28" fill="#e2e8f0"/>
    <path d="M150 78l6-16h8l6 16m-16-5h12" stroke="#64748b" stroke-width="2" stroke-linecap="round" fill="none"/>
    <text x="160" y="125" text-anchor="middle" fill="#334155" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="13" font-weight="600">File unavailable on server</text>
    <text x="160" y="145" text-anchor="middle" fill="#64748b" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="11">{$safeName}</text>
    <text x="160" y="165" text-anchor="middle" fill="#94a3b8" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="10">Mount persistent volume on Railway</text>
</svg>
SVG;
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
