<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Support\PhoneNumber;

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
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->join('whatsapp_accounts', 'whatsapp_accounts.id', '=', 'conversations.whatsapp_account_id')
            ->first();

        abort_unless($record, 404);

        if ($record->direction !== 'outbound') {
            return back()->with('error', 'Only your sent messages can be edited.');
        }

        if (\Illuminate\Support\Carbon::parse($record->created_at)->lt(now()->subMinutes(5))) {
            return back()->with('error', 'Edit time expired. You can edit sent messages within 5 minutes only.');
        }

        $metadata = json_decode($record->metadata ?? '{}', true) ?: [];
        $metadata['edited'] = true;
        $metadata['edited_at'] = now()->toIso8601String();
        $metadata['previous_provider_message_id'] = $metadata['provider_message_id'] ?? null;

        $status = 'sent';
        $settings = json_decode($record->account_settings ?? '{}', true) ?: [];
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
                        'to' => PhoneNumber::e164Digits((string) $record->contact_phone),
                        'type' => 'text',
                        'text' => ['body' => $data['body']],
                    ]);

                $status = $response->successful() ? 'sent' : 'failed';
                $metadata['provider'] = 'meta';
                $metadata['provider_message_id'] = $response->json('messages.0.id');
                $metadata['error'] = $response->successful() ? null : $response->json();
            } catch (\Illuminate\Http\Client\ConnectionException $exception) {
                $status = 'failed';
                $metadata['provider'] = 'meta';
                $metadata['error'] = $exception->getMessage();
            }
        } else {
            $status = 'failed';
            $metadata['error'] = 'WhatsApp account is not connected.';
        }

        DB::table('messages')->where('id', $message)->update([
            'body' => $data['body'],
            'status' => $status,
            'metadata' => json_encode($metadata),
            'updated_at' => now(),
        ]);

        if ($status === 'failed') {
            return back()->with('error', 'Message edited in CRM, but Meta WhatsApp rejected the updated text.');
        }

        return back()->with('success', 'Message edited and sent to WhatsApp as a new corrected message.');
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
        $workspaceId = $this->workspaceId($request);
        $subscription = DB::table('subscriptions')->where('workspace_id', $workspaceId)->latest()->first();
        $limits = json_decode($subscription->limits ?? '{}', true) ?: [];
        $teamLimit = (int) ($limits['team_members'] ?? 2);
        $currentMembers = DB::table('workspace_user')->where('workspace_id', $workspaceId)->count();

        if ($currentMembers >= $teamLimit) {
            return back()->with('error', "Your current plan allows {$teamLimit} team member(s). Please upgrade your subscription to add more.");
        }

        $existingUser = DB::table('users')->where('email', $data['email'])->first();
        $userId = $existingUser?->id;
        $temporaryPassword = 'password';

        if (! $userId) {
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $userId)->update([
                'name' => $data['name'],
                'updated_at' => now(),
            ]);
        }

        $alreadyMember = DB::table('workspace_user')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'This user is already a member of your workspace.']);
        }

        DB::table('workspace_user')->insert([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'role' => $data['role'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'team.invited', 'Team member invited: '.$data['email'], ['role' => $data['role']]);

        return back()->with('success', 'Team member invited successfully. Temporary password: '.$temporaryPassword);
    }

    public function updateTeamMemberRole(Request $request, int $member): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:owner,manager,agent,viewer'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('workspace_user')
            ->join('users', 'users.id', '=', 'workspace_user.user_id')
            ->where('workspace_user.id', $member)
            ->where('workspace_user.workspace_id', $workspaceId)
            ->select('workspace_user.*', 'users.email')
            ->first();

        abort_unless($record, 404);

        if ((int) $record->user_id === (int) $request->user()->id && $record->role === 'owner' && $data['role'] !== 'owner') {
            return back()->with('error', 'You cannot remove your own owner role.');
        }

        DB::table('workspace_user')->where('id', $member)->where('workspace_id', $workspaceId)->update([
            'role' => $data['role'],
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'team.role_updated', 'Team member role updated: '.$record->email, ['role' => $data['role']]);

        return back()->with('success', 'Team member role updated.');
    }

    public function deleteTeamMember(Request $request, int $member): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('workspace_user')
            ->join('users', 'users.id', '=', 'workspace_user.user_id')
            ->where('workspace_user.id', $member)
            ->where('workspace_user.workspace_id', $workspaceId)
            ->select('workspace_user.*', 'users.email')
            ->first();

        abort_unless($record, 404);

        if ((int) $record->user_id === (int) $request->user()->id) {
            return back()->with('error', 'You cannot remove yourself from the workspace.');
        }

        if ($record->role === 'owner') {
            $ownerCount = DB::table('workspace_user')->where('workspace_id', $workspaceId)->where('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return back()->with('error', 'At least one owner must remain in this workspace.');
            }
        }

        DB::table('workspace_user')->where('id', $member)->where('workspace_id', $workspaceId)->delete();
        DB::table('role_user')->where('workspace_id', $workspaceId)->where('user_id', $record->user_id)->delete();
        DB::table('team_user')->where('user_id', $record->user_id)->whereIn('team_id', function ($query) use ($workspaceId) {
            $query->select('id')->from('teams')->where('workspace_id', $workspaceId);
        })->delete();
        $this->activity($workspaceId, 'team.removed', 'Team member removed: '.$record->email);

        return back()->with('success', 'Team member removed.');
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
            'action_type' => ['required', 'in:send_ai_reply,send_template,update_status,add_note'],
            'reply_template' => ['nullable', 'string', 'max:2000'],
            'update_contact_status' => ['nullable', 'in:new_lead,interested,follow_up,won,lost,blocked'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $flow = [
            'match' => 'contains',
            'actions' => [[
                'type' => $data['action_type'],
                'reply_template' => $data['reply_template'] ?? null,
                'update_contact_status' => $data['update_contact_status'] ?? 'interested',
            ]],
        ];
        $automationId = DB::table('ai_automations')->insertGetId([
            'workspace_id' => $workspaceId,
            'name' => $data['name'],
            'trigger' => $data['trigger'],
            'status' => 'active',
            'flow' => json_encode($flow),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('automation_triggers')->insert([
            'automation_id' => $automationId,
            'type' => 'keyword_contains',
            'config' => json_encode(['keyword' => $data['trigger']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('automation_actions')->insert([
            'automation_id' => $automationId,
            'type' => $data['action_type'],
            'sort_order' => 1,
            'config' => json_encode($flow['actions'][0]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'automation.created', 'Automation created: '.$data['name']);

        return back()->with('success', 'Automation created and activated.');
    }

    public function toggleAutomation(Request $request, int $automation): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('ai_automations')->where('id', $automation)->where('workspace_id', $workspaceId)->first();
        abort_unless($record, 404);

        $status = $record->status === 'active' ? 'paused' : 'active';
        DB::table('ai_automations')->where('id', $automation)->update(['status' => $status, 'updated_at' => now()]);
        $this->activity($workspaceId, 'automation.'.$status, Str::headline($status).' automation: '.$record->name);

        return back()->with('success', 'Automation '.($status === 'active' ? 'activated' : 'paused').'.');
    }

    public function deleteAutomation(Request $request, int $automation): RedirectResponse
    {
        DB::table('ai_automations')->where('id', $automation)->where('workspace_id', $this->workspaceId($request))->delete();

        return back()->with('success', 'Automation deleted.');
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'audience_status' => ['required', 'in:all,new_lead,interested,follow_up,won'],
            'audience_count' => ['required', 'integer', 'min:1', 'max:5000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $contactsQuery = DB::table('contacts')->where('workspace_id', $workspaceId)->where('status', '!=', 'blocked');
        if ($data['audience_status'] !== 'all') {
            $contactsQuery->where('status', $data['audience_status']);
        }
        $audienceCount = min((int) $data['audience_count'], (clone $contactsQuery)->count());
        $broadcastId = DB::table('broadcast_campaigns')->insertGetId([
            'workspace_id' => $workspaceId,
            'name' => $data['name'],
            'body' => $data['body'],
            'audience_filter' => json_encode(['status' => $data['audience_status']]),
            'status' => $data['scheduled_at'] ? 'scheduled' : 'draft',
            'audience_count' => $audienceCount,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'broadcast.created', 'Broadcast campaign created: '.$data['name']);

        if (! $data['scheduled_at']) {
            return $this->sendBroadcast($request, $broadcastId);
        }

        return back()->with('success', 'Broadcast campaign scheduled.');
    }

    public function sendBroadcast(Request $request, int $broadcast): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $campaign = DB::table('broadcast_campaigns')->where('id', $broadcast)->where('workspace_id', $workspaceId)->first();
        abort_unless($campaign, 404);

        $filter = json_decode($campaign->audience_filter ?? '{}', true) ?: [];
        $contacts = DB::table('contacts')
            ->where('workspace_id', $workspaceId)
            ->where('status', '!=', 'blocked')
            ->when(($filter['status'] ?? 'all') !== 'all', fn ($query) => $query->where('status', $filter['status']))
            ->latest()
            ->limit(max(1, (int) $campaign->audience_count))
            ->get();

        if ($contacts->isEmpty()) {
            DB::table('broadcast_campaigns')->where('id', $campaign->id)->update(['status' => 'empty', 'updated_at' => now()]);

            return back()->with('error', 'No matching contacts found for this broadcast audience.');
        }

        DB::table('broadcast_campaigns')->where('id', $campaign->id)->update([
            'status' => 'sending',
            'started_at' => now(),
            'updated_at' => now(),
        ]);

        $sent = 0;
        $failed = 0;
        $accountId = $this->activeWhatsAppAccountId($workspaceId);
        foreach ($contacts as $contact) {
            $body = $this->renderBroadcastBody((string) $campaign->body, $contact);
            $conversationId = $this->findOrCreateConversation($workspaceId, $accountId, (int) $contact->id);
            $result = $this->createAndSendOutboundText($conversationId, $body, true, ['source' => 'broadcast', 'broadcast_id' => $campaign->id]);
            $sent += $result['sent'] ? 1 : 0;
            $failed += $result['sent'] ? 0 : 1;

            DB::table('broadcasts')->insert([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'status' => $result['sent'] ? 'sent' : 'failed',
                'body' => $body,
                'variables' => json_encode(['name' => $contact->name, 'phone_number' => $contact->phone_number, 'error' => $result['error']]),
                'attempts' => 1,
                'sent_at' => $result['sent'] ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('broadcast_campaigns')->where('id', $campaign->id)->update([
            'status' => $failed > 0 && $sent === 0 ? 'failed' : 'sent',
            'audience_count' => $contacts->count(),
            'sent_count' => $sent,
            'delivered_count' => $sent,
            'completed_at' => now(),
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'broadcast.sent', "Broadcast {$campaign->name} sent to {$sent} contacts.");

        return back()->with($sent > 0 ? 'success' : 'error', $sent > 0 ? "Broadcast sent to {$sent} contacts. Failed: {$failed}." : 'Broadcast failed for all contacts. Check WhatsApp account credentials.');
    }

    public function deleteBroadcast(Request $request, int $broadcast): RedirectResponse
    {
        DB::table('broadcast_campaigns')->where('id', $broadcast)->where('workspace_id', $this->workspaceId($request))->delete();

        return back()->with('success', 'Broadcast campaign deleted.');
    }

    public function training(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:document,url,faq'],
            'content' => ['nullable', 'string', 'max:20000'],
            'source_url' => ['nullable', 'url', 'max:500'],
        ]);
        if ($data['type'] === 'url' && empty($data['source_url'])) {
            return back()->withErrors(['source_url' => 'Source URL is required for URL training.']);
        }
        if (in_array($data['type'], ['document', 'faq'], true) && empty($data['content'])) {
            return back()->withErrors(['content' => 'Training content is required.']);
        }
        $workspaceId = $this->workspaceId($request);
        $content = (string) ($data['content'] ?? $data['source_url'] ?? '');
        $chunks = $this->estimateChunks($content);
        DB::table('ai_training_sources')->insert([
            'workspace_id' => $workspaceId,
            'title' => $data['title'],
            'type' => $data['type'],
            'content' => $data['content'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'status' => 'indexed',
            'chunks_count' => $chunks,
            'trained_at' => now(),
            'metadata' => json_encode(['characters' => strlen($content), 'words' => str_word_count(strip_tags($content))]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->refreshAiPrompt($workspaceId);
        $this->activity($workspaceId, 'training.indexed', 'AI training indexed: '.$data['title']);

        return back()->with('success', "Training source indexed with {$chunks} chunks.");
    }

    public function reindexTraining(Request $request, int $source): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        $record = DB::table('ai_training_sources')->where('id', $source)->where('workspace_id', $workspaceId)->first();
        abort_unless($record, 404);

        $content = (string) ($record->content ?? $record->source_url ?? '');
        $chunks = $this->estimateChunks($content);
        DB::table('ai_training_sources')->where('id', $record->id)->update([
            'status' => 'indexed',
            'chunks_count' => $chunks,
            'trained_at' => now(),
            'metadata' => json_encode(['characters' => strlen($content), 'words' => str_word_count(strip_tags($content)), 'reindexed_at' => now()->toIso8601String()]),
            'updated_at' => now(),
        ]);
        $this->refreshAiPrompt($workspaceId);

        return back()->with('success', "Training source reindexed with {$chunks} chunks.");
    }

    public function deleteTraining(Request $request, int $source): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        DB::table('ai_training_sources')->where('id', $source)->where('workspace_id', $workspaceId)->delete();
        $this->refreshAiPrompt($workspaceId);

        return back()->with('success', 'Training source deleted and AI prompt refreshed.');
    }

    public function integration(Request $request): RedirectResponse
    {
        $provider = Str::slug((string) $request->input('provider'), '_');
        $allowed = ['shopify', 'woocommerce', 'zapier', 'stripe', 'telegram', 'slack', 'google_sheets'];
        if (! in_array($provider, $allowed, true)) {
            return back()->withErrors(['provider' => 'Please select a valid integration provider.']);
        }

        $rules = ['provider' => ['required', 'in:'.implode(',', $allowed)]];
        $required = match ($provider) {
            'shopify' => ['shop_domain', 'access_token'],
            'woocommerce' => ['site_url', 'consumer_key', 'consumer_secret'],
            'zapier' => ['webhook_url'],
            'stripe' => ['secret_key'],
            'telegram' => ['bot_token'],
            'slack' => ['bot_token'],
            'google_sheets' => ['spreadsheet_id', 'service_account_email', 'private_key'],
            default => [],
        };
        foreach ($required as $field) {
            $rules[$field] = ['required', 'string', 'max:5000'];
        }
        if ($provider === 'telegram') {
            $rules['chat_id'] = ['nullable', 'string', 'max:255'];
        }

        $attributes = [
            'shop_domain' => 'Shopify shop domain',
            'access_token' => 'Shopify admin access token',
            'site_url' => 'WooCommerce store URL',
            'consumer_key' => 'WooCommerce consumer key',
            'consumer_secret' => 'WooCommerce consumer secret',
            'webhook_url' => 'Zapier webhook URL',
            'secret_key' => 'Stripe secret key',
            'bot_token' => Str::headline($provider).' bot token',
            'chat_id' => 'Telegram chat ID',
            'spreadsheet_id' => 'Google spreadsheet ID',
            'service_account_email' => 'Google service account email',
            'private_key' => 'Google private key',
        ];

        $data = $request->validate($rules, [], $attributes);
        unset($data['provider']);

        $credentials = collect($data)
            ->mapWithKeys(fn ($value, $key) => [$key => Crypt::encryptString((string) $value)])
            ->all();
        $test = $this->testIntegrationConnection($provider, $data);
        $status = $test['ok'] ? 'connected' : 'connection_failed';
        $settings = [
            'label' => Str::headline($provider),
            'last_tested_at' => now()->toIso8601String(),
            'test_message' => $test['message'],
            'credential_keys' => array_keys($data),
        ];

        DB::table('connected_integrations')->updateOrInsert(
            ['workspace_id' => $this->workspaceId($request), 'provider' => $provider],
            [
                'status' => $status,
                'credentials' => json_encode($credentials),
                'settings' => json_encode($settings),
                'last_synced_at' => $test['ok'] ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with($test['ok'] ? 'success' : 'error', $test['message']);
    }

    private function testIntegrationConnection(string $provider, array $credentials): array
    {
        try {
            $http = Http::timeout(15)->connectTimeout(10)->withOptions(['proxy' => '']);
            $response = match ($provider) {
                'shopify' => $http
                    ->withHeaders(['X-Shopify-Access-Token' => $credentials['access_token']])
                    ->get('https://'.preg_replace('#^https?://#', '', rtrim($credentials['shop_domain'], '/')).'/admin/api/2024-10/shop.json'),
                'woocommerce' => $http->get(rtrim($credentials['site_url'], '/').'/wp-json/wc/v3/system_status', [
                    'consumer_key' => $credentials['consumer_key'],
                    'consumer_secret' => $credentials['consumer_secret'],
                ]),
                'zapier' => $http->post($credentials['webhook_url'], [
                    'source' => 'ChatFlow AI',
                    'event' => 'integration_test',
                    'tested_at' => now()->toIso8601String(),
                ]),
                'stripe' => $http
                    ->withBasicAuth($credentials['secret_key'], '')
                    ->get('https://api.stripe.com/v1/account'),
                'telegram' => $http->get('https://api.telegram.org/bot'.$credentials['bot_token'].'/getMe'),
                'slack' => $http
                    ->withToken($credentials['bot_token'])
                    ->asForm()
                    ->post('https://slack.com/api/auth.test'),
                'google_sheets' => null,
                default => null,
            };

            if ($provider === 'google_sheets') {
                $valid = str_contains($credentials['service_account_email'], '@')
                    && str_contains($credentials['private_key'], 'BEGIN PRIVATE KEY')
                    && filled($credentials['spreadsheet_id']);

                return [
                    'ok' => $valid,
                    'message' => $valid
                        ? 'Google Sheets credentials saved. Share the sheet with the service account email to allow access.'
                        : 'Google Sheets credentials are incomplete or invalid.',
                ];
            }

            if (! $response) {
                return ['ok' => false, 'message' => 'Unsupported integration provider.'];
            }

            $ok = $provider === 'slack'
                ? (bool) $response->json('ok')
                : $response->successful();

            return [
                'ok' => $ok,
                'message' => $ok
                    ? Str::headline($provider).' connected and tested successfully.'
                    : Str::headline($provider).' connection failed: '.$this->integrationErrorMessage($response->json() ?: $response->body()),
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => Str::headline($provider).' connection failed: '.$exception->getMessage(),
            ];
        }
    }

    private function integrationErrorMessage(mixed $error): string
    {
        if (is_array($error)) {
            return (string) (
                data_get($error, 'error.message')
                ?? data_get($error, 'error_description')
                ?? data_get($error, 'message')
                ?? data_get($error, 'errors.0.message')
                ?? json_encode($error)
            );
        }

        return Str::limit((string) $error, 220);
    }

    public function apiKey(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $token = 'cf_'.Str::random(48);
        DB::table('api_keys')->insert([
            'workspace_id' => $this->workspaceId($request),
            'name' => $data['name'],
            'token_hash' => hash('sha256', $token),
            'encrypted_token' => Crypt::encryptString($token),
            'token_preview' => substr($token, 0, 8).'...'.substr($token, -6),
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

    public function markAllNotificationsRead(Request $request): RedirectResponse
    {
        $workspaceId = $this->workspaceId($request);
        DB::table('conversations')->where('workspace_id', $workspaceId)->update([
            'unread_count' => 0,
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'notifications.read', 'All notifications marked as read.');

        return back()->with('success', 'All notifications marked as read.');
    }

    public function notificationSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inbox_alerts' => ['nullable', 'boolean'],
            'subscription_alerts' => ['nullable', 'boolean'],
            'activity_digest' => ['nullable', 'boolean'],
            'email_alerts' => ['nullable', 'boolean'],
            'quiet_hours' => ['required', 'in:off,night,weekend'],
        ]);
        $workspaceId = $this->workspaceId($request);
        $settings = [
            'inbox_alerts' => (bool) ($data['inbox_alerts'] ?? false),
            'subscription_alerts' => (bool) ($data['subscription_alerts'] ?? false),
            'activity_digest' => (bool) ($data['activity_digest'] ?? false),
            'email_alerts' => (bool) ($data['email_alerts'] ?? false),
            'quiet_hours' => $data['quiet_hours'],
        ];

        DB::table('workspaces')->where('id', $workspaceId)->update([
            'notification_settings' => json_encode($settings),
            'updated_at' => now(),
        ]);
        $this->activity($workspaceId, 'notifications.settings', 'Notification preferences updated.', $settings);

        return back()->with('success', 'Notification preferences saved.');
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

    private function findOrCreateConversation(int $workspaceId, ?int $accountId, int $contactId): int
    {
        $conversationId = DB::table('conversations')
            ->where('workspace_id', $workspaceId)
            ->where('contact_id', $contactId)
            ->value('id');

        if ($conversationId) {
            return (int) $conversationId;
        }

        $accountId ??= (int) DB::table('whatsapp_accounts')->where('workspace_id', $workspaceId)->latest()->value('id');
        if (! $accountId) {
            $accountId = DB::table('whatsapp_accounts')->insertGetId([
                'workspace_id' => $workspaceId,
                'name' => 'Main Business',
                'phone_number' => '+92 300 0000000',
                'provider' => 'meta',
                'status' => 'pending_setup',
                'quality_rating' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return DB::table('conversations')->insertGetId([
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
    }

    private function createAndSendOutboundText(int $conversationId, string $body, bool $aiGenerated = false, array $extraMetadata = []): array
    {
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'direction' => 'outbound',
            'sender_type' => $aiGenerated ? 'ai' : 'agent',
            'body' => $body,
            'message_type' => 'text',
            'status' => 'sent',
            'ai_generated' => $aiGenerated,
            'metadata' => json_encode($extraMetadata),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = DB::table('conversations')
            ->join('contacts', 'contacts.id', '=', 'conversations.contact_id')
            ->join('whatsapp_accounts', 'whatsapp_accounts.id', '=', 'conversations.whatsapp_account_id')
            ->where('conversations.id', $conversationId)
            ->select('contacts.phone_number as contact_phone', 'contacts.status as contact_status', 'conversations.status as conversation_status', 'whatsapp_accounts.settings as account_settings')
            ->first();

        if (($record?->contact_status === 'blocked') || ($record?->conversation_status === 'blocked')) {
            DB::table('messages')->where('id', $messageId)->update([
                'status' => 'failed',
                'metadata' => json_encode($extraMetadata + ['error' => 'Contact is blocked.']),
                'updated_at' => now(),
            ]);

            return ['sent' => false, 'message_id' => $messageId, 'error' => 'Contact is blocked.'];
        }

        $settings = json_decode($record->account_settings ?? '{}', true) ?: [];
        $token = $settings['access_token'] ?? null;
        $phoneNumberId = $settings['phone_number_id'] ?? null;

        if (! $token || ! $phoneNumberId) {
            DB::table('messages')->where('id', $messageId)->update([
                'status' => 'failed',
                'metadata' => json_encode($extraMetadata + ['provider' => 'meta', 'error' => 'WhatsApp account is not connected.']),
                'updated_at' => now(),
            ]);
            DB::table('conversations')->where('id', $conversationId)->update(['last_message_at' => now(), 'updated_at' => now()]);

            return ['sent' => false, 'message_id' => $messageId, 'error' => 'WhatsApp account is not connected.'];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->connectTimeout(10)
                ->withOptions(['proxy' => ''])
                ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => preg_replace('/\D+/', '', $this->normalizePhoneNumber($record->contact_phone)),
                    'type' => 'text',
                    'text' => ['body' => $body],
                ]);

            DB::table('messages')->where('id', $messageId)->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'metadata' => json_encode($extraMetadata + [
                    'provider' => 'meta',
                    'provider_message_id' => $response->json('messages.0.id'),
                    'error' => $response->successful() ? null : $response->json(),
                ]),
                'updated_at' => now(),
            ]);
            DB::table('conversations')->where('id', $conversationId)->update(['last_message_at' => now(), 'updated_at' => now()]);

            return ['sent' => $response->successful(), 'message_id' => $messageId, 'error' => $response->successful() ? null : json_encode($response->json())];
        } catch (\Illuminate\Http\Client\ConnectionException $exception) {
            DB::table('messages')->where('id', $messageId)->update([
                'status' => 'failed',
                'metadata' => json_encode($extraMetadata + ['provider' => 'meta', 'error' => $exception->getMessage()]),
                'updated_at' => now(),
            ]);
            DB::table('conversations')->where('id', $conversationId)->update(['last_message_at' => now(), 'updated_at' => now()]);

            return ['sent' => false, 'message_id' => $messageId, 'error' => $exception->getMessage()];
        }
    }

    private function renderBroadcastBody(string $body, object $contact): string
    {
        return str_replace(
            ['{{name}}', '{{phone}}', '{{status}}'],
            [(string) $contact->name, (string) $contact->phone_number, Str::headline((string) $contact->status)],
            $body
        );
    }

    private function estimateChunks(string $content): int
    {
        $characters = max(1, strlen(trim(strip_tags($content))));

        return max(1, (int) ceil($characters / 900));
    }

    private function refreshAiPrompt(int $workspaceId): void
    {
        $sources = DB::table('ai_training_sources')
            ->where('workspace_id', $workspaceId)
            ->where('status', 'indexed')
            ->latest('trained_at')
            ->limit(12)
            ->get()
            ->map(fn ($source) => "Source: {$source->title}\n".Str::limit((string) ($source->content ?: $source->source_url), 1200))
            ->implode("\n\n");

        DB::table('ai_prompts')->updateOrInsert(
            ['workspace_id' => $workspaceId, 'type' => 'reply'],
            [
                'name' => 'Workspace AI Knowledge',
                'tone' => 'friendly',
                'system_prompt' => trim("You are ChatFlow AI. Reply concisely using this business knowledge when relevant.\n\n".$sources),
                'is_active' => true,
                'settings' => json_encode(['generated_from_training' => true, 'refreshed_at' => now()->toIso8601String()]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function activity(int $workspaceId, string $type, string $description, array $properties = []): void
    {
        DB::table('activity_logs')->insert([
            'workspace_id' => $workspaceId,
            'type' => $type,
            'description' => $description,
            'properties' => json_encode($properties),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function normalizePhoneNumber(string $phone, string $countryCode = '+92'): string
    {
        return PhoneNumber::normalize($phone, $countryCode);
    }
}
