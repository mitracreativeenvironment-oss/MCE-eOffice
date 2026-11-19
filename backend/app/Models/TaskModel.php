<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'agent_tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'user_id',
        'lead_agent_id',
        'title',
        'status',
        'brief_input',
        'final_deliverable',
        'review_feedback',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithAgent(string $id = null)
    {
        $builder = $this->select('agent_tasks.*, agent_profiles.name as agent_name, agent_profiles.emoji as agent_emoji, agent_profiles.color as agent_color, divisions.label as division_label')
            ->join('agent_profiles', 'agent_profiles.id = agent_tasks.lead_agent_id', 'inner')
            ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left');

        if ($id) {
            return $builder->where('agent_tasks.id', $id)->first();
        }

        return $builder->orderBy('agent_tasks.created_at', 'DESC')->findAll();
    }
}

