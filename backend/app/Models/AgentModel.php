<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentModel extends Model
{
    protected $table            = 'agent_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'division_id',
        'slug',
        'name',
        'description',
        'color',
        'emoji',
        'vibe',
        'source_path',
        'system_prompt',
        'preferred_model_tier',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithDivision(string $id = null)
    {
        $builder = $this->select('agent_profiles.*, divisions.label as division_label, divisions.floor_level, divisions.color_hex as division_color')
            ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left');

        if ($id) {
            return $builder->where('agent_profiles.id', $id)->orWhere('agent_profiles.slug', $id)->first();
        }

        return $builder->findAll();
    }
}

