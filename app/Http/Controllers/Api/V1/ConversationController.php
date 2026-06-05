<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\SendWhatsAppMessageAction;
use App\DTOs\OutboundMessageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use ApiResponse;

    public function index(Request $request, ConversationRepository $conversations)
    {
        return $this->success($conversations->listForWorkspace((int) $request->attributes->get('workspace_id')));
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        return $this->success($conversation->load(['contact', 'whatsappAccount', 'messages']));
    }

    public function update(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $conversation->update($request->validate([
            'status' => ['sometimes', 'in:open,pending,resolved,closed'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'unread_count' => ['sometimes', 'integer', 'min:0'],
        ]));

        return $this->success($conversation->fresh(), 'Conversation updated successfully');
    }

    public function sendMessage(SendMessageRequest $request, Conversation $conversation, SendWhatsAppMessageAction $send)
    {
        abort_unless($conversation->workspace_id === (int) $request->attributes->get('workspace_id'), 404);

        $message = $send->execute(new OutboundMessageData(
            conversationId: $conversation->id,
            body: $request->string('body')->toString(),
            senderType: $request->input('sender_type', 'agent'),
        ));

        return $this->success($message, 'Message sent successfully', status: 201);
    }
}
