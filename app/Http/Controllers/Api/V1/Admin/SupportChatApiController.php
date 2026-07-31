<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Models\AppUser;
use App\Models\Module;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\OpenAiSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;

class SupportChatApiController extends Controller
{
    use ResponseTrait;

    public function show(Request $request)
    {
        $user = $this->authenticatedAppUser($request);
        if (! $user) {
            return $this->addErrorResponse(401, trans('global.token_not_match'), '');
        }

        $ticket = SupportTicket::query()
            ->where('user_id', $user->id)
            ->where('app_role', $this->appRole($user))
            ->latest('id')
            ->first();

        return $this->addSuccessResponse(200, 'Support chat retrieved.', [
            'ticket' => $ticket ? $this->ticketData($ticket) : null,
            'messages' => $ticket ? $this->messages($ticket) : [],
        ]);
    }

    public function store(Request $request, OpenAiSupportService $openAi)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:2000|required_without:attachment',
            'attachment' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
                'dimensions:max_width=4096,max_height=4096',
                'required_without:message',
            ],
        ]);
        if ($validator->fails()) {
            return $this->errorComputing($validator);
        }

        $user = $this->authenticatedAppUser($request);
        if (! $user) {
            return $this->addErrorResponse(401, trans('global.token_not_match'), '');
        }

        $message = trim(strip_tags((string) $request->input('message')));
        $attachment = $request->file('attachment');

        $role = $this->appRole($user);
        $writeLock = Cache::lock("support-chat:{$user->id}:{$role}", 10);
        if (! $writeLock->get()) {
            return $this->addErrorResponse(409, 'The previous message is still being saved.', '');
        }

        $storedAttachmentPath = null;
        try {
            [$ticket, $userReplyId] = DB::transaction(function () use (
                $message,
                $attachment,
                $user,
                $role,
                &$storedAttachmentPath
            ): array {
                $ticket = SupportTicket::query()
                    ->where('user_id', $user->id)
                    ->where('app_role', $role)
                    ->where('thread_status', true)
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                if (! $ticket) {
                    $ticket = SupportTicket::create([
                        'user_id' => $user->id,
                        'module' => Module::where('default_module', 1)->value('id'),
                        'thread_id' => (string) Str::uuid(),
                        'app_role' => $role,
                        'title' => 'Live support',
                        'description' => mb_substr($message !== '' ? $message : 'Photo', 0, 255),
                        'thread_status' => true,
                        'ai_enabled' => true,
                        'operator_active' => false,
                    ]);
                }

                $attachmentData = [];
                if ($attachment) {
                    $storedAttachmentPath = $attachment->store("support-chat/{$ticket->id}", 'local');
                    $attachmentData = [
                        'attachment_path' => $storedAttachmentPath,
                        'attachment_name' => mb_substr($attachment->getClientOriginalName(), 0, 255),
                        'attachment_mime' => $attachment->getMimeType(),
                        'attachment_size' => $attachment->getSize(),
                    ];
                }

                $reply = SupportTicketReply::create(array_merge([
                    'thread_id' => $ticket->id,
                    'user_id' => $user->id,
                    'is_admin_reply' => false,
                    'message' => $message,
                    'reply_status' => true,
                    'source' => 'user',
                ], $attachmentData));
                $ticket->update(['last_message_at' => now()]);

                return [$ticket, $reply->id];
            }, 3);
        } catch (\Throwable $exception) {
            if ($storedAttachmentPath) {
                Storage::disk('local')->delete($storedAttachmentPath);
            }
            throw $exception;
        } finally {
            $writeLock->release();
        }

        $aiLock = Cache::lock("support-chat-ai:{$ticket->id}", 30);
        if ($message !== '' && $aiLock->get()) {
            try {
                $aiText = $openAi->reply($ticket->fresh());
                if ($aiText) {
                    DB::transaction(function () use ($ticket, $userReplyId, $aiText): void {
                        $lockedTicket = SupportTicket::whereKey($ticket->id)
                            ->lockForUpdate()
                            ->first();
                        $latestReplyId = SupportTicketReply::where('thread_id', $ticket->id)
                            ->max('id');

                        if (! $lockedTicket
                            || ! $lockedTicket->ai_enabled
                            || $lockedTicket->operator_active
                            || (int) $latestReplyId !== (int) $userReplyId) {
                            return;
                        }

                        SupportTicketReply::create([
                            'thread_id' => $ticket->id,
                            'user_id' => null,
                            'is_admin_reply' => true,
                            'message' => $aiText,
                            'reply_status' => true,
                            'source' => 'ai',
                        ]);
                        $lockedTicket->update(['last_message_at' => now()]);
                    }, 3);
                }
            } finally {
                $aiLock->release();
            }
        }

        return $this->addSuccessResponse(200, 'Message sent.', [
            'ticket' => $this->ticketData($ticket->fresh()),
            'messages' => $this->messages($ticket),
        ]);
    }

    public function attachment(Request $request, SupportTicketReply $reply)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(
            $reply->attachment_path
                && Storage::disk('local')->exists($reply->attachment_path),
            404
        );

        return Storage::disk('local')->response(
            $reply->attachment_path,
            $reply->attachment_name,
            [
                'Content-Type' => $reply->attachment_mime ?: 'application/octet-stream',
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function authenticatedAppUser(Request $request): ?AppUser
    {
        return AppUser::where('token', $request->input('token'))->first();
    }

    private function appRole(AppUser $user): string
    {
        return strtolower((string) $user->user_type) === 'driver' ? 'driver' : 'rider';
    }

    private function ticketData(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'thread_id' => $ticket->thread_id,
            'status' => $ticket->thread_status ? 'open' : 'closed',
            'ai_enabled' => $ticket->ai_enabled,
            'operator_active' => $ticket->operator_active,
        ];
    }

    private function messages(SupportTicket $ticket): array
    {
        return $ticket->replies()
            ->oldest('id')
            ->limit(200)
            ->get()
            ->map(fn (SupportTicketReply $reply): array => [
                'id' => $reply->id,
                'message' => $reply->message,
                'is_support' => $reply->is_admin_reply,
                'source' => $reply->source,
                'attachment_url' => $reply->attachment_path
                    ? URL::temporarySignedRoute(
                        'support-chat.attachment',
                        now()->addMinutes(15),
                        ['reply' => $reply->id]
                    )
                    : null,
                'attachment_name' => $reply->attachment_name,
                'attachment_mime' => $reply->attachment_mime,
                'attachment_size' => $reply->attachment_size,
                'created_at' => optional($reply->created_at)->toIso8601String(),
            ])
            ->all();
    }
}
