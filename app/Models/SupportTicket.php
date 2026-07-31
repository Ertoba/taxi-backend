<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'thread_id',
        'app_role',
        'title',
        'description',
        'thread_status',
        'ai_enabled',
        'operator_active',
        'last_message_at',
    ];

    protected $casts = [
        'thread_status' => 'boolean',
        'ai_enabled' => 'boolean',
        'operator_active' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (SupportTicket $ticket): void {
            $ticket->replies()
                ->whereNotNull('attachment_path')
                ->pluck('attachment_path')
                ->each(fn (string $path) => Storage::disk('local')->delete($path));
        });
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'thread_id');
    }
}
