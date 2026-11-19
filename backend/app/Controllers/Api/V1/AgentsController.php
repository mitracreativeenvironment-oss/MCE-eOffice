<?php

namespace App\Controllers\Api\V1;

use App\Models\AgentModel;
use App\Models\DivisionModel;
use CodeIgniter\HTTP\ResponseInterface;

class AgentsController extends BaseApiController
{
    protected AgentModel $agentModel;
    protected DivisionModel $divisionModel;

    public function __construct()
    {
        $this->agentModel    = new AgentModel();
        $this->divisionModel = new DivisionModel();
    }

    /**
     * GET /api/v1/agents
     * Query agents with optional ?division=, ?search=, ?floor=
     */
    public function index(): ResponseInterface
    {
        $division = $this->request->getGet('division');
        $search   = $this->request->getGet('search') ?? $this->request->getGet('q');
        $floor    = $this->request->getGet('floor');

        $builder = $this->agentModel->select([
            'agent_profiles.id',
            'agent_profiles.division_id',
            'agent_profiles.slug',
            'agent_profiles.name',
            'agent_profiles.description',
            'agent_profiles.color',
            'agent_profiles.emoji',
            'agent_profiles.vibe',
            'agent_profiles.preferred_model_tier',
            'divisions.label as division_label',
            'divisions.floor_level',
            'divisions.color_hex as division_color',
            'divisions.icon_name as division_icon',
        ])->join('divisions', 'divisions.id = agent_profiles.division_id', 'left');

        if ($division) {
            $builder->where('agent_profiles.division_id', $division);
        }

        if ($floor) {
            $builder->where('divisions.floor_level', (int) $floor);
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
            ->orderBy('divisions.id', 'ASC')
            ->orderBy('agent_profiles.name', 'ASC')
            ->findAll();

        return $this->success($agents, 'Agents retrieved.');
    }

    /**
     * GET /api/v1/agents/:id
     * Returns full agent dossier including markdown system prompt
     */
    public function show($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->notFound('Agent ID required.');
        }

        $agent = $this->agentModel->select([
            'agent_profiles.*',
            'divisions.label as division_label',
            'divisions.floor_level',
            'divisions.color_hex as division_color',
            'divisions.icon_name as division_icon',
            'characters.id as character_id',
            'characters.current_status',
            'office_desks.desk_code',
            'office_desks.position_x',
            'office_desks.position_y',
            'office_desks.position_z',
        ])
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left')
        ->join('characters', 'characters.assigned_agent_id = agent_profiles.id', 'left')
        ->join('office_desks', 'office_desks.id = characters.assigned_desk_id', 'left')
        ->where('agent_profiles.id', $id)
        ->orWhere('agent_profiles.slug', $id)
        ->first();

        if (!$agent) {
            return $this->notFound('Agent not found.');
        }

        return $this->success($agent, 'Agent details retrieved.');
    }

    /**
     * GET /api/v1/divisions
     */
    public function divisions(): ResponseInterface
    {
        $divisions = $this->divisionModel->orderBy('floor_level', 'ASC')->findAll();
        return $this->success($divisions, 'Divisions retrieved.');
    }
}
