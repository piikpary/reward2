<?php

namespace App\Http\Controllers\v2\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAllRead(Request $request)
    {
        $user = $request->user();

        $user->notification_badge_count = 0;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'unreadCount' => 0,
            ],
        ]);
    }
}