<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentMessage;
use Illuminate\Http\Request;

class AdminParentMessageController extends Controller
{
    public function index()
    {
        $messages = ParentMessage::query()
            ->with(['parentAccount', 'student'])
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('parent.admin-messages', [
            'messages' => $messages,
        ]);
    }

    public function markRead(Request $request, ParentMessage $message)
    {
        $validated = $request->validate([
            'admin_reply' => 'nullable|string|max:1000',
        ]);

        $message->update([
            'status' => $validated['admin_reply']
                ? ParentMessage::STATUS_REPLIED
                : ParentMessage::STATUS_READ,
            'admin_reply' => $validated['admin_reply'] ?? $message->admin_reply,
            'read_at' => $message->read_at ?? now(),
        ]);

        return back()->with('message_success', 'Message updated.');
    }
}
