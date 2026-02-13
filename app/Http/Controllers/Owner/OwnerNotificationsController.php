<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerNotificationsController extends Controller
{
    public function index()
    {
        $user = Auth::guard('owner')->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type, // e.g., 'App\Notifications\NewOrder'
                    'title' => $this->formatTitle($n),
                    'body' => $this->formatBody($n),
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->toISOString(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::guard('owner')->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('owner')->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    private function formatTitle($notification)
    {
        // Add custom formatting based on notification type if needed
        return $notification->data['title'] ?? 'System Notification';
    }

    private function formatBody($notification)
    {
        return $notification->data['body'] ?? $notification->data['message'] ?? '';
    }
}
