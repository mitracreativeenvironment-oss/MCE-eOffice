<?php

namespace App\Controllers\Api\V1;

use App\Models\AgentModel;
use App\Models\CharacterModel;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Services\RouterService;
use CodeIgniter\HTTP\ResponseInterface;

class ConversationsController extends BaseApiController
{
    protected ConversationModel $conversationModel;
    protected MessageModel $messageModel;
    protected CharacterModel $characterModel;
    protected AgentModel $agentModel;
    protected RouterService $routerService;

    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->messageModel      = new MessageModel();
        $this->characterModel    = new CharacterModel();
        $this->agentModel        = new AgentModel();
        $this->routerService     = new RouterService();
    }

    /**
     * POST /api/v1/characters/:id/interact
     * Initiate or resume chat session with an avatar in the 3D office
     */
    public function interact($characterId = null): ResponseInterface
    {
        if (!$characterId) {
            return $this->notFound('Character ID is required.');
        }

        $character = $this->characterModel->find($characterId);
        if (!$character) {
            return $this->notFound('Character not found in 3D scene.');
        }

        $agent = $this->agentModel->find($character['assigned_agent_id']);
        if (!$agent) {
            return $this->notFound('No agent persona assigned to this character.');
        }

        // Find or create conversation
        $conversation = $this->conversationModel
            ->where('character_id', $characterId)
            ->where('agent_id', $agent['id'])
            ->orderBy('created_at', 'DESC')
            ->first();

        $now = date('Y-m-d H:i:s');
        if (!$conversation) {
            $conversationId = $this->generateUuid();
            $this->conversationModel->insert([
                'id'           => $conversationId,
                'agent_id'     => $agent['id'],
                'character_id' => $characterId,
                'title'        => "Session with {$agent['name']}",
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $conversation = $this->conversationModel->find($conversationId);

            // Add default greeting
            $greeting = "Halo! Saya **{$agent['name']}** ({$agent['emoji']}). {$agent['vibe']}. Ada yang bisa saya bantu di divisi ini?";
            $this->messageModel->insert([
                'conversation_id' => $conversationId,
                'sender_type'     => 'agent',
                'content'         => $greeting,
                'model_used'      => 'system_init',
                'created_at'      => $now,
            ]);
        }

        // Get recent messages
        $messages = $this->messageModel
            ->where('conversation_id', $conversation['id'])
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->success([
            'conversation' => $conversation,
            'agent'        => $agent,
            'character'    => $character,
            'messages'     => $messages,
        ], 'Chat session opened.');
    }

    /**
     * GET /api/v1/conversations/:id/messages
     */
    public function messages($conversationId = null): ResponseInterface
    {
        if (!$conversationId) {
            return $this->notFound('Conversation ID required.');
        }

        $messages = $this->messageModel
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->success($messages, 'Messages retrieved.');
    }

    /**
     * POST /api/v1/conversations/:id/send
     * Send user prompt and receive AI response via 9Router
     */
    public function send($conversationId = null): ResponseInterface
    {
        if (!$conversationId) {
            return $this->notFound('Conversation ID required.');
        }

        $conversation = $this->conversationModel->find($conversationId);
        if (!$conversation) {
            return $this->notFound('Conversation not found.');
        }

        $agent = $this->agentModel->find($conversation['agent_id']);
        if (!$agent) {
            return $this->notFound('Assigned agent profile not found.');
        }

        $body = $this->getBody();
        $userText = trim($body['message'] ?? '');
        if ($userText === '') {
            return $this->validationError(['message' => 'Message content cannot be empty.']);
        }

        $now = date('Y-m-d H:i:s');

        // 1. Record User Message
        $this->messageModel->insert([
            'conversation_id' => $conversationId,
            'sender_type'     => 'user',
            'content'         => $userText,
            'created_at'      => $now,
        ]);

        // 2. Update character status to 'thinking'
        if ($conversation['character_id']) {
            $this->characterModel->update($conversation['character_id'], [
                'current_status'     => 'thinking',
                'last_status_update' => $now,
            ]);
        }

        // 3. Prepare Prompt & Context
        $systemPrompt = $agent['system_prompt'] . "\n\n" .
            "IMPORTANT OPERATIONAL GUIDELINES:\n" .
            "- Respond as {$agent['name']} faithfully to your personality and deliverables.\n" .
            "- Maintain professional context within the MCE Virtual Office environment.\n" .
            "- Format code or structured deliverables clearly using GitHub Flavored Markdown.";

        $messagesPayload = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Fetch recent context (last 10 messages)
        $history = $this->messageModel
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'DESC')
            ->findAll(10);
        $history = array_reverse($history);

        foreach ($history as $m) {
            $role = ($m['sender_type'] === 'user') ? 'user' : 'assistant';
            $messagesPayload[] = [
                'role'    => $role,
                'content' => $m['content'],
            ];
        }

        // 4. Resolve Model & Call 9Router
        $targetModel = $this->routerService->resolveModel($agent['preferred_model_tier'] ?? 'coding');

        try {
            $aiResponse = $this->routerService->chat($messagesPayload, $targetModel);
            $replyContent = $aiResponse['content'];
            $usedModel    = $aiResponse['model'];
            $usage        = $aiResponse['usage'];
        } catch (\Exception $e) {
            // Graceful fallback if 9Router local daemon is offline
            $replyContent = "[System Notice: 9Router Gateway currently offline ({$e->getMessage()}). Responding in offline simulation mode.]\n\n" .
                "Terima kasih atas pesan Anda: \"{$userText}\". Saya ({$agent['name']}) telah mencatat kebutuhan Anda. " .
                "Pastikan 9Router service berjalan di `http://127.0.0.1:20128` untuk inferensi AI langsung.";
            $usedModel    = 'offline-fallback';
            $usage        = ['prompt_tokens' => 0, 'completion_tokens' => 0];
        }

        // 5. Record Agent Reply
        $agentMsgTime = date('Y-m-d H:i:s');
        $msgId = $this->messageModel->insert([
            'conversation_id'   => $conversationId,
            'sender_type'       => 'agent',
            'content'           => $replyContent,
            'model_used'        => $usedModel,
            'tokens_prompt'     => $usage['prompt_tokens'] ?? 0,
            'tokens_completion' => $usage['completion_tokens'] ?? 0,
            'created_at'        => $agentMsgTime,
        ]);

        // 6. Reset character status to 'idle'
        if ($conversation['character_id']) {
            $this->characterModel->update($conversation['character_id'], [
                'current_status'     => 'idle',
                'last_status_update' => $agentMsgTime,
            ]);
        }

        return $this->success([
            'message_id' => $msgId,
            'reply'      => $replyContent,
            'model'      => $usedModel,
            'usage'      => $usage,
            'created_at' => $agentMsgTime,
        ], 'Reply generated.');
    }

    /**
     * GET /api/v1/conversations/:id/stream
     * Server-Sent Events (SSE) streaming endpoint
     */
    public function stream($conversationId = null)
    {
        if (!$conversationId) {
            return $this->notFound('Conversation ID required.');
        }

        $conversation = $this->conversationModel->find($conversationId);
        if (!$conversation) {
            return $this->notFound('Conversation not found.');
        }

        $agent = $this->agentModel->find($conversation['agent_id']);
        if (!$agent) {
            return $this->notFound('Agent not found.');
        }

        $userPrompt = $this->request->getGet('prompt');
        if (!$userPrompt) {
            return $this->error('Prompt query param required.');
        }

        // Configure SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        header('Access-Control-Allow-Origin: *');

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $now = date('Y-m-d H:i:s');
        $this->messageModel->insert([
            'conversation_id' => $conversationId,
            'sender_type'     => 'user',
            'content'         => $userPrompt,
            'created_at'      => $now,
        ]);

        $systemPrompt = $agent['system_prompt'] . "\n\n" .
            "IMPORTANT: Respond as {$agent['name']}. Provide structured analysis.";

        $messagesPayload = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $targetModel = $this->routerService->resolveModel($agent['preferred_model_tier'] ?? 'coding');

        $fullReply = '';
        try {
            $fullReply = $this->routerService->streamChat(
                $messagesPayload,
                $targetModel,
                function ($delta, $isDone) {
                    if ($isDone) {
                        echo "data: [DONE]\n\n";
                    } else {
                        echo "data: " . json_encode(['delta' => $delta]) . "\n\n";
                    }
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                }
            );
        } catch (\Exception $e) {
            echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
            echo "data: [DONE]\n\n";
            flush();
        }

        // Save completed streamed reply
        if (!empty($fullReply)) {
            $this->messageModel->insert([
                'conversation_id' => $conversationId,
                'sender_type'     => 'agent',
                'content'         => $fullReply,
                'model_used'      => $targetModel,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        exit;
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

