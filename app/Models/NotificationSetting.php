<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'in_app_enabled',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helpers
    public static function isEnabled($userId, $type, $channel = 'email')
    {
        $setting = self::where('user_id', $userId)
            ->where('notification_type', $type)
            ->first();

        if (!$setting) {
            return true; // Default enabled
        }

        return $channel === 'email' ? $setting->email_enabled : $setting->in_app_enabled;
    }
}
