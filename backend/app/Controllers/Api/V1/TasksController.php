<?php

namespace App\Controllers\Api\V1;

use App\Models\AgentModel;
use App\Models\TaskModel;
use App\Services\RouterService;
use CodeIgniter\HTTP\ResponseInterface;

class TasksController extends BaseApiController
{
    protected TaskModel $taskModel;
    protected AgentModel $agentModel;
    protected RouterService $routerService;

    public function __construct()
    {
        $this->taskModel     = new TaskModel();
        $this->agentModel    = new AgentModel();
        $this->routerService = new RouterService();
    }

    /**
     * GET /api/v1/tasks
     */
    public function index(): ResponseInterface
    {
        $tasks = $this->taskModel->getWithAgent();
        return $this->success($tasks, 'Tasks retrieved.');
    }

    /**
     * POST /api/v1/tasks
     * Delegate a formal task to an agent
     */
    public function create(): ResponseInterface
    {
        $body = $this->getBody();

        $rules = [
            'lead_agent_id' => 'required',
            'title'         => 'required|min_length[3]|max_length[255]',
            'brief_input'   => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        $agent = $this->agentModel->find($body['lead_agent_id']);
        if (!$agent) {
            return $this->notFound('Agent not found.');
        }

        $now = date('Y-m-d H:i:s');
        $taskId = $this->generateUuid();

        // Optional: trigger initial deliverable generation via 9Router
        $systemPrompt = $agent['system_prompt'] . "\n\n" .
            "TASK ASSIGNMENT INSTRUCTIONS:\n" .
            "You have been assigned the following task titled: \"{$body['title']}\".\n" .
            "Generate your formal technical deliverable per your specified deliverables specification.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "BRIEF:\n" . $body['brief_input']],
        ];

        $targetModel = $this->routerService->resolveModel($agent['preferred_model_tier'] ?? 'coding');
        $deliverable = null;
        $status = 'in_progress';

        try {
            $response = $this->routerService->chat($messages, $targetModel);
            $deliverable = $response['content'];
            $status = 'review_ready';
        } catch (\Exception $e) {
            $deliverable = "[Deliverable Pending: 9Router Gateway currently offline ({$e->getMessage()}). Agent will process task once gateway connects.]";
        }

        $this->taskModel->insert([
            'id'                => $taskId,
            'user_id'           => $body['user_id'] ?? 'guest-user',
            'lead_agent_id'     => $agent['id'],
            'title'             => $body['title'],
            'status'            => $status,
            'brief_input'       => $body['brief_input'],
            'final_deliverable' => $deliverable,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $task = $this->taskModel->getWithAgent($taskId);
        return $this->created($task, 'Task successfully created.');
    }

    /**
     * GET /api/v1/tasks/:id
     */
    public function show($id = null): ResponseInterface
    {
        if (!$id) return $this->notFound('Task ID required.');

        $task = $this->taskModel->getWithAgent($id);
        if (!$task) return $this->notFound('Task not found.');

        return $this->success($task, 'Task details retrieved.');
    }

    /**
     * POST /api/v1/tasks/:id/approve
     */
    public function approve($id = null): ResponseInterface
    {
        if (!$id) return $this->notFound('Task ID required.');

        $task = $this->taskModel->find($id);
        if (!$task) return $this->notFound('Task not found.');

        $this->taskModel->update($id, [
            'status'     => 'approved',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->success($this->taskModel->getWithAgent($id), 'Task deliverable approved.');
    }

    /**
     * POST /api/v1/tasks/:id/reject
     */
    public function reject($id = null): ResponseInterface
    {
        if (!$id) return $this->notFound('Task ID required.');

        $task = $this->taskModel->find($id);
        if (!$task) return $this->notFound('Task not found.');

        $body = $this->getBody();
        $feedback = $body['feedback'] ?? 'Please revise deliverable.';

        $this->taskModel->update($id, [
            'status'          => 'rejected',
            'review_feedback' => $feedback,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->success($this->taskModel->getWithAgent($id), 'Task returned for revisions.');
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
