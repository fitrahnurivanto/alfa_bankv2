<?php

namespace App\Services;

use App\Models\Notification as NotificationModel;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification to user(s) with preference check
     * 
     * @param mixed $users User instance, array of users, or collection
     * @param object $notification Notification instance
     * @param string $type Notification type (e.g., 'project_deadline')
     */
    public static function send($users, $notification, $type)
    {
        // Ensure $users is iterable
        if (!is_array($users) && !$users instanceof \Illuminate\Support\Collection) {
            $users = [$users];
        }

        foreach ($users as $user) {
            // Check if user has email enabled for this notification type
            $emailEnabled = NotificationSetting::isEnabled($user->id, $type, 'email');
            $inAppEnabled = NotificationSetting::isEnabled($user->id, $type, 'in_app');

            if ($emailEnabled || $inAppEnabled) {
                $user->notify($notification);
            }
        }
    }

    /**
     * Create in-app notification manually
     */
    public static function create($userId, $type, $title, $message, $data = [], $actionUrl = null)
    {
        return NotificationModel::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId)
    {
        $notification = NotificationModel::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all user notifications as read
     */
    public static function markAllAsRead($userId)
    {
        NotificationModel::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get user's unread notifications
     */
    public static function getUnread($userId, $limit = 10)
    {
        return NotificationModel::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's all notifications with pagination
     */
    public static function getAll($userId, $perPage = 20)
    {
        return NotificationModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Delete notification
     */
    public static function delete($notificationId)
    {
        $notification = NotificationModel::find($notificationId);
        if ($notification) {
            return $notification->delete();
        }
        return false;
    }

    /**
     * Delete all read notifications for user
     */
    public static function deleteAllRead($userId)
    {
        return NotificationModel::where('user_id', $userId)
            ->read()
            ->delete();
    }
}
