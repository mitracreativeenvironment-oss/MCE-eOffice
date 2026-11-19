<?php

namespace App\Controllers\Api\V1;

use App\Models\AgentModel;
use App\Models\CharacterModel;
use App\Models\ConversationModel;
use App\Models\DeskModel;
use App\Models\DivisionModel;
use App\Models\MessageModel;
use App\Models\RoomModel;
use App\Models\TaskModel;
use App\Services\RouterService;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseApiController
{
    protected AgentModel $agentModel;
    protected DivisionModel $divisionModel;
    protected RoomModel $roomModel;
    protected DeskModel $deskModel;
    protected CharacterModel $characterModel;
    protected TaskModel $taskModel;
    protected ConversationModel $conversationModel;
    protected MessageModel $messageModel;
    protected RouterService $routerService;

    public function __construct()
    {
        $this->agentModel        = new AgentModel();
        $this->divisionModel     = new DivisionModel();
        $this->roomModel         = new RoomModel();
        $this->deskModel         = new DeskModel();
        $this->characterModel    = new CharacterModel();
        $this->taskModel         = new TaskModel();
        $this->conversationModel = new ConversationModel();
        $this->messageModel      = new MessageModel();
        $this->routerService     = new RouterService();
    }

    /**
     * GET /api/v1/admin/overview
     * Consolidated campus telemetry, counts, and gateway status
     */
    public function overview(): ResponseInterface
    {
        $totalAgents    = $this->agentModel->countAllResults();
        $activeAgents   = $this->agentModel->where('is_active', 1)->countAllResults();
        $inactiveAgents = $totalAgents - $activeAgents;

        $totalDivisions = $this->divisionModel->countAllResults();
        $totalDesks     = $this->deskModel->countAllResults();
        $totalRooms     = $this->roomModel->countAllResults();

        // Tasks breakdown
        $totalTasks      = $this->taskModel->countAllResults();
        $pendingTasks    = $this->taskModel->where('status', 'pending')->countAllResults();
        $inProgressTasks = $this->taskModel->where('status', 'in_progress')->countAllResults();
        $completedTasks  = $this->taskModel->where('status', 'completed')->countAllResults();
        $approvedTasks   = $this->taskModel->where('status', 'approved')->countAllResults();
        $rejectedTasks   = $this->taskModel->where('status', 'rejected')->countAllResults();

        // Conversations & Token telemetry
        $totalConversations = $this->conversationModel->countAllResults();
        $totalMessages      = $this->messageModel->countAllResults();

        $tokenStats = $this->messageModel->selectSum('tokens_prompt', 'sum_prompt')
            ->selectSum('tokens_completion', 'sum_completion')
            ->first();

        $sumPrompt     = (int) ($tokenStats['sum_prompt'] ?? 0);
        $sumCompletion = (int) ($tokenStats['sum_completion'] ?? 0);
        $totalTokens   = $sumPrompt + $sumCompletion;

        // Divisions with agent count and building coordinates
        $divisions = $this->divisionModel->orderBy('floor_level', 'ASC')->findAll();
        $divisionData = [];
        foreach ($divisions as $div) {
            $agentCount = $this->agentModel->where('division_id', $div['id'])->countAllResults();
            $room = $this->roomModel->where('division_id', $div['id'])->first();

            $divisionData[] = [
                'id'          => $div['id'],
                'label'       => $div['label'],
                'icon_name'   => $div['icon_name'],
                'color_hex'   => $div['color_hex'],
                'floor_level' => (int) $div['floor_level'],
                'description' => $div['description'],
                'agent_count' => $agentCount,
                'building'    => $room ? [
                    'id'              => $room['id'],
                    'name'            => $room['name'],
                    'position_x'      => (float) $room['position_x'],
                    'position_y'      => (float) $room['position_y'],
                    'position_z'      => (float) $room['position_z'],
                    'camera_target_x' => (float) $room['camera_target_x'],
                    'camera_target_y' => (float) $room['camera_target_y'],
                    'camera_target_z' => (float) $room['camera_target_z'],
                ] : null,
            ];
        }

        // Recent tasks
        $recentTasks = $this->taskModel->select([
            'agent_tasks.*',
            'agent_profiles.name as agent_name',
            'agent_profiles.emoji as agent_emoji',
            'agent_profiles.color as agent_color',
            'divisions.label as division_label',
        ])
        ->join('agent_profiles', 'agent_profiles.id = agent_tasks.lead_agent_id', 'left')
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left')
        ->orderBy('agent_tasks.created_at', 'DESC')
        ->limit(6)
        ->findAll();

        // Gateway health check
        $gatewayStatus = $this->routerService->checkHealth();

        return $this->success([
            'telemetry' => [
                'total_agents'        => $totalAgents,
                'active_agents'       => $activeAgents,
                'inactive_agents'     => $inactiveAgents,
                'total_divisions'     => $totalDivisions,
                'total_desks'         => $totalDesks,
                'total_rooms'         => $totalRooms,
                'total_conversations' => $totalConversations,
                'total_messages'      => $totalMessages,
                'total_tokens'        => $totalTokens,
                'tokens_prompt'       => $sumPrompt,
                'tokens_completion'   => $sumCompletion,
            ],
            'tasks_breakdown' => [
                'total'       => $totalTasks,
                'pending'     => $pendingTasks,
                'in_progress' => $inProgressTasks,
                'completed'   => $completedTasks,
                'approved'    => $approvedTasks,
                'rejected'    => $rejectedTasks,
            ],
            'divisions'       => $divisionData,
            'recent_tasks'    => $recentTasks,
            'router_gateway'  => $gatewayStatus,
        ], 'Admin overview statistics loaded.');
    }

    /**
     * GET /api/v1/admin/agents
     * Full catalog of 283 agents with rich search, filter, and desk positioning
     */
    public function agents(): ResponseInterface
    {
        $division = $this->request->getGet('division');
        $search   = $this->request->getGet('search') ?? $this->request->getGet('q');
        $tier     = $this->request->getGet('tier');
        $status   = $this->request->getGet('status');

        $builder = $this->agentModel->select([
            'agent_profiles.*',
            'divisions.label as division_label',
            'divisions.color_hex as division_color',
            'divisions.floor_level',
            'characters.id as character_id',
            'characters.current_status as character_status',
            'office_desks.desk_code',
            'office_desks.position_x as desk_x',
            'office_desks.position_y as desk_y',
            'office_desks.position_z as desk_z',
        ])
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left')
        ->join('characters', 'characters.assigned_agent_id = agent_profiles.id', 'left')
        ->join('office_desks', 'office_desks.id = characters.assigned_desk_id', 'left');

        if ($division) {
            $builder->where('agent_profiles.division_id', $division);
        }

        if ($tier) {
            $builder->where('agent_profiles.preferred_model_tier', $tier);
        }

        if ($status !== null && $status !== '') {
            $builder->where('agent_profiles.is_active', (int) $status);
        }

        if ($search) {
            $builder->groupStart()
                ->like('agent_profiles.name', $search)
                ->orLike('agent_profiles.slug', $search)
                ->orLike('agent_profiles.description', $search)
                ->orLike('agent_profiles.vibe', $search)
            ->groupEnd();
        }

        $agents = $builder->orderBy('divisions.floor_level', 'ASC')
            ->orderBy('agent_profiles.name', 'ASC')
            ->findAll();

        return $this->success($agents, 'Admin agents list retrieved.');
    }

    /**
     * PUT /api/v1/admin/agents/(:segment)
     * Update agent persona details, preferred model tier, active status, system prompt
     */
    public function updateAgent($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->notFound('Agent ID required.');
        }

        $agent = $this->agentModel->where('id', $id)->orWhere('slug', $id)->first();
        if (!$agent) {
            return $this->notFound('Agent not found.');
        }

        $body = $this->getBody();

        $updateData = [];
        if (isset($body['name'])) {
            $updateData['name'] = trim((string) $body['name']);
        }
        if (isset($body['description'])) {
            $updateData['description'] = trim((string) $body['description']);
        }
        if (isset($body['color'])) {
            $updateData['color'] = trim((string) $body['color']);
        }
        if (isset($body['emoji'])) {
            $updateData['emoji'] = trim((string) $body['emoji']);
        }
        if (isset($body['vibe'])) {
            $updateData['vibe'] = trim((string) $body['vibe']);
        }
        if (isset($body['preferred_model_tier'])) {
            $updateData['preferred_model_tier'] = trim((string) $body['preferred_model_tier']);
        }
        if (isset($body['is_active'])) {
            $updateData['is_active'] = (int) $body['is_active'];
        }
        if (isset($body['system_prompt'])) {
            $updateData['system_prompt'] = (string) $body['system_prompt'];
        }
        if (isset($body['division_id'])) {
            $updateData['division_id'] = (string) $body['division_id'];
        }

        if (empty($updateData)) {
            return $this->validationError(['no_changes' => 'No valid fields provided for update.']);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');
        $this->agentModel->update($agent['id'], $updateData);

        $updatedAgent = $this->agentModel->find($agent['id']);
        return $this->success($updatedAgent, 'Agent updated successfully.');
    }

    /**
     * PUT /api/v1/admin/divisions/(:segment)
     * Update division branding and 3D campus coordinates
     */
    public function updateDivision($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->notFound('Division ID required.');
        }

        $division = $this->divisionModel->find($id);
        if (!$division) {
            return $this->notFound('Division not found.');
        }

        $body = $this->getBody();

        $divUpdate = [];
        if (isset($body['label'])) {
            $divUpdate['label'] = trim((string) $body['label']);
        }
        if (isset($body['icon_name'])) {
            $divUpdate['icon_name'] = trim((string) $body['icon_name']);
        }
        if (isset($body['color_hex'])) {
            $divUpdate['color_hex'] = trim((string) $body['color_hex']);
        }
        if (isset($body['floor_level'])) {
            $divUpdate['floor_level'] = (int) $body['floor_level'];
        }
        if (isset($body['description'])) {
            $divUpdate['description'] = trim((string) $body['description']);
        }

        if (!empty($divUpdate)) {
            $divUpdate['updated_at'] = date('Y-m-d H:i:s');
            $this->divisionModel->update($id, $divUpdate);
        }

        // Update 3D building coordinates if provided
        if (isset($body['position_x']) || isset($body['position_y']) || isset($body['position_z'])) {
            $room = $this->roomModel->where('division_id', $id)->first();
            if ($room) {
                $roomUpdate = [];
                if (isset($body['position_x'])) {
                    $roomUpdate['position_x'] = (float) $body['position_x'];
                }
                if (isset($body['position_y'])) {
                    $roomUpdate['position_y'] = (float) $body['position_y'];
                }
                if (isset($body['position_z'])) {
                    $roomUpdate['position_z'] = (float) $body['position_z'];
                }
                $roomUpdate['updated_at'] = date('Y-m-d H:i:s');
                $this->roomModel->update($room['id'], $roomUpdate);
            }
        }

        $updatedDiv = $this->divisionModel->find($id);
        $updatedRoom = $this->roomModel->where('division_id', $id)->first();
        $updatedDiv['building'] = $updatedRoom;

        return $this->success($updatedDiv, 'Division updated successfully.');
    }

    /**
     * GET /api/v1/admin/conversations
     * Audit log of all chat sessions across all agents
     */
    public function conversations(): ResponseInterface
    {
        $search = $this->request->getGet('search') ?? $this->request->getGet('q');
        $limit  = (int) ($this->request->getGet('limit') ?? 50);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        $builder = $this->conversationModel->select([
            'conversations.*',
            'agent_profiles.name as agent_name',
            'agent_profiles.emoji as agent_emoji',
            'agent_profiles.color as agent_color',
            'agent_profiles.preferred_model_tier',
            'divisions.label as division_label',
            'divisions.color_hex as division_color',
            '(SELECT COUNT(*) FROM conversation_messages WHERE conversation_messages.conversation_id = conversations.id) as message_count',
            '(SELECT SUM(tokens_prompt + tokens_completion) FROM conversation_messages WHERE conversation_messages.conversation_id = conversations.id) as total_tokens',
            '(SELECT content FROM conversation_messages WHERE conversation_messages.conversation_id = conversations.id ORDER BY id DESC LIMIT 1) as last_message_content',
        ])
        ->join('agent_profiles', 'agent_profiles.id = conversations.agent_id', 'left')
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('conversations.title', $search)
                ->orLike('agent_profiles.name', $search)
                ->orLike('divisions.label', $search)
            ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $conversations = $builder->orderBy('conversations.updated_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();

        return $this->success([
            'total'         => $total,
            'conversations' => $conversations,
        ], 'Conversations retrieved.');
    }

    /**
     * GET /api/v1/admin/conversations/(:segment)/messages
     * Read complete message thread with token usage and model metadata
     */
    public function conversationMessages($conversationId = null): ResponseInterface
    {
        if (!$conversationId) {
            return $this->notFound('Conversation ID required.');
        }

        $conversation = $this->conversationModel->select([
            'conversations.*',
            'agent_profiles.name as agent_name',
            'agent_profiles.emoji as agent_emoji',
            'agent_profiles.color as agent_color',
            'agent_profiles.preferred_model_tier',
            'divisions.label as division_label',
        ])
        ->join('agent_profiles', 'agent_profiles.id = conversations.agent_id', 'left')
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left')
        ->where('conversations.id', $conversationId)
        ->first();

        if (!$conversation) {
            return $this->notFound('Conversation not found.');
        }

        $messages = $this->messageModel
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->success([
            'conversation' => $conversation,
            'messages'     => $messages,
        ], 'Messages retrieved.');
    }

    /**
     * POST /api/v1/admin/test-inference
     * Send test prompt to 9Router Gateway and measure live latency and tokens
     */
    public function testInference(): ResponseInterface
    {
        $body = $this->getBody();
        $prompt = trim((string) ($body['prompt'] ?? ''));
        $model  = trim((string) ($body['model'] ?? 'deepseek-chat'));

        if ($prompt === '') {
            return $this->validationError(['prompt' => 'Prompt text is required.']);
        }

        $startTime = microtime(true);

        try {
            $result = $this->routerService->chat([
                ['role' => 'user', 'content' => $prompt],
            ], $model, [
                'temperature' => (float) ($body['temperature'] ?? 0.7),
            ]);

            $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

            return $this->success([
                'success'      => true,
                'model'        => $result['model'],
                'content'      => $result['content'],
                'usage'        => $result['usage'],
                'latency_ms'   => $elapsedMs,
                'gateway_url'  => env('router.baseUrl', 'http://127.0.0.1:20128/v1'),
            ], 'Inference test completed.');
        } catch (\Throwable $e) {
            $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);
            return $this->success([
                'success'      => false,
                'error'        => $e->getMessage(),
                'latency_ms'   => $elapsedMs,
                'gateway_url'  => env('router.baseUrl', 'http://127.0.0.1:20128/v1'),
            ], 'Inference test failed.');
        }
    }
}

