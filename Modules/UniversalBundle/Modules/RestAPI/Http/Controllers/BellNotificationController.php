<?php

namespace Modules\RestAPI\Http\Controllers;

use Illuminate\Http\Request;
use Modules\RestAPI\Entities\FirebaseNotification;

class BellNotificationController extends ApiBaseController
{

    public function allNotifications(Request $request)
    {
        $user = api_user();
        $userId = (int)data_get($user, 'id');
        $perPage = (int)$request->get('per_page', 20);
        $perPage = $perPage > 0 ? min($perPage, 100) : 20;

        $notifications = FirebaseNotification::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function unreadNotifications(Request $request)
    {
        $user = api_user();
        $userId = (int)data_get($user, 'id');
        $perPage = (int)$request->get('per_page', 20);
        $perPage = $perPage > 0 ? min($perPage, 100) : 20;

        $notifications = FirebaseNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function notificationById(string $id)
    {
        $user = api_user();
        $userId = (int)data_get($user, 'id');
        $notification = FirebaseNotification::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
        ]);

        $user = api_user();
        $userId = (int)data_get($user, 'id');
        $notification = FirebaseNotification::query()
            ->where('user_id', $userId)
            ->where('id', $request->notification_id)
            ->firstOrFail();
        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead()
    {
        $user = api_user();
        $userId = (int)data_get($user, 'id');
        FirebaseNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

}
