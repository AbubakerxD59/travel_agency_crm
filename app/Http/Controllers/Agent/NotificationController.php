<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        // Mark unread notifications as read once agent opens notification center.
        $request->user()->unreadNotifications->markAsRead();

        return view('agent.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadQuery = $user->unreadNotifications();
        $unreadCount = (clone $unreadQuery)->count();

        $newlyFetched = (clone $unreadQuery)
            ->whereNull('sent_at')
            ->latest()
            ->get();

        $newNotifications = $newlyFetched
            ->map(function ($notification): array {
                $data = is_array($notification->data) ? $notification->data : [];

                return [
                    'id' => $notification->id,
                    'title' => (string) ($data['title'] ?? 'Notification'),
                    'message' => (string) ($data['message'] ?? ''),
                    'type' => (string) ($data['type'] ?? ''),
                    'customer_name' => (string) ($data['customer_name'] ?? ''),
                ];
            })
            ->values()
            ->all();

        if ($newlyFetched->isNotEmpty()) {
            $user->notifications()
                ->whereIn('id', $newlyFetched->pluck('id'))
                ->update(['sent_at' => now()]);
        }

        $notifications = $user->notifications()
            ->latest()
            ->get()
            ->map(function ($notification): array {
                $data = is_array($notification->data) ? $notification->data : [];

                return [
                    'id' => $notification->id,
                    'title' => (string) ($data['title'] ?? 'Notification'),
                    'message' => (string) ($data['message'] ?? ''),
                    'type' => (string) ($data['type'] ?? ''),
                    'customer_name' => (string) ($data['customer_name'] ?? ''),
                    'url' => route('agent.notifications.open', ['notificationId' => $notification->id]),
                    'is_read' => $notification->read_at !== null,
                    'created_at_human' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => $unreadCount,
            'new_count' => $newlyFetched->count(),
            'new_notifications' => $newNotifications,
            'has_unread' => $unreadCount > 0,
            'notifications' => $notifications,
        ]);
    }

    public function open(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $data = is_array($notification->data) ? $notification->data : [];
        $url = (string) ($data['url'] ?? '');

        if ($url !== '') {
            return redirect()->to($url);
        }

        return redirect()->route('agent.notifications.index');
    }
}
