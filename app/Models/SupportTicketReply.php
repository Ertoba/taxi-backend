<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'is_admin_reply',
        'message',
        'reply_status',
        'source',
    ];

    protected $casts = [
        'is_admin_reply' => 'boolean',
        'reply_status' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'thread_id');
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }
}
