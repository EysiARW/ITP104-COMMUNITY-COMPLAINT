<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

/** Evidence files live on the private disk; they are only served to the owner or an admin. */
class AttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        $user = auth()->user();
        $owns = (int) ($attachment->complaint?->complainant_id) === (int) $user->user_id;

        abort_unless($user->isAdmin() || $owns, 403);

        $path = $attachment->file_path;
        $disk = null;

        if (Storage::disk('local')->exists($path)) {
            $disk = Storage::disk('local');
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = Storage::disk('public');
        } elseif (Storage::exists($path)) {
            $disk = Storage::disk(config('filesystems.default', 'local'));
        }

        if ($disk) {
            return $disk->response($path, $attachment->file_name, ['Content-Disposition' => 'inline']);
        }

        $directPaths = [
            storage_path('app/' . $path),
            storage_path('app/private/' . $path),
            storage_path('app/public/' . $path),
        ];

        foreach ($directPaths as $dp) {
            if (file_exists($dp) && is_file($dp)) {
                return response()->file($dp, ['Content-Disposition' => 'inline']);
            }
        }

        abort(404, 'Attachment file not found on server.');
    }
}
