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
        abort_unless(Storage::exists($attachment->file_path), 404);

        return Storage::response($attachment->file_path, $attachment->file_name);
    }
}
