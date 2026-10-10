<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Route;

class NotificationController extends Controller
{
    /** Mark one notification read, then jump to the related complaint. */
    public function open(UserNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);
        $notification->update(['is_read' => true]);

        if ($notification->complaint && ! auth()->user()->isAdmin()) {
            return redirect()->route('complaints.show', $notification->complaint);
        }
        if ($notification->complaint && Route::has('admin.complaints.show')) {
            return redirect()->route('admin.complaints.show', $notification->complaint);
        }

        return redirect()->route('dashboard');
    }

    public function readAll()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return back();
    }
}
