<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function summary(int $workspaceId): array
    {
        return Cache::remember("analytics-summary:{$workspaceId}", now()->addMinutes(5), function () use ($workspaceId) {
            return [
                'total_messages' => DB::table('messages')
                    ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
                    ->where('conversations.workspace_id', $workspaceId)
                    ->count(),
                'contacts' => DB::table('contacts')->where('workspace_id', $workspaceId)->count(),
                'open_conversations' => DB::table('conversations')->where('workspace_id', $workspaceId)->where('status', 'open')->count(),
                'ai_replies' => DB::table('messages')
                    ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
                    ->where('conversations.workspace_id', $workspaceId)
                    ->where('messages.ai_generated', true)
                    ->count(),
            ];
        });
    }
}
