<?php

namespace App\Models;

use CodeIgniter\Model;

class CharacterModel extends Model
{
    protected $table            = 'characters';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'assigned_agent_id',
        'assigned_desk_id',
        'model_asset_path',
        'current_status',
        'last_status_update',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getFullSceneCharacters()
    {
        return $this->select([
            'characters.id as character_id',
            'characters.current_status',
            'characters.model_asset_path',
            'agent_profiles.id as agent_id',
            'agent_profiles.slug as agent_slug',
            'agent_profiles.name as agent_name',
            'agent_profiles.description as agent_description',
            'agent_profiles.color as agent_color',
            'agent_profiles.emoji as agent_emoji',
            'agent_profiles.vibe as agent_vibe',
            'agent_profiles.preferred_model_tier',
            'divisions.id as division_id',
            'divisions.label as division_label',
            'divisions.floor_level',
            'divisions.color_hex as division_color',
            'divisions.icon_name as division_icon',
            'office_desks.id as desk_id',
            'office_desks.desk_code',
            'office_desks.position_x',
            'office_desks.position_y',
            'office_desks.position_z',
            'office_desks.rotation_y',
            'office_rooms.id as room_id',
            'office_rooms.name as room_name',
        ])
        ->join('agent_profiles', 'agent_profiles.id = characters.assigned_agent_id', 'inner')
        ->join('divisions', 'divisions.id = agent_profiles.division_id', 'left')
        ->join('office_desks', 'office_desks.id = characters.assigned_desk_id', 'inner')
        ->join('office_rooms', 'office_rooms.id = office_desks.room_id', 'left')
        ->orderBy('divisions.floor_level', 'ASC')
        ->orderBy('divisions.id', 'ASC')
        ->orderBy('agent_profiles.name', 'ASC')
        ->findAll();
    }
}
