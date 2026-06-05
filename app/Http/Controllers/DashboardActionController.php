<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DashboardActionController extends Controller
{
    private function workspaceId(Request $request): int
    {
        return (int) DB::table('workspace_user')
            ->where('user_id', $request->user()->id)
            ->value('workspace_id') ?: 1;
    }

    public function contact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'phone_number' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email'],
            'status' => ['required', 'string', 'max:60'],
            'deal_value' => ['nullable', 'numeric', 'min:0'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $countryCode = $data['country_code'] ?? '+92';
        unset($data['country_code']);
        $data['phone_number'] = $this->normalizePhoneNumber($data['phone_number'], $countryCode);

        if (DB::table('contacts')->where('workspace_id', $workspaceId)->where('phone_number', $data['phone_number'])->exists()) {
            return back()->withErrors(['phone_number' => 'This phone number already exists in your CRM.']);
        }

        $accountId = $this->activeWhatsAppAccountId($workspaceId);
        if (! $accountId) {
            $accountId = DB::table('whatsapp_accounts')->insertGetId([
                'workspace_id' => $workspaceId,
                'name' => 'Main Business',
                'phone_number' => $countryCode.' 300 0000000',
                'provider' => 'meta',
                'status' => 'pending_setup',
                'quality_rating' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $contactId = DB::table('contacts')->insertGetId([
            ...$data,
            'workspace_id' => $workspaceId,
            'source' => 'manual',
            'owner_name' => $request->user()->name,
            'tags' => json_encode(['crm', 'country_code:'.$countryCode]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('leads')->insert([
            'workspace_id' => $workspaceId,
            'contact_id' => $contactId,
            'title' => $data['name'].' Deal',
            'stage' => $data['status'],
            'value' => $data['deal_value'] ?? 0,
            'score' => match ($data['status']) {
                'won' => 100,
                'interested' => 80,
                'follow_up' => 60,
                default => 40,
            },
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conversations')->insert([
            'workspace_id' => $workspaceId,
            'whatsapp_account_id' => $accountId,
            'contact_id' => $contactId,
            'status' => 'open',
            'priority' => 'normal',
            'unread_count' => 0,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Contact added and chat created successfully.');
    }

    public function message(Request $request, int $conversation): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);
        if (empty($data['body']) && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Type a message or attach a file.']);
        }
        $workspaceId = $this->workspaceId($request);
        $exists = DB::table('conversations')->where('id', $conversation)->where('workspace_id', $workspaceId)->exists();

        abort_unless($exists, 404);
        $subscription = DB::table('subscriptions')->where('workspace_id', $workspaceId)->latest()->first();
        $limits = json_decode($subscription->limits ?? '{}', true) ?: [];
        $messageLimit = (int) ($limits['messages'] ?? 1000);
        $usedMessages = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.workspace_id', $workspaceId)
            ->where('messages.created_at', '>=', now()->startOfMonth())
            ->count();

        if ($usedMessages >= $messageLimit) {
            return back()->with('error', "Your current plan allows {$messageLimit} messages per month. Please upgrade your subscription to send more.");
        }

        $conversationRecord = DB::table('conversations')
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->join('whatsapp_accounts', 'whatsapp_accounts.id', '=', 'conversations.whatsapp_account_id')
            ->where('conversations.id', $conversation)
            ->where('conversations.workspace_id', $workspaceId)
            ->select('contacts.phone_number as contact_phone', 'contacts.status as contact_status', 'conversations.status as conversation_status', 'whatsapp_accounts.settings as account_settings')
            ->first();
        if (($conversationRecord?->contact_status === 'blocked') || ($conversationRecord?->conversation_status === 'blocked')) {
            return back()->with('error', 'This contact is blocked. Unblock the contact before sending a message.');
        }
        $file = $request->file('attachment');
        if ($file) {
            $blocked = ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'html', 'htm', 'sh', 'msi'];
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === '') {
                $extension = $file->guessExtension() ?: match ($file->getClientMimeType()) {
                    'application/pdf' => 'pdf',
                    'text/plain' => 'txt',
                    'text/csv' => 'csv',
                    default => 'file',
                };
            }
            if (in_array($extension, $blocked, true)) {
                return back()->withErrors(['attachment' => 'This file type is blocked for security.']);
            }
        }
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversation,
            'direction' => 'outbound',
            'sender_type' => 'agent',
            'body' => $data['body'] ?? ($file ? $file->getClientOriginalName() : ''),
            'message_type' => $file ? (str_starts_with((string) $file->getMimeType(), 'image/') ? 'image' : 'document') : 'text',
            'status' => 'sent',
            'ai_generated' => false,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($file) {
            $directory = public_path('uploads/messages');
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            if (! File::isWritable($directory)) {
                return back()->withErrors(['attachment' => 'Upload folder is not writable.']);
            }
            $filename = Str::uuid().'.'.$extension;
            $file->move($directory, $filename);
            DB::table('message_media')->insert([
                'message_id' => $messageId,
                'disk' => 'public',
                'path' => '/uploads/messages/'.$filename,
                'mime_type' => $file->getClientMimeType(),
                'size' => File::size(public_path('uploads/messages/'.$filename)),
                'metadata' => json_encode(['original_name' => $file->getClientOriginalName()]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! $file && ! empty($data['body']) && $conversationRecord) {
            $settings = json_decode($conversationRecord->account_settings ?? '{}', true) ?: [];
            $token = $settings['access_token'] ?? null;
            $phoneNumberId = $settings['phone_number_id'] ?? null;
            if ($token && $phoneNumberId) {
                try {
                    $response = Http::withToken($token)
                        ->timeout(15)
                        ->connectTimeout(10)
                        ->withOptions(['proxy' => ''])
                        ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                            'messaging_product' => 'whatsapp',
                    'to' => preg_replace('/\D+/', '', $this->normalizePhoneNumber($conversationRecord->contact_phone)),
                            'type' => 'text',
                            'text' => ['body' => $data['body']],
                        ]);

                    DB::table('messages')->where('id', $messageId)->update([
                        'status' => $response->successful() ? 'sent' : 'failed',
                        'metadata' => json_encode([
                            'provider' => 'meta',
                            'provider_message_id' => $response->json('messages.0.id'),
                            'error' => $response->successful() ? null : $response->json(),
                        ]),
                        'updated_at' => now(),
                    ]);

                    if (! $response->successful()) {
                        DB::table('conversations')->where('id', $conversation)->update([
                            'last_message_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return back()->with('error', 'Message saved, but Meta WhatsApp rejected it. Check Phone Number ID, access token, recipient number and template/conversation window.');
                    }
                } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                    DB::table('messages')->where('id', $messageId)->update([
                        'status' => 'failed',
                        'metadata' => json_encode([
                            'provider' => 'meta',
                            'error' => $exception->getMessage(),
                        ]),
                        'updated_at' => now(),
                    ]);
                    DB::table('conversations')->where('id', $conversation)->update([
                        'last_message_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return back()->with('error', 'Message saved, but Meta WhatsApp could not be reached. Check internet/proxy settings and try again.');
                }
            }
        }

        DB::table('conversations')->where('id', $conversation)->update([
            'last_message_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Message saved in chat.');
    }

    public function markConversationRead(Request $request, int $conversation): RedirectResponse
    {
        DB::table('conversations')
            ->where('id', $conversation)
            ->where('workspace_id', $this->workspaceId($request))
            ->update(['unread_count' => 0, 'updated_at' => now()]);

        return back();
    }

    public function messageStatus(Request $request, int $message): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:sent,delivered,read,failed']]);
        $workspaceId = $this->workspaceId($request);
        $exists = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('messages.id', $message)
            ->where('conversations.workspace_id', $workspaceId)
            ->exists();

        abort_unless($exists, 404);

        DB::table('messages')->where('id', $message)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Message status updated.');
    }

    public function deleteMessage(Request $request, int $message): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->leftJoin('message_media', 'message_media.message_id', '=', 'messages.id')
            ->where('messages.id', $message)
            ->where('conversations.workspace_id', $workspaceId)
            ->select('messages.id', 'message_media.path')
            ->first();

        abort_unless($record, 404);

        if ($record->path && str_starts_with($record->path, '/uploads/messages/')) {
            $filePath = public_path(ltrim($record->path, '/'));
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        DB::table('messages')->where('id', $message)->delete();

        return back()->with('success', 'Message deleted successfully.');
    }

    public function editMessage(Request $request, int $message): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->join('whatsapp_accounts', 'whatsapp_accounts.id', '=', 'conversations.whatsapp_account_id')
            ->where('messages.id', $message)
            ->where('conversations.workspace_id', $workspaceId)
            ->select(
                'messages.id',
                'messages.direction',
                'messages.created_at',
                'messages.metadata',
                'contacts.phone_number as contact_phone',
                'whatsapp_accounts.settings as account_settings'
            )
            ->first();

        abort_unless($record, 404);

        if ($record->direction !== 'outbound') {
            return back()->with('error', 'Only your sent messages can be edited.');
        }

        if (\Illuminate\Support\Carbon::parse($record->created_at)->lt(now()->subMinutes(5))) {
            return back()->with('error', 'Message edit time expired. You can edit messages within 5 minutes only.');
        }

        $metadata = json_decode($record->metadata ?? '{}', true) ?: [];
        $metadata['edited'] = true;
        $metadata['edited_at'] = now()->toIso8601String();

        $settings = json_decode($record->account_settings ?? '{}', true) ?: [];
        $token = $settings['access_token'] ?? null;
        $phoneNumberId = $settings['phone_number_id'] ?? null;

        if ($token && $phoneNumberId && ! empty($metadata['provider_message_id'])) {
            try {
                $response = Http::withToken($token)
                    ->timeout(15)
                    ->connectTimeout(10)
                    ->withOptions(['proxy' => ''])
                    ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => preg_replace('/\D+/', '', $this->normalizePhoneNumber($record->contact_phone)),
                        'type' => 'text',
                        'text' => ['body' => 'Edited: '.$data['body']],
                    ]);

                $metadata['edit_follow_up_provider_message_id'] = $response->json('messages.0.id');
                $metadata['edit_follow_up_error'] = $response->successful() ? null : $response->json();
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                $metadata['edit_follow_up_error'] = $exception->getMessage();
            }
        }

        DB::table('messages')->where('id', $message)->update([
            'body' => $data['body'],
            'metadata' => json_encode($metadata),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Message edited successfully. WhatsApp receives edited text as a new follow-up message.');
    }

    public function syncMessageStatuses(Request $request): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $messageIds = DB::table('messages')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.workspace_id', $workspaceId)
            ->where('messages.direction', 'outbound')
            ->whereIn('messages.status', ['sent', 'delivered'])
            ->pluck('messages.id');

        if ($messageIds->isNotEmpty()) {
            DB::table('messages')
                ->whereIn('id', $messageIds)
                ->where('status', 'sent')
                ->where('created_at', '<=', now()->subSeconds(5))
                ->update(['status' => 'delivered', 'updated_at' => now()]);

            DB::table('messages')
                ->whereIn('id', $messageIds)
                ->where('status', 'delivered')
                ->where('updated_at', '<=', now()->subSeconds(8))
                ->update(['status' => 'read', 'updated_at' => now()]);
        }

        return back();
    }

    public function deleteConversation(Request $request, int $conversation): RedirectResponse
    {
        DB::table('conversations')
            ->where('id', $conversation)
            ->where('workspace_id', $this->workspaceId($request))
            ->delete();

        return back()->with('success', 'Chat deleted successfully.');
    }

    public function clearConversation(Request $request, int $conversation): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $exists = DB::table('conversations')
            ->where('id', $conversation)
            ->where('workspace_id', $workspaceId)
            ->exists();

        abort_unless($exists, 404);

        $mediaRows = DB::table('message_media')
            ->join('messages', 'messages.id', '=', 'message_media.message_id')
            ->where('messages.conversation_id', $conversation)
            ->select('message_media.path')
            ->get();

        foreach ($mediaRows as $media) {
            if ($media->path && str_starts_with($media->path, '/uploads/messages/')) {
                $filePath = public_path(ltrim($media->path, '/'));
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }

        DB::table('messages')->where('conversation_id', $conversation)->delete();
        DB::table('conversations')->where('id', $conversation)->update([
            'unread_count' => 0,
            'last_message_at' => null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Chat cleared successfully.');
    }

    public function deleteContact(Request $request, int $contact): RedirectResponse
    {
        DB::table('contacts')
            ->where('id', $contact)
            ->where('workspace_id', $this->workspaceId($request))
            ->delete();

        return back()->with('success', 'Contact and related chat deleted successfully.');
    }

    public function updateContact(Request $request, int $contact): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'phone_number' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email'],
            'status' => ['required', 'in:new_lead,interested,follow_up,won,lost,blocked'],
            'deal_value' => ['nullable', 'numeric', 'min:0'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);
        $countryCode = $data['country_code'] ?? '+92';
        unset($data['country_code'], $data['avatar']);
        $data['phone_number'] = $this->normalizePhoneNumber($data['phone_number'], $countryCode);

        $exists = DB::table('contacts')
            ->where('id', $contact)
            ->where('workspace_id', $workspaceId)
            ->exists();
        abort_unless($exists, 404);

        $duplicate = DB::table('contacts')
            ->where('workspace_id', $workspaceId)
            ->where('phone_number', $data['phone_number'])
            ->where('id', '!=', $contact)
            ->exists();
        if ($duplicate) {
            return back()->withErrors(['phone_number' => 'This phone number already exists in your CRM.']);
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $directory = public_path('uploads/contacts');
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            $file = $request->file('avatar');
            $filename = Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $file->move($directory, $filename);
            $avatarPath = '/uploads/contacts/'.$filename;
        }

        DB::table('contacts')->where('id', $contact)->where('workspace_id', $workspaceId)->update([
            ...$data,
            ...($avatarPath ? ['avatar' => $avatarPath] : []),
            'updated_at' => now(),
        ]);
        DB::table('leads')->where('contact_id', $contact)->where('workspace_id', $workspaceId)->update([
            'stage' => $data['status'],
            'value' => $data['deal_value'] ?? 0,
            'updated_at' => now(),
        ]);
        DB::table('conversations')->where('contact_id', $contact)->where('workspace_id', $workspaceId)->update([
            'status' => $data['status'] === 'blocked' ? 'blocked' : 'open',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Contact profile updated successfully.');
    }

    public function blockContact(Request $request, int $contact): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $data = $request->validate([
            'blocked' => ['required', 'boolean'],
        ]);
        $exists = DB::table('contacts')
            ->where('id', $contact)
            ->where('workspace_id', $workspaceId)
            ->exists();

        abort_unless($exists, 404);

        DB::table('contacts')->where('id', $contact)->where('workspace_id', $workspaceId)->update([
            'status' => $data['blocked'] ? 'blocked' : 'new_lead',
            'updated_at' => now(),
        ]);
        DB::table('conversations')->where('contact_id', $contact)->where('workspace_id', $workspaceId)->update([
            'status' => $data['blocked'] ? 'blocked' : 'open',
            'updated_at' => now(),
        ]);

        return back()->with('success', $data['blocked'] ? 'Contact blocked successfully.' : 'Contact unblocked successfully.');
    }

    public function contactStage(Request $request, int $contact): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new_lead,interested,follow_up,won,lost,blocked']]);
        $workspaceId = $this->workspaceId($request);

        DB::table('contacts')->where('id', $contact)->where('workspace_id', $workspaceId)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);
        DB::table('leads')->where('contact_id', $contact)->where('workspace_id', $workspaceId)->update([
            'stage' => $data['status'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'CRM stage updated.');
    }

    public function contactNote(Request $request, int $contact): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $exists = DB::table('contacts')->where('id', $contact)->where('workspace_id', $workspaceId)->exists();

        abort_unless($exists, 404);

        DB::table('contact_notes')->insert([
            'contact_id' => $contact,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_internal' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! empty($data['next_follow_up_at'])) {
            DB::table('leads')->where('contact_id', $contact)->where('workspace_id', $workspaceId)->update([
                'next_follow_up_at' => $data['next_follow_up_at'],
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'CRM note saved.');
    }

    public function subscription(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'in:starter,pro,agency'],
        ]);

        if (config('services.stripe.secret')) {
            return back()->with('error', 'Please use secure checkout to activate a subscription.');
        }

        $workspaceId = $this->workspaceId($request);
        $limits = [
            'starter' => ['whatsapp_accounts' => 1, 'messages' => 1000, 'team_members' => 2],
            'pro' => ['whatsapp_accounts' => 3, 'messages' => 10000, 'team_members' => 10],
            'agency' => ['whatsapp_accounts' => 10, 'messages' => 100000, 'team_members' => 50],
        ][$data['plan']];

        DB::table('subscriptions')->updateOrInsert(
            ['workspace_id' => $workspaceId],
            [
                'plan' => $data['plan'],
                'status' => 'active',
                'limits' => json_encode($limits),
                'renews_at' => now()->addMonth(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('workspaces')->where('id', $workspaceId)->update(['plan' => $data['plan'], 'updated_at' => now()]);

        return redirect('/app/dashboard')->with('success', 'Demo subscription activated successfully.');
    }

    public function team(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'role' => ['required', 'in:owner,manager,agent,viewer'],
        ]);
        $userId = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('workspace_user')->insert([
            'workspace_id' => $this->workspaceId($request),
            'user_id' => $userId,
            'role' => $data['role'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Team member invited successfully.');
    }

    public function whatsappAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'phone_number_id' => ['required', 'string', 'max:120'],
            'access_token' => ['required', 'string', 'max:500'],
            'verify_token' => ['required', 'string', 'max:120'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $subscription = DB::table('subscriptions')->where('workspace_id', $workspaceId)->latest()->first();
        $limits = json_decode($subscription->limits ?? '{}', true) ?: [];
        $accountLimit = (int) ($limits['whatsapp_accounts'] ?? 1);
        $connectedAccounts = DB::table('whatsapp_accounts')
            ->where('workspace_id', $workspaceId)
            ->where('status', '!=', 'pending_setup')
            ->count();

        if ($connectedAccounts >= $accountLimit) {
            return back()->with('error', "Your current plan allows {$accountLimit} WhatsApp account(s). Please upgrade your subscription to add more.");
        }

        $settings = [
            'phone_number_id' => $data['phone_number_id'],
            'access_token' => $data['access_token'],
            'verify_token' => $data['verify_token'],
        ];
        unset($data['phone_number_id'], $data['access_token'], $data['verify_token']);

        DB::table('whatsapp_accounts')->insert([
            ...$data,
            'workspace_id' => $workspaceId,
            'provider' => 'meta',
            'status' => 'connected',
            'quality_rating' => 'high',
            'last_synced_at' => now(),
            'settings' => json_encode($settings),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'WhatsApp account connected.');
    }

    public function updateWhatsAppAccount(Request $request, int $account): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'phone_number_id' => ['required', 'string', 'max:120'],
            'access_token' => ['nullable', 'string', 'max:500'],
            'verify_token' => ['required', 'string', 'max:120'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('whatsapp_accounts')
            ->where('id', $account)
            ->where('workspace_id', $workspaceId)
            ->first();

        abort_unless($record, 404);

        $oldSettings = json_decode($record->settings ?? '{}', true) ?: [];
        $settings = [
            'phone_number_id' => $data['phone_number_id'],
            'access_token' => $data['access_token'] ?: ($oldSettings['access_token'] ?? null),
            'verify_token' => $data['verify_token'],
        ];
        unset($data['phone_number_id'], $data['access_token'], $data['verify_token']);

        DB::table('whatsapp_accounts')
            ->where('id', $account)
            ->where('workspace_id', $workspaceId)
            ->update([
                ...$data,
                'status' => 'connected',
                'last_synced_at' => now(),
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'WhatsApp account updated successfully.');
    }

    public function deleteWhatsAppAccount(Request $request, int $account): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $exists = DB::table('whatsapp_accounts')
            ->where('id', $account)
            ->where('workspace_id', $workspaceId)
            ->exists();

        abort_unless($exists, 404);

        $mediaRows = DB::table('message_media')
            ->join('messages', 'messages.id', '=', 'message_media.message_id')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.workspace_id', $workspaceId)
            ->where('conversations.whatsapp_account_id', $account)
            ->select('message_media.path')
            ->get();

        foreach ($mediaRows as $media) {
            if ($media->path && str_starts_with($media->path, '/uploads/messages/')) {
                $filePath = public_path(ltrim($media->path, '/'));
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }

        DB::table('whatsapp_accounts')
            ->where('id', $account)
            ->where('workspace_id', $workspaceId)
            ->delete();

        return back()->with('success', 'WhatsApp account deleted successfully.');
    }

    public function automation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger' => ['required', 'string', 'max:255'],
        ]);
        DB::table('ai_automations')->insert([
            ...$data,
            'workspace_id' => $this->workspaceId($request),
            'status' => 'active',
            'flow' => json_encode(['conditions' => [], 'actions' => ['send_ai_reply']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Automation created.');
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'audience_count' => ['required', 'integer', 'min:1'],
        ]);
        DB::table('broadcast_campaigns')->insert([
            ...$data,
            'workspace_id' => $this->workspaceId($request),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Broadcast campaign created.');
    }

    public function training(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:document,url,faq'],
        ]);
        DB::table('ai_training_sources')->insert([
            ...$data,
            'workspace_id' => $this->workspaceId($request),
            'status' => 'indexed',
            'chunks_count' => 0,
            'trained_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Training source added.');
    }

    public function integration(Request $request): RedirectResponse
    {
        $data = $request->validate(['provider' => ['required', 'string', 'max:80']]);
        DB::table('connected_integrations')->updateOrInsert(
            ['workspace_id' => $this->workspaceId($request), 'provider' => Str::slug($data['provider'], '_')],
            ['status' => 'connected', 'settings' => json_encode([]), 'last_synced_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Integration connected.');
    }

    public function apiKey(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $token = 'cf_'.Str::random(48);
        DB::table('api_keys')->insert([
            'workspace_id' => $this->workspaceId($request),
            'name' => $data['name'],
            'token_hash' => hash('sha256', $token),
            'abilities' => json_encode(['read', 'write']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'API key created. Copy it now: '.$token);
    }

    public function profile(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email']]);
        $request->user()->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate(['workspace_name' => ['required', 'string', 'max:255'], 'timezone' => ['required', 'string', 'max:80']]);
        DB::table('workspaces')->where('id', $this->workspaceId($request))->update([
            'name' => $data['workspace_name'],
            'timezone' => $data['timezone'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Workspace settings saved.');
    }

    public function adminWorkspaceSubscription(Request $request, int $workspace): RedirectResponse
    {
        abort_unless($request->user()?->email === 'admin@chatflow.test', 403);

        $data = $request->validate([
            'plan' => ['required', 'in:starter,pro,agency'],
            'status' => ['required', 'in:active,expired,canceled'],
        ]);
        $limits = [
            'starter' => ['whatsapp_accounts' => 1, 'messages' => 1000, 'team_members' => 2],
            'pro' => ['whatsapp_accounts' => 3, 'messages' => 10000, 'team_members' => 10],
            'agency' => ['whatsapp_accounts' => 10, 'messages' => 100000, 'team_members' => 50],
        ][$data['plan']];

        DB::table('subscriptions')->updateOrInsert(
            ['workspace_id' => $workspace],
            [
                'plan' => $data['plan'],
                'status' => $data['status'],
                'limits' => json_encode($limits),
                'trial_ends_at' => null,
                'renews_at' => $data['status'] === 'active' ? now()->addMonth() : null,
                'ends_at' => $data['status'] === 'active' ? null : now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        DB::table('workspaces')->where('id', $workspace)->update([
            'plan' => $data['plan'],
            'updated_at' => now(),
        ]);
        DB::table('activity_logs')->insert([
            'workspace_id' => $workspace,
            'type' => 'platform.subscription.updated',
            'description' => 'Platform admin updated subscription to '.ucfirst($data['plan']).' / '.ucfirst($data['status']),
            'properties' => json_encode(['admin_user_id' => $request->user()->id, 'plan' => $data['plan'], 'status' => $data['status']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Customer subscription updated successfully.');
    }

    private function activeWhatsAppAccountId(int $workspaceId): ?int
    {
        $account = DB::table('whatsapp_accounts')
            ->where('workspace_id', $workspaceId)
            ->whereNotNull('settings')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(settings, '$.access_token')) IS NOT NULL")
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(settings, '$.phone_number_id')) IS NOT NULL")
            ->latest('updated_at')
            ->value('id');

        return $account ? (int) $account : null;
    }

    private function normalizePhoneNumber(string $phone, string $countryCode = '+92'): string
    {
        $phone = trim($phone);
        $countryCode = '+'.preg_replace('/\D+/', '', $countryCode);

        if ($phone === '') {
            return $countryCode;
        }

        if (str_starts_with($phone, '+')) {
            return '+'.preg_replace('/\D+/', '', $phone);
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        $countryDigits = ltrim($countryCode, '+');
        if (str_starts_with($digits, $countryDigits)) {
            return '+'.$digits;
        }

        $digits = ltrim($digits, '0');

        return $countryCode.$digits;
    }
}
