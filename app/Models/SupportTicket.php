<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'thread_id');
    }
}
