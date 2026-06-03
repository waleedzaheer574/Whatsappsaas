<?php

namespace App\Services\Automation;

use App\Models\AiAutomation;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class WorkflowEngine
{
    public function runForMessage(Conversation $conversation, string $message): void
    {
        $automations = AiAutomation::query()
            ->where('workspace_id', $conversation->workspace_id)
            ->where('status', 'active')
            ->get();

        foreach ($automations as $automation) {
            $matched = str_contains(strtolower($message), strtolower($automation->trigger));

            DB::table('automation_logs')->insert([
                'automation_id' => $automation->id,
                'conversation_id' => $conversation->id,
                'status' => $matched ? 'matched' : 'skipped',
                'context' => json_encode(['message' => $message]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
