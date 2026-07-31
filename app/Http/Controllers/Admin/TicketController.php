<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Controllers\Traits\VendorWalletTrait;
use App\Models\Module;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TicketController extends Controller
{
    use MediaUploadingTrait, VendorWalletTrait;

    public function index(Request $request)
    {
        $module = Module::where('default_module', '1')->first();
        $moduleId = $module?->id;
        $moduleName = $module?->name ?? 'Mili Taxi';
        $status = request()->input('status');

        $query = SupportTicket::query()
            ->when($moduleId, fn ($builder) => $builder->where('module', $moduleId))
            ->with(['appUser:id,first_name,last_name'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        $statusCounts = [
            'all' => SupportTicket::count(),
            'open' => SupportTicket::where('thread_status', 1)->count(),
            'closed' => SupportTicket::where('thread_status', 0)->count(),
        ];

        $isFiltered = ($status);

        if ($status !== null) {
            $query->where('support_tickets.thread_status', $status);
        }

        $query->orderBy('support_tickets.id', 'desc');

        $data = $isFiltered ? $query->paginate(50) : $query->paginate(50);

        return view('admin.ticket.index', compact('data', 'moduleName', 'statusCounts'));
    }

    public function reply(Request $request, $id)
    {

        $data = SupportTicket::with(['replies.appUser'])
            ->where('id', $id)
            ->firstOrFail();

        $replies = $data->replies()->orderBy('id', 'desc')->paginate(50);

        return view('admin.ticket.ticketmessage', compact('data', 'replies'));
    }

    public function threads(Request $request, $id)
    {

        $userId = Auth::id();
        $adminedata = User::find($userId);

        $supportTicketData = SupportTicket::where('id', $id)->first();

        $supportTicketReplies = SupportTicket::with(['appUser', 'replies' => function ($query) {
            $query->orderBy('id');
        }])->findOrFail($id);

        return view('admin.ticket.thread', compact('id', 'adminedata', 'supportTicketData', 'supportTicketReplies'));
    }

    public function messages(Request $request, $id)
    {
        $ticket = SupportTicket::with(['appUser', 'replies' => function ($query) {
            $query->orderBy('id')->limit(200);
        }])->findOrFail($id);

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'operator_active' => $ticket->operator_active,
                'ai_enabled' => $ticket->ai_enabled,
            ],
            'messages' => $ticket->replies->map(fn (SupportTicketReply $reply): array => [
                'id' => $reply->id,
                'message' => $reply->message,
                'is_support' => (bool) $reply->is_admin_reply,
                'source' => $reply->source,
                'sender' => $reply->is_admin_reply
                    ? ($reply->source === 'ai' ? 'AI' : 'ოპერატორი')
                    : trim(($ticket->appUser?->first_name ?? '').' '.($ticket->appUser?->last_name ?? '')),
                'attachment_url' => $reply->attachment_path
                    ? URL::temporarySignedRoute(
                        'support-chat.attachment',
                        now()->addMinutes(15),
                        ['reply' => $reply->id]
                    )
                    : null,
                'attachment_name' => $reply->attachment_name,
                'created_at' => optional($reply->created_at)->format('d.m.Y H:i'),
            ])->values(),
        ]);
    }

    public function create(Request $request, $id)
    {
        $request->validate([
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
        $status = 1;
        $admin = 1;
        $userId = Auth::id();
        $ticket = SupportTicket::findOrFail($id);

        $storedAttachmentPath = null;
        try {
            $attachmentData = [];
            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $storedAttachmentPath = $attachment->store("support-chat/{$ticket->id}", 'local');
                $attachmentData = [
                    'attachment_path' => $storedAttachmentPath,
                    'attachment_name' => mb_substr($attachment->getClientOriginalName(), 0, 255),
                    'attachment_mime' => $attachment->getMimeType(),
                    'attachment_size' => $attachment->getSize(),
                ];
            }

            SupportTicketReply::create(array_merge([
                'thread_id' => $id,
                'user_id' => $userId,
                'is_admin_reply' => $admin,
                'message' => trim(strip_tags((string) $request->input('message'))),
                'reply_status' => $status,
                'source' => 'operator',
            ], $attachmentData));
        } catch (\Throwable $exception) {
            if ($storedAttachmentPath) {
                Storage::disk('local')->delete($storedAttachmentPath);
            }
            throw $exception;
        }

        $ticket->update([
            'operator_active' => true,
            'ai_enabled' => false,
            'last_message_at' => now(),
        ]);

        $templateId = 42;
        $this->sendNotificationOnTicketReply($id, $ticket->user_id, $ticket->title, $templateId);

        return redirect()->route('admin.ticket.thread', $id);

    }

    public function mode(Request $request, $id)
    {
        $request->validate(['mode' => 'required|in:ai,operator']);
        $ticket = SupportTicket::findOrFail($id);
        $operator = $request->input('mode') === 'operator';
        $ticket->update([
            'operator_active' => $operator,
            'ai_enabled' => ! $operator,
        ]);

        return redirect()
            ->route('admin.ticket.thread', $id)
            ->with('message', $operator
                ? 'Operator mode enabled.'
                : 'AI assistant enabled until an operator replies.');
    }

    public function destroy($id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);
            $ticket->delete();

            return response()->json(['message' => 'Ticket deleted successfully.']);
        } catch (\Exception $e) {

            return response()->json(['message' => 'Error deleting ticket.'], 500);
        }
    }

    public function ticketDeleteAll(Request $request) //
    {
        abort_if(Gate::denies('ticket_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $ids = $request->input('ids');

        if (! empty($ids)) {
            try {
                SupportTicket::whereIn('id', $ids)
                    ->get()
                    ->each(fn (SupportTicket $ticket) => $ticket->delete());

                return response()->json(['message' => 'Items deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Something went wrong'], 500);
            }
        }

    }
}
