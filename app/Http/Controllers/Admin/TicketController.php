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

class TicketController extends Controller
{
    use MediaUploadingTrait, VendorWalletTrait;

    public function index(Request $request)
    {
        $module = Module::where('default_module', '1')->first();
        $moduleId = $module?->id;
        $moduleName = $module?->name ?? 'RideOn';
        $status = request()->input('status');

        $query = SupportTicket::query()
            ->when($moduleId, fn ($builder) => $builder->where('module', $moduleId))
            ->with(['appUser:id,first_name,last_name'])
            ->orderBy('id', 'desc');

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
            $query->orderBy('id', 'desc');
        }])->findOrFail($id);

        return view('admin.ticket.thread', compact('id', 'adminedata', 'supportTicketData', 'supportTicketReplies'));
    }

    public function create(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:2000']);
        $status = 1;
        $admin = 1;
        $userId = Auth::id();

        $add = new SupportTicketReply;
        $add->thread_id = $id;
        $add->user_id = $userId;
        $add->is_admin_reply = $admin;
        $add->message = $request->message;
        $add->reply_status = $status;
        $add->source = 'operator';
        $add->save();

        $ticket = SupportTicket::findOrFail($id);
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
                SupportTicket::whereIn('id', $ids)->delete();

                return response()->json(['message' => 'Items deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Something went wrong'], 500);
            }
        }

    }
}
